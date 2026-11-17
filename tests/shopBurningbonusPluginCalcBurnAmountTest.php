<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * shopBurningbonusPlugin::calcBurnAmount() — сумма к сгоранию не больше реального баланса. BURBON-969
 */
class shopBurningbonusPluginCalcBurnAmountTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testCalcBurnAmount(float $to_burn, float $balance, float $expected): void
    {
        $this->assertSame($expected, shopBurningbonusPlugin::calcBurnAmount($to_burn, $balance));
    }

    public static function provider(): array
    {
        return [
            'баланс больше суммы' => [300.0, 800.0, 300.0],
            'баланс равен сумме' => [300.0, 300.0, 300.0],
            'баланс меньше суммы' => [6.8, 2.5, 2.5],
            'нулевой баланс' => [6.8, 0.0, 0.0],
            'отрицательный баланс' => [6.8, -10.0, 0.0],
            'сгорать нечему' => [0.0, 500.0, 0.0],
            'отрицательная сумма' => [-5.0, 500.0, 0.0],
        ];
    }
}
