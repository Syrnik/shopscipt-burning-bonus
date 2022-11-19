<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * @ControllerAction settings/saveaffiliate
 */
class shopBurningbonusPluginSettingsSaveaffiliateController extends shopMarketingSettingsJsonController
{
    /**
     * @return void
     */
    public function execute(): void
    {
        $settings = waRequest::post('setting', [], waRequest::TYPE_ARRAY);
        $asm = new waAppSettingsModel();

        if ($settings && is_array($settings)) {
            if (!($settings['lifetime'] = intval($settings['lifetime'] ?? 0))) $settings['lifetime'] = 365;
            $settings['delay'] = trim($settings['delay'] ?? '');

            foreach ($settings as $key => $setting) {
                $asm->set('shop.burningbonus', $key, $setting);
            }

        }
    }
}
