<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * shopBurningbonusPlugin::calcClosestBurnDate() — ближайший день списания строго после сегодняшнего,
 * но не раньше даты отложенного старта (включительно). BURBON-964
 */
class shopBurningbonusPluginClosestBurnDateTest extends TestCase
{
    /**
     * @dataProvider provider
     * @throws Exception
     */
    public function testCalcClosestBurnDate(string $period, string $delay, string $today, ?string $expected): void
    {
        $date = shopBurningbonusPlugin::calcClosestBurnDate($period, $delay, new DateTime($today));

        $this->assertSame($expected, $date ? $date->format('Y-m-d') : null);
    }

    public static function provider(): array
    {
        // 2026-11-16 и 2026-11-23, 2026-11-30, 2026-12-07 — понедельники
        return [
            // Ежемесячно
            'monthly: без отложенного старта' => ['monthly', '', '2026-11-20', '2026-12-01'],
            'monthly: сегодня день списания — следующий' => ['monthly', '', '2026-12-01', '2027-01-01'],
            'monthly: последний день месяца' => ['monthly', '', '2026-11-30', '2026-12-01'],
            'monthly: delay — день списания (пример из задачи)' => ['monthly', '2026-12-01', '2026-11-20', '2026-12-01'],
            'monthly: delay за день до дня списания' => ['monthly', '2026-11-30', '2026-11-20', '2026-12-01'],
            'monthly: delay на день позже дня списания' => ['monthly', '2026-12-02', '2026-11-20', '2027-01-01'],
            'monthly: delay 31-го числа' => ['monthly', '2026-12-31', '2026-11-20', '2027-01-01'],
            'monthly: delay — завтра и день списания' => ['monthly', '2026-12-01', '2026-11-30', '2026-12-01'],
            'monthly: delay = сегодня' => ['monthly', '2026-11-20', '2026-11-20', '2026-12-01'],
            'monthly: delay = сегодня, день списания' => ['monthly', '2026-12-01', '2026-12-01', '2027-01-01'],
            'monthly: delay в прошлом' => ['monthly', '2026-10-01', '2026-11-20', '2026-12-01'],
            'monthly: delay через несколько месяцев' => ['monthly', '2027-03-01', '2026-11-20', '2027-03-01'],

            // Еженедельно
            'weekly: без отложенного старта' => ['weekly', '', '2026-11-18', '2026-11-23'],
            'weekly: сегодня понедельник — следующий' => ['weekly', '', '2026-11-16', '2026-11-23'],
            'weekly: воскресенье' => ['weekly', '', '2026-11-22', '2026-11-23'],
            'weekly: delay — понедельник' => ['weekly', '2026-11-30', '2026-11-18', '2026-11-30'],
            'weekly: delay в воскресенье перед понедельником' => ['weekly', '2026-11-29', '2026-11-18', '2026-11-30'],
            'weekly: delay во вторник' => ['weekly', '2026-12-01', '2026-11-18', '2026-12-07'],
            'weekly: delay = сегодня' => ['weekly', '2026-11-18', '2026-11-18', '2026-11-23'],
            'weekly: delay = сегодня, понедельник' => ['weekly', '2026-11-16', '2026-11-16', '2026-11-23'],
            'weekly: delay в прошлом' => ['weekly', '2026-11-02', '2026-11-18', '2026-11-23'],

            // Списание выключено
            'never' => ['never', '2026-12-01', '2026-11-20', null],
            'пустая периодичность' => ['', '', '2026-11-20', null],
        ];
    }

    /**
     * Время в $today не влияет на результат
     * @throws Exception
     */
    public function testTimeOfDayIsIgnored(): void
    {
        $date = shopBurningbonusPlugin::calcClosestBurnDate('monthly', '2026-12-01', new DateTime('2026-11-30 23:59:59'));

        $this->assertSame('2026-12-01', $date->format('Y-m-d'));
    }
}
