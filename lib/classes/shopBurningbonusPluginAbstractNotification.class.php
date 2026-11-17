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
abstract class shopBurningbonusPluginAbstractNotification
{
    /**
     *
     */
    public const EXCEPTION_NO_ADDRESS = 701;

    /**
     * @var array
     */
    protected array $notification_data;
    /**
     * @var waSmarty3View|null
     */
    protected ?waSmarty3View $view = null;

    /**
     * @param array $notification_data
     */
    public function __construct(array $notification_data)
    {
        $this->notification_data = $notification_data;
    }

    /**
     * @param int $customer_id
     * @param float $burning_amount
     * @param string|null $address
     * @return bool
     */
    abstract public function send(int $customer_id, float $burning_amount, ?string $address = null): bool;

    /**
     * Адрес покупателя для транспорта уведомления, если он не передан явно
     *
     * @param shopCustomer $customer
     * @return string пустая строка, если адреса нет
     */
    abstract protected function getDefaultAddress(shopCustomer $customer): string;

    /**
     * @param int $customer_id
     * @param string|null $address
     * @param float $burning_amount
     * @return array
     * @throws SmartyException
     * @throws waException
     */
    protected function buildMessage(int $customer_id, ?string $address, float $burning_amount): array
    {
        if ($this->view) $this->view->clearAllAssign();
        else $this->view = new waSmarty3View(wa());

        $customer = new shopCustomer($customer_id);
        if (!$customer->exists()) throw new waException(sprintf_wp('Customer with ID=%d does not exist', $customer_id));

        if (!$address && !($address = $this->getDefaultAddress($customer)))
            throw new waException(_wp('Recipient address is not specified'), self::EXCEPTION_NO_ADDRESS);

        $this->view->assign(['customer' => $customer, 'burning_amount' => $burning_amount]);

        $body = $this->view->fetch("string:{$this->notification_data['body']}");
        $subject = $this->view->fetch("string:{$this->notification_data['subject']}");
        return array($customer, $address, $body, $subject);
    }
}
