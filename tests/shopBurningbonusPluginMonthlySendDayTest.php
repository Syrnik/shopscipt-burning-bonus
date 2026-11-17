<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * shopBurningbonusPluginNotifyTask::isMonthlySendDay() — уведомление на 29–31 число
 * в коротких месяцах уходит в последний день месяца. BURBON-965
 */
class shopBurningbonusPluginMonthlySendDayTest extends TestCase
{
    /**
     * @dataProvider provider
     * @throws Exception
     */
    public function testIsMonthlySendDay(int $schedule_day, string $today, bool $expected): void
    {
        $this->assertSame($expected, shopBurningbonusPluginNotifyTask::isMonthlySendDay($schedule_day, new DateTime($today)));
    }

    public static function provider(): array
    {
        return [
            'обычный день совпадает' => [25, '2026-11-25', true],
            'обычный день не совпадает' => [25, '2026-11-24', false],
            '1-е число' => [1, '2026-12-01', true],

            '31-е в 31-дневном месяце' => [31, '2026-12-31', true],
            '31-е в 31-дневном месяце, 30-е число' => [31, '2026-12-30', false],
            '31-е в 30-дневном месяце — 30-го' => [31, '2026-11-30', true],
            '31-е в 30-дневном месяце — 29-го' => [31, '2026-11-29', false],
            '30-е в 30-дневном месяце' => [30, '2026-11-30', true],

            '31-е в феврале — 28-го' => [31, '2027-02-28', true],
            '30-е в феврале — 28-го' => [30, '2027-02-28', true],
            '29-е в феврале — 28-го' => [29, '2027-02-28', true],
            '28-е в феврале' => [28, '2027-02-28', true],
            '28-е в феврале — 27-го' => [28, '2027-02-27', false],

            '31-е в високосном феврале — 29-го' => [31, '2028-02-29', true],
            '31-е в високосном феврале — 28-го' => [31, '2028-02-28', false],
            '29-е в високосном феврале' => [29, '2028-02-29', true],
            '28-е в високосном феврале — 29-го' => [28, '2028-02-29', false],
        ];
    }
}
