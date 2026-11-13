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
        // то же право, что у самой «Программы лояльности» и у сохранения этих настроек
        if (!$this->getUser()->getRights('shop', 'setup_marketing'))
            throw new waRightsException(_ws('Access denied'));

        $settings = wa('shop')->getPlugin('burningbonus')->getSettings();
        $info = [
            'root_path' => wa()->getConfig()->getRootPath()
        ];

        $this->view->assign(compact(['settings', 'info']));
    }
}
