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
class shopBurningbonusPluginEmailNotification extends shopBurningbonusPluginAbstractNotification
{
    /**
     * @var int|null
     */
    protected ?int $contact_id = null;

    /**
     * @var string|null
     */
    protected ?string $from_name;

    /**
     * @var string
     */
    protected string $default_from;

    /**
     * @param array{subject:string,body:string,from:string} $notification_data
     * @throws waException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function __construct(array $notification_data)
    {
        parent::__construct($notification_data);
        $this->default_from = wa('shop')->getConfig()->getGeneralSettings('email') ?? '';
        $this->from_name = wa('shop')->getConfig()->getGeneralSettings('name') ?? null;
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
        list($customer, $address, $body, $subject) = $this->buildMessage($customer_id, $address, $burning_amount);

        $message = new waMailMessage($subject, $body);
        $message->setFrom($this->getFrom(), $this->from_name);
        $message->setTo($address, $customer->getName());

        return (bool)$message->send();
    }

    /**
     * @return string
     */
    protected function getFrom(): string
    {
        if ($from = trim($this->notification_data['from'] ?? '')) return $from;
        return $this->default_from;
    }
}
