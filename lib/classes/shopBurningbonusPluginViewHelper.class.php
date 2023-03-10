<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * View helper
 * @property shopBurningbonusPlugin $plugin
 */
class shopBurningbonusPluginViewHelper extends waPluginViewHelper
{
    /**
     * Срок действия бонусных баллов
     *
     * @return int
     */
    public function duration(): int
    {
        try {
            // Плагин отключен
            if ('never' === $this->plugin()->getSettings('period')) return 0;
            return intval($this->plugin()->getSettings('lifetime') ?: 0);
        } catch (waException $e) {
            return 0;
        }
    }

    /**
     * Дата следующего сгорания бонусов
     *
     * @return string|null
     */
    public function next_burn(): ?string
    {
        try {
            $next_burn_date = $this->plugin->closestBurnDate();
        } catch (Exception $e) {
            return null;
        }

        return $next_burn_date ? $next_burn_date->format('Y-m-d') : null;
    }
}
