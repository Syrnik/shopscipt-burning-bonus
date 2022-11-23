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
class shopBurningbonusPluginNotifyTask implements shopBurningbonusPluginTaskInterface
{
    /**
     * @var shopBurningbonusPlugin
     */
    protected shopBurningbonusPlugin $plugin;

    /**
     * @var shopBurningbonusNotificationsModel|null
     */
    protected ?shopBurningbonusNotificationsModel $notificationsModel = null;

    /**
     * @var shopBurningbonusAffiliateModel|null
     */
    protected ?shopBurningbonusAffiliateModel $affiliateModel = null;

    /**
     * @var DateTimeInterface|DateTime
     */
    protected DateTimeInterface $today;

    /**
     * @param shopBurningbonusPlugin $plugin
     */
    public function __construct(shopBurningbonusPlugin $plugin)
    {
        $this->plugin = $plugin;
        $this->today = new DateTime();
    }

    /**
     * @param DateTime $burn_date
     * @return bool
     */
    protected function isDateInPeriod(DateTime $burn_date): bool
    {
        if (!$this->plugin->getSettings('delay')) return true;
        $period = $this->plugin->getSettings('period');

        if ('weekly' === $period) $compare_date = new DateTime('next monday');
        else$compare_date = new DateTime('first day of next month');

        return $burn_date->format('Y-m-d') === $compare_date->format('Y-m-d');
    }

    /**
     * @return bool
     * @throws waException
     */
    public function run(): bool
    {
        if (!$this->plugin->isBurningEnabled()) return true;

        $this->setToday();

        if (!($notifications = $this->getActualNotifications())) return true;
        if (!($lifetime = intval($this->plugin->getSettings('lifetime')))) return true;

        try {
            $upcoming_burning = $this->plugin->closestBurnDate();
            if (!$this->isDateInPeriod($upcoming_burning)) return true;

            $upcoming_burning = $upcoming_burning->modify("-{$lifetime} days")->format('Y-m-d');
        } catch (Exception $e) {
            throw new waException($e->getMessage());
        }

        $sentencedCustomers = $this->getAffiliateModel()->queryBurning($upcoming_burning);

        // некому слать
        if (!$sentencedCustomers->count()) {
            $this->getNotificationModel()->markSent(array_column($notifications, 'id'));
            return true;
        }

        foreach ($sentencedCustomers as $customer) {
            if (!(new shopCustomer((int)$customer['contact_id']))->exists()) continue;
            foreach ($notifications as $index => $notification) {
                if (!($notificationMessage = $notification['_message'] ?? null)) {
                    switch ($notification['transport']) {
                        case 'email' :
                            $notificationMessage = new shopBurningbonusPluginEmailNotification($notification);
                            break;
                        case 'sms':
                            $notificationMessage = new shopBurningbonusPluginSmsNotification($notification);
                            break;
                        default:
                            continue 2;
                    }
                    $notifications[$index]['_message'] = $notificationMessage;
                }

                try {
                    $notificationMessage->send((int)$customer['contact_id'], (float)$customer['to_burn']);
                } catch (SmartyException|waException $e) {
                    //todo log
                }
            }
        }

        $this->getNotificationModel()->markSent(array_column($notifications, 'id'));
        return true;
    }

    /**
     * @return void
     */
    protected function setToday(): void
    {
        $param = waRequest::param('set-date', null, waRequest::TYPE_STRING_TRIM);
        if (!$param || !preg_match('/^20\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $param))
            return;

        try {
            $this->today = new DateTime($param);
        } catch (Exception $e) {
        }
    }

    /**
     * @return array
     * @throws waException
     */
    protected function getActualNotifications(): array
    {
        if (!($enabled = $this->getNotificationModel()->getEnabled())) return [];
        $to_send = [];
        [$year, $month, $day, $dow] = array_map('intval', explode(',', $this->today->format('Y,n,j,N')));
        $time = $this->today->format('H:i');

        foreach ($enabled as $notification) {
            // ежемесячное и день отправки не сегодняшний
            if ('monthly' === $notification['schedule_type'] && $day !== intval($notification['schedule_day']))
                continue;

            // еженедельное и день недели отправки не сегодняшний
            if ('weekly' === $notification['schedule_type'] && $dow !== intval($notification['schedule_day']))
                continue;

            // Указано время, после которого надо отправлять и оно ещё не настало
            if ($notification['scheduled_time'] && $notification['scheduled_time'] > $time)
                continue;

            // уже когда-то отправлялось
            if ($notification['sent']) {
                // когда отправлялось?
                [$sent_year, $sent_month, $sent_day] = array_map('intval', explode('-', substr($notification['sent'], 0, 10)));

                // в этом месяце уже были отправки этого уведомления
                if ($sent_year === $year && $sent_month === $month) {

                    // ежемесячное, в этом месяце уже отправляли
                    if ('monthly' === $notification['schedule_type']) continue;

                    // еженедельное и сегодня отправляли
                    if ($sent_day === $day) continue;
                }
            }

            $to_send[] = $notification;
        }

        return $to_send;
    }

    /**
     * @return shopBurningbonusNotificationsModel
     */
    protected function getNotificationModel(): shopBurningbonusNotificationsModel
    {
        return $this->notificationsModel ?? $this->notificationsModel = new shopBurningbonusNotificationsModel();
    }

    /**
     * @return shopBurningbonusAffiliateModel
     */
    protected function getAffiliateModel(): shopBurningbonusAffiliateModel
    {
        return $this->affiliateModel ?? $this->affiliateModel = new shopBurningbonusAffiliateModel();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Отправка уведомлений';
    }
}
