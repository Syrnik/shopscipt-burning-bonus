<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2026
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * Переводы для Vue-приложения экрана уведомлений.
 *
 * Шаблон кладёт их в штатный словарь $.wa.locale, во Vue они читаются через t()/tn() из build/components/i18n.js.
 * Ключ — 'burningbonus.' + msgid, msgid — строковый литерал в _wp(), чтобы его видел wa.php locale.
 * Маркеры {name} в строках — места для элементов разметки (поле ввода, выделенное время), их не переводят.
 * Множественные формы — массив из трёх строк для 1, 2 и 5, с %d вместо числа:
 *     'burningbonus.%d day' => [
 *         str_replace('1', '%d', _wp('%d day', '%d days', 1)),
 *         str_replace('2', '%d', _wp('%d day', '%d days', 2)),
 *         str_replace('5', '%d', _wp('%d day', '%d days', 5)),
 *     ],
 *
 * Перед сборкой бандла build/check-locale-keys.js сверяет ключи здесь с вызовами t()/tn() во Vue.
 */
class shopBurningbonusPluginJsLocale
{
    /**
     * @return array<string, string|string[]>
     */
    public static function getStrings(): array
    {
        return [
            'burningbonus.Bonus expiry notifications' => _wp('Bonus expiry notifications'),
            'burningbonus.Delete notification?' => _wp('Delete notification?'),
            'burningbonus.Delete' => _wp('Delete'),
            'burningbonus.Cancel' => _wp('Cancel'),
            'burningbonus.New notification' => _wp('New notification'),
            'burningbonus.<untitled>' => _wp('<untitled>'),
            'burningbonus.(untitled)' => _wp('(untitled)'),

            // форма уведомления
            'burningbonus.Notification name' => _wp('Notification name'),
            'burningbonus.Enabled' => _wp('Enabled'),
            'burningbonus.Delivery method' => _wp('Delivery method'),
            'burningbonus.Sender' => _wp('Sender'),
            'burningbonus.Default address' => _wp('Default address'),
            'burningbonus.Other email…' => _wp('Other email…'),
            'burningbonus.Default (set by the SMS gateway)' => _wp('Default (set by the SMS gateway)'),
            'burningbonus.Other ID…' => _wp('Other ID…'),
            'burningbonus.When to send' => _wp('When to send'),
            'burningbonus.On day {input} of each month' => _wp('On day {input} of each month'),
            'burningbonus.In months without day %d, the notification is sent on the last day of the month' => _wp('In months without day %d, the notification is sent on the last day of the month'),
            'burningbonus.Every Monday' => _wp('Every Monday'),
            'burningbonus.Every Tuesday' => _wp('Every Tuesday'),
            'burningbonus.Every Wednesday' => _wp('Every Wednesday'),
            'burningbonus.Every Thursday' => _wp('Every Thursday'),
            'burningbonus.Every Friday' => _wp('Every Friday'),
            'burningbonus.Every Saturday' => _wp('Every Saturday'),
            'burningbonus.Every Sunday' => _wp('Every Sunday'),
            'burningbonus.Sending time' => _wp('Sending time'),
            'burningbonus.after {time}' => _wp('after {time}'),
            'burningbonus.any time' => _wp('any time'),
            'burningbonus.Notifications are sent as soon as the script runs on the specified day.' => _wp('Notifications are sent as soon as the script runs on the specified day.'),
            'burningbonus.Notifications are sent as soon as the script runs on the specified day after {time}.' => _wp('Notifications are sent as soon as the script runs on the specified day after {time}.'),
            'burningbonus.If the script does not run on that day, the notifications will not be sent.' => _wp('If the script does not run on that day, the notifications will not be sent.'),
            'burningbonus.Message language' => _wp('Message language'),
            'burningbonus.Default' => _wp('Default'),
            'burningbonus.+ Add language' => _wp('+ Add language'),
            'burningbonus.Delete version' => _wp('Delete version'),
            'burningbonus.Delete the %s version?' => _wp('Delete the %s version?'),
            'burningbonus.Customers whose language has no version of its own receive this text.' => _wp('Customers whose language has no version of its own receive this text.'),
            'burningbonus.Message subject' => _wp('Message subject'),
            'burningbonus.Message text' => _wp('Message text'),
            'burningbonus.Save' => _wp('Save'),
            'burningbonus.Saved' => _wp('Saved'),
            'burningbonus.Test sending' => _wp('Test sending'),

            // пробная отправка
            'burningbonus.Test message' => _wp('Test message'),
            'burningbonus.To' => _wp('To'),
            'burningbonus.Select a contact to get test data from' => _wp('Select a contact to get test data from'),
            'burningbonus.Name' => _wp('Name'),
            'burningbonus.Current balance' => _wp('Current balance'),
            'burningbonus.Will expire' => _wp('Will expire'),
            'burningbonus.Will remain' => _wp('Will remain'),
            'burningbonus.Text version' => _wp('Text version'),
            'burningbonus.Test sending requires at least one customer whose bonuses expire at the next write-off. There are none at the moment.' => _wp('Test sending requires at least one customer whose bonuses expire at the next write-off. There are none at the moment.'),
            'burningbonus.Try again' => _wp('Try again'),
            'burningbonus.try again' => _wp('try again'),
            'burningbonus.Close' => _wp('Close'),
            'burningbonus.or' => _wp('or'),
            'burningbonus.cancel' => _wp('cancel'),
            'burningbonus.Error:' => _wp('Error:'),
            'burningbonus.Notification sent' => _wp('Notification sent'),

            // ошибки
            'burningbonus.Empty error list' => _wp('Empty error list'),
            'burningbonus.Controller execution error' => _wp('Controller execution error'),
            'burningbonus.Unknown error' => _wp('Unknown error'),
        ];
    }
}
