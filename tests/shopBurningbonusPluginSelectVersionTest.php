<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * shopBurningbonusPluginAbstractNotification::selectVersion() — языковая версия уведомления для покупателя. BURBON-971
 *
 * Локаль покупателя — waContact::getLocale(): язык из профиля, а если он не указан — язык Webasyst
 * по умолчанию (здесь ru_RU в роли системного).
 */
class shopBurningbonusPluginSelectVersionTest extends TestCase
{
    private const SYSTEM_LOCALE = 'ru_RU';

    /**
     * @dataProvider provider
     */
    public function testSelectVersion(array $locales, string $customer_locale, ?string $expected): void
    {
        $versions = array_map(fn(string $locale) => ['locale' => $locale, 'subject' => "subject $locale", 'body' => "body $locale"], $locales);

        $version = shopBurningbonusPluginAbstractNotification::selectVersion($versions, $customer_locale);

        $this->assertSame($expected, $version['locale'] ?? null);
        if (null !== $expected) $this->assertSame("body $expected", $version['body']);
    }

    public static function provider(): array
    {
        return [
            'локаль в профиле есть, версия для неё есть' => [['', 'en_US', 'ru_RU'], 'en_US', 'en_US'],
            'локаль в профиле есть, версии для неё нет' => [['', 'ru_RU'], 'de_DE', ''],
            'локали в профиле нет, версия на системном языке есть' => [['', 'en_US', self::SYSTEM_LOCALE], self::SYSTEM_LOCALE, self::SYSTEM_LOCALE],
            'локали в профиле нет, версии на системном языке нет' => [['', 'en_US'], self::SYSTEM_LOCALE, ''],
            'только версия по умолчанию' => [[''], 'en_US', ''],
            'версия по умолчанию не первой' => [['en_US', ''], 'de_DE', ''],
            'нет ни подходящей, ни по умолчанию' => [['en_US'], 'de_DE', null],
            'пустая локаль не выбирает версию по коду' => [['', 'en_US'], '', ''],
            'нет версий' => [[], 'en_US', null],
        ];
    }
}
