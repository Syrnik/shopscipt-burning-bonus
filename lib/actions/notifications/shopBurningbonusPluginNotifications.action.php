<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * @ControllerAction notifications/default
 */
class shopBurningbonusPluginNotificationsAction extends shopMarketingSettingsViewAction
{
    /** @var shopBurningbonusNotificationsModel|null */
    protected ?shopBurningbonusNotificationsModel $notifications_model = null;

    /**
     * @return void
     * @throws waException
     */
    public function execute()
    {
        $config = wa('shop')->getConfig();

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $references = [
            'default_email_address' => $config->getGeneralSettings('email'),
            'notification_template' => $this->getNotificationTemplate(),
            'body_templates'        => $this->getBodyTemplates()
        ];

        $notifications_list = $this->getList();

        // debug-режим: не кэшируем бандл, пока его пересобирают
        $plugin_version = wa('shop')->getPlugin('burningbonus')->getVersion() . (waSystemConfig::isDebug() ? '.' . time() : '');
        $ui = wa()->whichUI('shop');

        $this->view->assign([
            'references'         => $references,
            'notifications_list' => $notifications_list,
            'plugin_version'     => $plugin_version,
            'ui'                 => $ui,
        ]);
    }

    /**
     * @return list<array{id:int, name:string, transport:string}>
     */
    protected function getList(): array
    {
        $data = $this->getModel()->select('*')->order('id')->fetchAll();

        return array_values(array_map(fn($row) => [
            'id'        => (int)$row['id'],
            'name'      => (string)$row['name'],
            'transport' => (string)$row['transport']
        ], (array)$data));
    }

    /**
     * @return shopBurningbonusNotificationsModel
     */
    protected function getModel(): shopBurningbonusNotificationsModel
    {
        return $this->notifications_model ?? $this->notifications_model = new shopBurningbonusNotificationsModel;
    }

    protected function getNotificationTemplate(): array
    {
        $template = $this->getModel()->getEmptyRow();
        $template['id'] = $template['scheduled_time'] = $template['body'] = null;
        $template['status'] = true;
        $template['schedule_day'] = 1;
        $template['subject'] = '{$customer->get(\'firstname\')}, ваши бонусы скоро сгорят!';

        return $template;
    }

    /**
     * @return array{email:string, sms:string}
     */
    protected function getBodyTemplates(): array
    {
        try {
            $dir = wa('shop')->getAppPath('plugins/burningbonus/templates/notification-templates/');
            $plugin = wa('shop')->getPlugin('burningbonus');
        } catch (waException $e) {
            return ['email' => '', 'sms' => ''];
        }
        $period = $plugin->getSettings('period');
        $period = in_array($period, ['monthly', 'weekly'], true) ? $period : 'monthly';

        $result = ['email' => '', 'sms' => ''];

        foreach (['email', 'sms'] as $transport) {
            $file = "$dir$transport-$period.html";
            if (file_exists($file) && is_readable($file) && is_file($file))
                $result[$transport] = (string)file_get_contents($file);
        }

        return $result;
    }
}
