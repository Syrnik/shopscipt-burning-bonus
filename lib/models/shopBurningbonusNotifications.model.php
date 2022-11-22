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
class shopBurningbonusNotificationsModel extends waModel
{
    /**
     * @var string
     */
    protected $table = 'shop_burningbonus_notifications';

    /**
     * @return array
     * @throws waException
     */
    public function getEnabled(): array
    {
        return (array)$this->getByField('status', 1, true);
    }

    /**
     * @param int|int[]|string|string[] $id
     * @param string|null $datetime
     * @return void
     */
    public function markSent($id, ?string $datetime = null): void
    {
        if (!$id) return;
        if (!$datetime) $datetime = date('Y-m-d H:i:s');

        $this->updateByField('id', $id, ['sent' => $datetime]);
    }
}
