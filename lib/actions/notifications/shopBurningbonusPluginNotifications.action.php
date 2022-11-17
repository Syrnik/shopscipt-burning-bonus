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
    protected ?shopBurningbonusNotificationsModel $Notifications = null;

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
            'notification_template' => $this->getNotificationTemplate()
        ];

        $notifications_list = $this->getList();

        $this->view->assign(compact(['references', 'notifications_list']));
    }

    /**
     * @return array{id:int, name:string, type:string}[]
     */
    protected function getList(): array
    {
        $data = $this->getModel()->select('*')->order('id')->fetchAll();

        return array_values(array_map(function ($row) {
            return [
                'id'        => (int)$row['id'],
                'name'      => (string)$row['name'],
                'transport' => (string)$row['transport']
            ];
        }, (array)$data));
    }

    /**
     * @return shopBurningbonusNotificationsModel
     */
    protected function getModel(): shopBurningbonusNotificationsModel
    {
        return $this->Notifications ?? $this->Notifications = new shopBurningbonusNotificationsModel;
    }

    protected function getNotificationTemplate(): array
    {
        $template = $this->getModel()->getEmptyRow();
        $template['id'] = $template['scheduled_time'] = null;
        $template['status'] = $template['registered_only'] = $template['burned_only'] = true;
        $template['schedule_day'] = 1;

        return $template;
    }
}
