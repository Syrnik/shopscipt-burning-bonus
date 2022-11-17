<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

return [
    'name'          => 'Сгорание бонусов',
    'img'           => 'img/burningbonus.gif',
    'version'       => '1.0.0',
    'vendor'        => '670917',
    'shop_settings' => true,
    'handlers'      => [
        'backend_settings_affiliate' => 'backendSettingsAffiliateHandler',
        'backend_marketing_sidebar'  => 'backendMarketingSidebarHandler'
    ],
];
