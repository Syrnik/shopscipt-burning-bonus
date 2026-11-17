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
            $settings = (array)parent::getSettings();
            $settings['lifetime'] = max(0, intval($settings['lifetime'] ?? 0));
            $this->settings = $settings;
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
            'id' => 'burningbonus',
            'name' => 'Сгорание бонусов',
            'url' => '?plugin=burningbonus&module=settings&action=affiliate',
        ];
    }

    /**
     * @EventHandler backend_marketing_sidebar
     * @return string[]
     * @throws waException
     */
    public function backendMarketingSidebarHandler()
    {
        if (version_compare(wa()->whichUI(), '2.0', '>=')) {
            return [];
        }
        return [
            'settings_li' => '<li><a href="' .
                wa()->getAppUrl('shop') .
                '?plugin=burningbonus&module=notifications"><i class="icon16 ss notification-bw"></i><span class="s-name">' .
                _wp('Уведомления о сгорании бонусов') .
                '</span></a></li>'
        ];
    }

    public function backendExtendedMenuHandler(array &$params): void
    {
        $id = $this->id;

        if (isset($params['menu']['marketing']['submenu'])) {
            $menu_item = [
                'name' => _wp('Уведомления о сгорании бонусов'),
                'url' => wa()->getAppUrl('shop') . '?plugin=burningbonus&module=notifications',
            ];

            $params['menu']['marketing']['submenu'][$id] = $menu_item;
        }
    }

    /**
     * @return bool
     */
    public function isBurningEnabled(): bool
    {
        return in_array($this->getSettings('period'), ['monthly', 'weekly'], true);
    }

    /**
     * Ближайший день списания после сегодняшнего с учётом отложенного старта
     *
     * @param DateTimeInterface|null $today по умолчанию — текущая дата
     * @return DateTime|null
     * @throws Exception
     */
    public function closestBurnDate(?DateTimeInterface $today = null): ?DateTime
    {
        if (!$this->isBurningEnabled()) {
            return null;
        }

        $delay = trim((string)$this->getSettings('delay'));

        // Отложенный старт прошёл — настройка больше не нужна. Сверяемся с реальной датой,
        // а не с $today: отладочный запуск с подменённой датой не должен стирать настройку
        if ($delay && $delay <= date('Y-m-d')) {
            self::getSettingsModel()->set($this->getSettingsKey(), 'delay', '');
        }

        return self::calcClosestBurnDate((string)$this->getSettings('period'), $delay, $today ?? new DateTime());
    }

    /**
     * Ближайший день списания строго после $today, но не раньше $delay (включительно).
     *
     * Правило совпадает с shopBurningbonusPluginBurnTask::isBurningDelayed(): до даты $delay
     * баллы не сгорают, а в сам день $delay списание выполняется, если это день списания.
     *
     * @param string $period 'weekly' — по понедельникам, 'monthly' — 1-го числа
     * @param string $delay дата отложенного старта Y-m-d или пустая строка
     * @param DateTimeInterface $today
     * @return DateTime|null null, если периодичность списания не задана
     * @throws Exception
     */
    public static function calcClosestBurnDate(string $period, string $delay, DateTimeInterface $today): ?DateTime
    {
        if (!in_array($period, ['weekly', 'monthly'], true)) {
            return null;
        }

        $start = new DateTime($today->format('Y-m-d'));
        $start->modify('+1 day');

        if ($delay && $delay > $start->format('Y-m-d')) {
            $start = new DateTime($delay);
        }

        if ('weekly' === $period) {
            return 1 === (int)$start->format('N') ? $start : $start->modify('next monday');
        }

        return 1 === (int)$start->format('j') ? $start : $start->modify('first day of next month');
    }
}
