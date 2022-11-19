<?php
/**
 * Main plugin class
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

/**
 *
 */
class shopBurningbonusPlugin extends shopPlugin
{
    /**
     * @param $name
     * @return mixed|null
     */
    public function getSettings($name = null)
    {
        if (null === $this->settings) {
            parent::getSettings();
            $this->settings['lifetime'] = max(0, intval($this->settings['lifetime']));
        }

        return parent::getSettings($name);
    }

    /**
     * @EventHandler backend_settings_affiliate
     * @return string[]
     */
    public function backendSettingsAffiliateHandler(): array
    {
        return [
            'id'   => 'burningbonus',
            'name' => 'Сгорание бонусов',
            'url'  => '?plugin=burningbonus&module=settings&action=affiliate'
        ];
    }

    /**
     * @EventHandler backend_marketing_sidebar
     * @return string[]
     * @throws waException
     */
    public function backendMarketingSidebarHandler(): array
    {
        return [
            'settings_li' => '<li><a href="' .
                wa()->getAppUrl('shop') .
                '?plugin=burningbonus&module=notifications"><i class="icon16 ss notification-bw"></i><span class="s-name">' .
                _wp('Уведомления о сгорании бонусов') .
                '</span></a></li>'
        ];
    }
}
