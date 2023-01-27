<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 *
 */
class shopBurningbonusCli extends waCliController
{
    /**
     *
     */
    protected const LOG_FILE = 'shop/plugins/burningbonus.cli.log';

    /** @var resource|SysvSemaphore */
    protected $semaphore;

    /** @var string|null */
    protected ?string $semafore_file = null;

    /**
     * @var array|string[]
     */
    protected array $task_classmap = [
        'burn'   => shopBurningbonusPluginBurnTask::class,
        'notify' => shopBurningbonusPluginNotifyTask::class,
    ];

    /**
     * @var shopBurningbonusPlugin
     */
    protected shopBurningbonusPlugin $plugin;

    /**
     * @return void
     */
    public function execute()
    {
        if ($this->helpRequired()) {
            $this->showHelp();
            return;
        }

        try {
            if (!$this->acquireSemaphore()) return;
        } catch (waException $e) {
            return;
        }

        try {
            $tasks = $this->getTasks();

            foreach ($tasks as $task) {
                if ($task_class = $this->task_classmap[$task] ?? '') {
                    if (class_exists($task_class)) {
                        $task = new $task_class($this->plugin);
                        $task->run();
                    }
                }
            }

        } catch (Throwable $exception) {
            waLog::log($exception->getMessage(), self::LOG_FILE);
        }

        $this->releaseSemaphore();
    }

    /**
     * @return bool
     */
    protected function helpRequired(): bool
    {
        $params = waRequest::param();
        return array_key_exists('help', $params);
    }

    /**
     * @return void
     */
    protected function showHelp()
    {
        echo $str = trim("shopBurningbonus CLI script v" . $this->plugin->getVersion() . ", PHP версии " . PHP_VERSION) . "\n";
        echo str_repeat('=', mb_strlen($str, 'UTF-8') - 1) . "\n\n";
        echo "Строка для запуска: 'php cli.php shop burningbonus <задание...> <-команда...>'\n\n";
        echo "<команда> всегда начинается с тире, <задание> просто идентификатор задания. \n\n";

        echo "Доступные команды:\n";
        echo " -help -- Показывает эту справку\n";

        echo "\n\nДоступные задания:\n\n";

        foreach ($this->task_classmap as $key => $item) {

            $class = new $item($this->plugin);
            $description = $class->getDescription();

            echo $key . "\n" . str_repeat('-', mb_strlen($key, 'UTF-8')) . "\n";
            echo $description . "\n\n";
        }
    }

    /**
     * @return bool
     * @throws waException
     */
    protected function acquireSemaphore(): bool
    {
        if (function_exists('sem_get')) {
            $sem_key = ftok(__FILE__, 'B');
            if (false === ($semaphore = sem_get($sem_key))) {
                waLog::log('Ошибка получения семафора при выполнении консольного задания', self::LOG_FILE);
                throw new waException('Ошибка получения семафора при выполнении консольного задания');
            }
            $this->semaphore = $semaphore;

            if (false === sem_acquire($this->semaphore, true)) {
                waLog::log('Семафор заблокирован другим процессом. Возможно, параллельно выполняется ещё одно задание плагина сгорающих бонусов', self::LOG_FILE);
                return false;
            }
        } else {
            waLog::log("В системе отсутствует поддержка SystemV семафоров. Запросите у администратора системы или хостера, чтобы они добавили поддержку sysvsem в вашу установку PHP!", self::LOG_FILE);
            $this->semafore_file = wa()->getDataPath('plugins/burningbonus/cli.lock', false, 'shop', false);
            if (file_exists($this->semafore_file)) {
                if (!is_file($this->semafore_file) || !is_readable($this->semafore_file) || is_writable($this->semafore_file)) {
                    waLog::log("Нет доступа к lock-файлу $this->semafore_file", self::LOG_FILE);
                    throw new waException("Нет доступа к lock-файлу $this->semafore_file");
                }
                //что-то у нас этот лок уже больше суток торчит
                if (time() > intval(@filemtime($this->semafore_file)) + 86400) {
                    waLog::log("Lock-файл $this->semafore_file что-то очень давно, больше суток назад, создан. Удаляем.", self::LOG_FILE);
                    if (!@unlink($this->semafore_file)) {
                        waLog::log("Не получилось удалить lock-файл $this->semafore_file. Так работать нельзя.", self::LOG_FILE);
                        throw new waException("Не получилось удалить lock-файл $this->semafore_file. Так работать нельзя.");
                    }
                } else {
                    waLog::log('lock-файл заблокирован другим процессом. Возможно, параллельно выполняется ещё одно задание плагина сгорающих бонусов', self::LOG_FILE);
                    return false;
                }

                if (!@touch($this->semafore_file)) {
                    waLog::log("Ошибка создания lock-файла $this->semafore_file", self::LOG_FILE);
                    throw new waException("Ошибка создания lock-файла $this->semafore_file");
                }
            }
        }

        return true;
    }

    /**
     * @return string[] Array of task ID
     */
    protected function getTasks(): array
    {
        $params = waRequest::param();
        $params = array_filter($params, 'is_numeric', ARRAY_FILTER_USE_KEY);

        return $params ?: array_keys($this->task_classmap);
    }

    /**
     * @return void
     */
    protected function releaseSemaphore(): void
    {
        if (function_exists('sem_get')) {
            if (false === sem_release($this->semaphore)) {
                waLog::log('Ошибка при освобождении семафора. Это что-то странное.', self::LOG_FILE);
            }
        } else {
            @unlink($this->semafore_file);
        }
    }

    /**
     * @return void
     * @throws waException
     */
    protected function preExecute()
    {
        parent::preExecute();
        $this->plugin = wa('shop')->getPlugin('burningbonus');
    }
}
