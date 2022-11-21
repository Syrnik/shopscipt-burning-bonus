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
class shopBurningbonusPluginSmsNotification extends shopBurningbonusPluginAbstractNotification
{
    /**
     * @param array $notification_data
     */
    public function __construct(array $notification_data)
    {
        parent::__construct($notification_data);
    }

    /**
     * @param int $customer_id
     * @param float $burning_amount
     * @param string|null $address
     * @return bool
     * @throws SmartyException
     * @throws waException
     */
    public function send(int $customer_id, float $burning_amount, ?string $address = null): bool
    {
        [, $address, $body] = $this->buildMessage($customer_id, $address, $burning_amount);

        $sms = new waSMS();

        return (bool)$sms->send($address, trim($body), trim($this->notification_data['from'] ?? '') ?: null);
    }
}
