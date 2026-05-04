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
            if (!$this->acquireLock()) return;
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

        $this->releaseLock();
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
    protected function acquireLock(): bool
    {
        $result = (new shopBurningbonusNotificationsModel())
            ->query("SELECT GET_LOCK('shop_burningbonus_cli', 0)")
            ->fetchField();

        if ($result === '1') {
            return true;
        }

        if ($result === '0') {
            waLog::log(
                'Блокировка занята другим процессом. Возможно, параллельно выполняется ещё одно задание плагина сгорающих бонусов.',
                self::LOG_FILE
            );
            return false;
        }

        waLog::log('Ошибка GET_LOCK при выполнении консольного задания.', self::LOG_FILE);
        throw new waException('Ошибка GET_LOCK при выполнении консольного задания.');
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
    protected function releaseLock(): void
    {
        (new shopBurningbonusNotificationsModel())
            ->exec("DO RELEASE_LOCK('shop_burningbonus_cli')");
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
