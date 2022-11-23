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
class shopBurningbonusPluginBurnTask implements shopBurningbonusPluginTaskInterface
{
    /**
     *
     */
    const LAST_BURN_KEY = 'last.burn';

    /**
     * @var shopBurningbonusPlugin
     */
    protected shopBurningbonusPlugin $plugin;

    /**
     * @var DateTimeInterface|DateTime
     */
    protected DateTimeInterface $today;

    /**
     * @var waAppSettingsModel|null
     */
    protected ?waAppSettingsModel $appSettingsModel = null;


    /**
     * @inheritDoc
     */
    public function __construct(shopBurningbonusPlugin $plugin)
    {
        $this->plugin = $plugin;
        $this->today = new DateTime();
        $this->setToday();
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
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Выполняет списание бонусов';
    }

    /**
     * @inheritDoc
     */
    public function run(): bool
    {
        if (!$this->plugin->isBurningEnabled() || $this->isLaunchedToday() || $this->isBurningDelayed() || !$this->isBurnDay()) return true;

        $control_date = clone $this->today;
        $lifetime = (int)$this->plugin->getSettings('lifetime');
        $control_date->modify("-$lifetime days");

        $affiliateModel = new shopBurningbonusAffiliateModel;

        try {
            $burning_men = $affiliateModel->queryBurning($control_date->format('Y-m-d'));
        } catch (waDbException|waException $e) {
            return false;
        }

        foreach ($burning_men as $row) {
            $row = $affiliateModel->typecastBurningRow($row);
            if (!$row['contact_id'] || !$row['to_burn']) continue;
            try {
                if (!(new shopCustomer($row['contact_id']))->exists()) continue;
            } catch (waException $e) {
                continue;
            }
            $affiliateModel->applyBonus($row['contact_id'], 0 - $row['to_burn'], null, 'Списание неиспользованных просроченных бонусных баллов', 'burn_bonus');
        }

        $this->getAppSettingsModel()->set(['shop', $this->plugin->getId()], self::LAST_BURN_KEY, date('Y-m-d H:i:s'));

        return true;
    }

    /**
     * Сегодня уже запускалось? Если дата последнего запуска или в будущем по отношению к сегодняшней — не отрабатываем
     * @return bool
     */
    protected function isLaunchedToday(): bool
    {
        $lastBurn = trim((string)$this->getAppSettingsModel()->get('shop.bugningbonus', self::LAST_BURN_KEY));
        if (!$lastBurn) return false;

        try {
            return (new DateTime($lastBurn)) >= $this->today;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return waAppSettingsModel
     */
    protected function getAppSettingsModel(): waAppSettingsModel
    {
        return $this->appSettingsModel ?? $this->appSettingsModel = new waAppSettingsModel();
    }

    /**
     * @return bool
     */
    protected function isBurningDelayed(): bool
    {
        $delay = trim((string)$this->plugin->getSettings('delay'));
        if (!$delay) return false;

        return $delay > $this->today->format('Y-m-d');
    }

    /**
     * @return bool
     */
    protected function isBurnDay(): bool
    {
        switch ($this->plugin->getSettings('period')) {
            case 'weekly':
                return 1 === (int)$this->today->format('N');
            case 'monthly':
                return 1 === (int)$this->today->format('j');
        }

        return false;
    }
}
