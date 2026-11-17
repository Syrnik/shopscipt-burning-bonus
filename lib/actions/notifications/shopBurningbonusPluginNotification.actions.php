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
    protected ?shopBurningbonusPlugin $plugin = null;

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
     * @ControllerAction save
     * @return void
     * @throws waException
     */
    public function saveAction(): void
    {
        $data = waRequest::post('data', '{}', waRequest::TYPE_STRING_TRIM);
        try {
            $data = waUtils::jsonDecode($data, true);
        } catch (waException $e) {
            $data = null;
        }
        if (!is_array($data)) {
            $this->errors = [_wp('Invalid notification data')];
            return;
        }

        $model = new shopBurningbonusNotificationsModel;
        $id = (int)($data['id'] ?? 0);
        $existing = $id ? $model->getById($id) : null;
        if ($id && !$existing) {
            $this->errors = [_wp('Notification not found. It may have been deleted already.')];
            return;
        }

        // транспорт у сохранённого уведомления не меняется
        $transport = $existing ? $existing['transport'] : (string)($data['transport'] ?? 'email');
        [$row, $errors] = $this->validateNotification($data, $transport);
        if ($errors) {
            $this->errors = $errors;
            return;
        }

        if ($existing) {
            $model->updateById($id, $row);
        } else {
            $id = (int)$model->insert($row + ['transport' => $transport]);
        }

        $this->response = ['id' => $id];
    }

    /**
     * В базу попадают только перечисленные здесь поля, а не всё, что пришло в запросе
     *
     * @param array $data
     * @param string $transport
     * @return array{0:array<string,mixed>, 1:string[]} данные для записи и список ошибок
     */
    protected function validateNotification(array $data, string $transport): array
    {
        $errors = [];
        $string = fn(string $key): string => is_scalar($data[$key] ?? null) ? (string)$data[$key] : '';

        if (!in_array($transport, ['email', 'sms'], true)) {
            $errors[] = sprintf_wp('Delivery method %s is not supported', $transport);
        }

        $row = [
            'name'    => mb_substr(trim($string('name')), 0, 255),
            'status'  => empty($data['status']) ? 0 : 1,
            'subject' => trim($string('subject')),
            'body'    => $string('body'),
            'from'    => trim($string('from')),
        ];

        if ('' === trim($row['body'])) {
            $errors[] = _wp('Message text is required');
        }
        if ('email' === $transport) {
            if ('' === $row['subject']) {
                $errors[] = _wp('Message subject is required');
            }
            if ('' !== $row['from'] && !(new waEmailValidator())->isValid($row['from'])) {
                $errors[] = _wp('Invalid sender address');
            }
        }
        if (mb_strlen($row['from']) > 200) {
            $errors[] = _wp('Sender is too long');
        }

        $row['schedule_type'] = $string('schedule_type');
        $row['schedule_day'] = (int)($data['schedule_day'] ?? 0);
        if (!in_array($row['schedule_type'], ['monthly', 'weekly'], true)) {
            $errors[] = _wp('Notification schedule is not specified');
        } elseif ($row['schedule_day'] < 1 || $row['schedule_day'] > ('monthly' === $row['schedule_type'] ? 31 : 7)) {
            $errors[] = 'monthly' === $row['schedule_type'] ? _wp('Day of month must be between 1 and 31') : _wp('Invalid day of week');
        }

        $time = $string('scheduled_time');
        if ('' !== $time && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
            $errors[] = _wp('Invalid sending time');
        }
        $row['scheduled_time'] = '' === $time ? null : $time;

        return [$row, $errors];
    }

    /**
     * @ControllerAction get
     * @return void
     */
    public function getAction(): void
    {
        $id = (int)waRequest::get('id', 0, waRequest::TYPE_INT);
        if (!$id) {
            $this->errors = [_wp('ID is not specified')];
            return;
        }

        $data = (new shopBurningbonusNotificationsModel)->getById($id);
        if ($data) {
            $data['id'] = (int)$data['id'];
            $data['status'] = (bool)$data['status'];
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
            $this->errors = [_wp('Notification ID is not specified')];
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
                throw new waException(sprintf_wp('Unknown write-off schedule: %s', $schedule_type));

            // Дата списания — по настройкам плагина и с учётом отложенного старта, как у рассылки
            // по расписанию. Расписание самого уведомления (schedule_type) на неё не влияет
            if (!($date = $this->getPlugin()->closestBurnControlDate()))
                throw new waException(_wp('Bonus write-off is disabled or the bonus lifetime is not set in the settings'));

            $burning_data = (new shopBurningbonusAffiliateModel)->getBurningById($contact_id, $date);

            $customer = new shopCustomer($contact_id);
            if (!$customer->exists())
                throw new waException(sprintf_wp('Customer with ID=%d does not exist', $contact_id));

            // Как при рассылке по расписанию: не больше реального баланса покупателя
            $to_burn = shopBurningbonusPlugin::calcBurnAmount((float)($burning_data['to_burn'] ?? 0), (float)$customer->affiliateBonus());
            if (0 >= $to_burn)
                throw new waException(sprintf_wp('Customer with ID=%d has no bonuses to write off', $contact_id));
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
                    $this->errors[] = sprintf_wp('Delivery method %s is not supported', $transport);
                    return;
            }
        } catch (waException $e) {
            $this->errors[] = $e->getMessage();
            return;
        }

        try {
            if ($notification->send($contact_id, $to_burn, $recipient))
                $this->response = _wp('Notification sent successfully');
            else $this->errors[] = _wp('Failed to send notification');
        } catch (SmartyException|SmartyCompilerException $e) {
            $this->errors[] = sprintf_wp('Template error: %s', $e->getMessage());
        } catch (waException $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * @return shopBurningbonusPlugin
     * @throws waException
     */
    protected function getPlugin(): shopBurningbonusPlugin
    {
        return $this->plugin ?? $this->plugin = wa('shop')->getPlugin('burningbonus');
    }
}
