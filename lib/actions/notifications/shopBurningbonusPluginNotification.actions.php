<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * @Controller notification
 */
class shopBurningbonusPluginNotificationActions extends waJsonActions
{
    /**
     * @return void
     * @throws waException
     * @throws waRightsException
     */
    protected function preExecute(): void
    {
        if (!wa()->getUser()->getRights('shop', 'setup_marketing')) {
            throw new waRightsException(_ws('Access denied'));
        }

        parent::preExecute();
    }

    /**
     * @ControllerAction default
     * @return void
     */
    public function defaultAction(): void
    {
        $id = waRequest::get('id', null, waRequest::TYPE_INT);
    }

    /**
     * @ControllerAction save
     * @return void
     * @throws waException
     */
    public function saveAction(): void
    {
        $data = waRequest::post('data', '{}', waRequest::TYPE_STRING_TRIM);
        $data = waUtils::jsonDecode($data, true);
        $model = new shopBurningbonusNotificationsModel;

        $data['name'] = $data['name'] ?? '';

        if ($data['id'] ?? false) {
            $id = $data['id'];
            unset($data['id']);
            $model->updateById($id, $data);
        } else {
            $id = $model->insert($data);
        }

        $this->response = ['id' => $id];
    }

    /**
     * @ControllerAction get
     * @return void
     */
    public function getAction(): void
    {
        $id = (int)waRequest::get('id', 0, waRequest::TYPE_INT);
        if (!$id) {
            $this->errors = ['Не указан идентификатор'];
            return;
        }

        $data = (new shopBurningbonusNotificationsModel)->getById($id);
        if ($data) {
            $data['id'] = (int)$data['id'];
            $data['status'] = (bool)$data['status'];
            $data['registered_only'] = (bool)$data['registered_only'];
            $data['burned_only'] = (bool)$data['burned_only'];
            $data['schedule_day'] = (int)$data['schedule_day'];
            $data['scheduled_time'] = $data['scheduled_time'] ?: null;
        }

        $this->response = $data ?: [];
    }

    /**
     * @ControllerAction delete
     * @return void
     */
    public function deleteAction(): void
    {
        if (!($id = (int)waRequest::post('id', 0, waRequest::TYPE_INT))) {
            $this->errors = ['Не указан идентификатор уведомления'];
            return;
        }

        $model = new shopBurningbonusNotificationsModel;
        $model->deleteById($id);

        $this->response = ['id' => $id];
    }

    /**
     * @ControllerAction sendtest
     * @return void
     */
    public function sendtestAction(): void
    {
        $contact_id = (int)waRequest::post('contact_id', 0, waRequest::TYPE_INT);
        $recipient = waRequest::post('recipient', '', waRequest::TYPE_STRING_TRIM);
        $notification_data = waRequest::post('notification', '', waRequest::TYPE_ARRAY);

        try {
//            $notification_data = waUtils::jsonDecode($notification_data, true);
//            if (!is_array($notification_data) || !$notification_data)
//                throw new waException("Не передано данные уведомления");

            $schedule_type = $notification_data['schedule_type'] ?? '';
            if (!in_array($schedule_type, ['monthly', 'weekly']))
                throw new waException("Неизвестный тип списания '$schedule_type'");

            $date = (new DateTime($schedule_type === 'monthly' ? 'first day of next month' : 'next monday'))
                ->format('Y-m-d');

            $burning_data = (new shopBurningbonusAffiliateModel)->getBurningById($contact_id, $date);

            if (!($burning_data['to_burn']))
                throw new waException("Нет бонусов для списания у покупателя с ID=$contact_id");
        } catch (waException|Exception $e) {
            $this->errors[] = $e->getMessage();
            return;
        }

        $transport = $notification_data['transport'] ?? '';

        try {
            switch ($transport) {
                case 'email':
                    $notification = new shopBurningbonusPluginEmailNotification($notification_data);
                    break;
                case 'sms':
                    $notification = new shopBurningbonusPluginSmsNotification($notification_data);
                    break;
                default:
                    $this->errors[] = "Доставка $transport не поддерживается";
                    return;
            }
        } catch (waException $e) {
            $this->errors[] = $e->getMessage();
            return;
        }

        try {
            if ($notification->send($contact_id, $burning_data['to_burn'], $recipient))
                $this->response = "Уведомление успешно отправлено";
            else $this->errors[] = "Не удалось отправить уведомление";
        } catch (SmartyException|SmartyCompilerException $e) {
            $this->errors[] = "Ошибка шаблона: {$e->getMessage()}";
        } catch (waException $e) {
            $this->errors[] = $e->getMessage();
        }
    }
}
