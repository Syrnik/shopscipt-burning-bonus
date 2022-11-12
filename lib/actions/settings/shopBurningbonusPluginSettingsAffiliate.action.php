<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * Настройки для раздела "Партнерская программа"
 */
class shopBurningbonusPluginSettingsAffiliateAction extends waViewAction
{
    /**
     * @return void
     * @throws waRightsException
     * @throws waException
     */
    public function execute()
    {
        if (!$this->getUser()->getRights('shop', 'settings'))
            throw new waRightsException(_w('Access denied'));

        $settings = wa('shop')->getPlugin('burningbonus')->getSettings();

        $this->view->assign(compact(['settings']));
    }
}
