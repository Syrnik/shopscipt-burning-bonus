<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * @Controller notifications
 */
class shopBurningbonusPluginNotificationsActions extends waJsonActions
{
    /** @var shopBurningbonusNotificationsModel|null */
    protected ?shopBurningbonusNotificationsModel $Notifications = null;

    /** @var null|shopBurningbonusPlugin */
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
     * Выбирает максимум 10 контактов для теста
     *
     * @ControllerAction gettestdata
     * @return void
     */
    public function gettestdataAction(): void
    {
        try {
            $lifetime = (int)$this->getPlugin()->getSettings('lifetime');
            $period = $this->getPlugin()->getSettings('period');

            if (!in_array($period, ['weekly', 'monthly'], true)) {
                $this->errors = [_wp('Bonus write-off is disabled in the settings')];
                return;
            }

            if (!$lifetime) {
                $this->errors = [_wp('Bonus lifetime is not set in the settings')];
                return;
            }

            // Тот же расчёт, что у рассылки по расписанию: с учётом отложенного старта
            if (!($date = $this->getPlugin()->closestBurnControlDate())) {
                $this->errors = [_wp('Failed to determine the next write-off date')];
                return;
            }

            $count = 0;
            $burningbonusAffiliateModel = new shopBurningbonusAffiliateModel;
            $sentenced = $burningbonusAffiliateModel->queryBurning($date);

            while (10 > $count && $row = $sentenced->fetchAssoc()) {
                if (!($row['contact_id'] ?? null)) continue;
                $row = $burningbonusAffiliateModel->typecastBurningRow($row);
                $customer = new shopCustomer($row['contact_id']);
                if (!$customer->exists()) continue;

                // Как при рассылке по расписанию: реальный баланс покупателя, а не по журналу транзакций,
                // и сумма к сгоранию не больше него. Покупателей, которым рассылка ничего не отправит, не показываем
                $row['actual_balance'] = (float)$customer->affiliateBonus();
                $row['to_burn'] = shopBurningbonusPlugin::calcBurnAmount($row['to_burn'], $row['actual_balance']);
                if (0 >= $row['to_burn']) continue;
                // по локали покупателя диалог показывает, какая языковая версия ему уйдёт
                $this->response[] = $row + ['customer_name' => $customer->getName(), 'locale' => (string)$customer->getLocale()];
                $count++;
            }
        } catch (Exception $exception) {
            $this->errors = [$exception->getMessage(), $exception->getCode()];
        }
    }

    /**
     * @return shopBurningbonusNotificationsModel
     */
    protected function getModel(): shopBurningbonusNotificationsModel
    {
        return $this->Notifications ?? $this->Notifications = new shopBurningbonusNotificationsModel;
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
