<?php
/**
 * Языковые версии уведомлений (BURBON-971): тема и текст переезжают из shop_burningbonus_notifications
 * в shop_burningbonus_notification_texts, текущие становятся версией по умолчанию (locale = '').
 *
 * Каждый шаг проверяет, нужен ли он, поэтому миграцию можно запустить повторно после сбоя.
 */

$model = new waModel();

$schema_file = wa('shop')->getConfig()->getPluginPath('burningbonus') . '/lib/config/db.php';
$schema = include($schema_file);
if (!is_array($schema) || empty($schema['shop_burningbonus_notification_texts']))
    throw new waException('Shop/Burningbonus: Database schema isn\'t readable');

$model->createSchema(['shop_burningbonus_notification_texts' => $schema['shop_burningbonus_notification_texts']]);

$columns = $model->describe('shop_burningbonus_notifications');
$has_subject = isset($columns['subject']);
$has_body = isset($columns['body']);

if ($has_body) {
    $subject = $has_subject ? 'subject' : 'NULL';
    $model->exec(
        "INSERT IGNORE INTO shop_burningbonus_notification_texts (notification_id, locale, subject, body)
         SELECT id, '', $subject, body FROM shop_burningbonus_notifications"
    );
}

$drop = [];
if ($has_subject) $drop[] = 'DROP COLUMN subject';
if ($has_body) $drop[] = 'DROP COLUMN body';
if ($drop) {
    $model->exec('ALTER TABLE shop_burningbonus_notifications ' . implode(', ', $drop));
}

// Кэш метаданных у waModel свой для каждой таблицы, поэтому сбрасываем через модели обеих таблиц,
// иначе модели продолжат видеть удалённые subject/body и не увидят новую таблицу
(new shopBurningbonusNotificationsModel())->clearMetadataCache();
(new shopBurningbonusNotificationTextsModel())->clearMetadataCache();

// Файлы версии 1.0, которых больше нет: Инсталлер при обновлении их не удаляет.
// Старый шаблон из actions-legacy в UI 1.3 перекрыл бы общий templates/actions/notifications/Notifications.html
$plugin_path = wa('shop')->getConfig()->getPluginPath('burningbonus');
foreach (['js/notifications-settings.js', 'templates/actions-legacy/notifications'] as $obsolete) {
    try {
        waFiles::delete($plugin_path . '/' . $obsolete);
    } catch (waException $e) {
        waLog::log("Shop/Burningbonus: can't delete obsolete $obsolete: " . $e->getMessage(), 'shop/plugins/burningbonus/update.log');
    }
}
