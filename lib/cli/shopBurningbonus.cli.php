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
     * @throws waException
     */
    protected function preExecute()
    {
        parent::preExecute();
        $this->plugin = wa('shop')->getPlugin('burningbonus');
    }

    public function execute()
    {
        if ($this->helpRequired()) {
            $this->showHelp();
            return;
        }

        $tasks = $this->getTasks();

        foreach ($tasks as $task) {
            if ($task_class = $this->task_classmap[$task] ?? '') {
                if (class_exists($task_class)) {
                    $task = new $task_class($this->plugin);
                    $task->run();
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function helpRequired(): bool
    {
        $params = waRequest::param();
        return array_key_exists('help', $params);
    }

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
     * @return string[] Array of task ID
     */
    protected function getTasks(): array
    {
        $params = waRequest::param();
        $params = array_filter($params, 'is_numeric', ARRAY_FILTER_USE_KEY);

        return $params ?: array_keys($this->task_classmap);
    }
}
