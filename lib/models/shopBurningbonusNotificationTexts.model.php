<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * Языковые версии уведомлений: тема и текст. Версия с locale = '' — по умолчанию, есть у каждого уведомления
 */
class shopBurningbonusNotificationTextsModel extends waModel
{
    /**
     * @var string
     */
    protected $table = 'shop_burningbonus_notification_texts';

    /**
     * Версии уведомлений, по умолчанию первой, остальные — по коду локали
     *
     * @param int|int[] $notification_id
     * @return array<int, list<array{locale:string, subject:string, body:string}>> notification_id => версии
     */
    public function getByNotification($notification_id): array
    {
        $ids = array_filter(array_map('intval', (array)$notification_id));
        if (!$ids) return [];

        $rows = $this->select('notification_id, locale, subject, body')
            ->where('notification_id IN (i:ids)', ['ids' => $ids])
            ->order('notification_id, locale')
            ->fetchAll();

        $result = [];
        foreach ((array)$rows as $row) {
            $result[(int)$row['notification_id']][] = [
                'locale'  => (string)$row['locale'],
                'subject' => (string)$row['subject'],
                'body'    => (string)$row['body'],
            ];
        }

        return $result;
    }

    /**
     * Заменяет все версии уведомления переданными
     *
     * @param int $notification_id
     * @param list<array{locale:string, subject:string, body:string}> $versions
     * @return void
     */
    public function saveVersions(int $notification_id, array $versions): void
    {
        $this->deleteByField('notification_id', $notification_id);
        if (!$versions) return;

        $this->multipleInsert(array_map(fn(array $v) => [
            'notification_id' => $notification_id,
            'locale'          => $v['locale'],
            'subject'         => $v['subject'],
            'body'            => $v['body'],
        ], $versions));
    }
}
