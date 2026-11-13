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
        if (!$settings || !is_array($settings)) {
            return;
        }

        // сохраняем только известные настройки (см. lib/config/settings.php)
        $period = (string)($settings['period'] ?? '');
        if (!in_array($period, ['never', 'weekly', 'monthly'], true)) {
            $this->errors = ['Неизвестная периодичность сгорания'];
            return;
        }

        $delay = trim((string)($settings['delay'] ?? ''));
        if ('' !== $delay) {
            $date = DateTime::createFromFormat('!Y-m-d', $delay);
            if (!$date || $date->format('Y-m-d') !== $delay) {
                $this->errors = ['Дата начала списания указана неверно'];
                return;
            }
        }

        $values = [
            'lifetime' => max(0, (int)($settings['lifetime'] ?? 0)) ?: 365,
            'period'   => $period,
            'delay'    => $delay,
        ];

        $asm = new waAppSettingsModel();
        foreach ($values as $key => $value) {
            $asm->set('shop.burningbonus', $key, $value);
        }
    }
}
