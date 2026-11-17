<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 * @ControllerAction notifications/default
 */
class shopBurningbonusPluginNotificationsAction extends shopMarketingSettingsViewAction
{
    /** @var shopBurningbonusNotificationsModel|null */
    protected ?shopBurningbonusNotificationsModel $notifications_model = null;

    /**
     * @return void
     * @throws waException
     */
    public function execute()
    {
        $config = wa('shop')->getConfig();
        $locales = $this->getLocales();

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $references = [
            'default_email_address' => $config->getGeneralSettings('email'),
            'notification_template' => $this->getNotificationTemplate(),
            'locales'               => $locales,
            'text_templates'        => $this->getTextTemplates(array_column($locales, 'id')),
        ];

        $notifications_list = $this->getList();

        // debug-режим: не кэшируем бандл, пока его пересобирают
        $plugin_version = wa('shop')->getPlugin('burningbonus')->getVersion() . (waSystemConfig::isDebug() ? '.' . time() : '');
        $ui = wa()->whichUI('shop');

        $this->view->assign([
            'references'         => $references,
            'js_locale'          => shopBurningbonusPluginJsLocale::getStrings(),
            'notifications_list' => $notifications_list,
            'plugin_version'     => $plugin_version,
            'ui'                 => $ui,
        ]);
    }

    /**
     * @return list<array{id:int, name:string, transport:string, locales:string[]}>
     */
    protected function getList(): array
    {
        $data = (array)$this->getModel()->select('*')->order('id')->fetchAll();
        $versions = (new shopBurningbonusNotificationTextsModel())->getByNotification(array_column($data, 'id'));

        return array_values(array_map(fn($row) => [
            'id'        => (int)$row['id'],
            'name'      => (string)$row['name'],
            'transport' => (string)$row['transport'],
            // языки версий для значков в списке, версия по умолчанию не в счёт
            'locales'   => array_values(array_filter(array_column($versions[(int)$row['id']] ?? [], 'locale'))),
        ], $data));
    }

    /**
     * @return shopBurningbonusNotificationsModel
     */
    protected function getModel(): shopBurningbonusNotificationsModel
    {
        return $this->notifications_model ?? $this->notifications_model = new shopBurningbonusNotificationsModel;
    }

    /**
     * @return array
     */
    protected function getNotificationTemplate(): array
    {
        $template = $this->getModel()->getEmptyRow();
        $template['id'] = $template['scheduled_time'] = null;
        $template['status'] = true;
        $template['schedule_day'] = 1;

        return $template;
    }

    /**
     * Языки установки для языковых версий, по названию
     *
     * @return list<array{id:string, name:string, code:string}>
     */
    protected function getLocales(): array
    {
        $all = (array)waLocale::getAll(true);
        $name_count = array_count_values(array_map(fn($info) => (string)($info['name'] ?? ''), $all));

        $result = [];
        foreach ($all as $locale => $info) {
            $name = (string)($info['name'] ?? $locale);
            // одинаковые названия (English для en_US и en_GB) различаем по региону
            if (($name_count[$name] ?? 0) > 1 && !empty($info['region'])) $name .= " ({$info['region']})";
            $result[] = [
                'id'   => (string)$locale,
                'name' => $name,
                'code' => strtoupper(substr((string)$locale, 0, 2)),
            ];
        }
        usort($result, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $result;
    }

    /**
     * Тексты по умолчанию для новых версий: тема и текст для email и SMS из templates/notification-templates/.
     *
     * Под ключом '' — для версии по умолчанию на языке пользователя, под кодами локалей — для языков,
     * у которых есть свой каталог шаблонов. Английские шаблоны лежат в корне каталога, они же для en_US.
     * Для языков без шаблонов редактор копирует версию по умолчанию.
     *
     * @param string[] $locales
     * @return array<string, array{subject:string, email:string, sms:string}>
     */
    protected function getTextTemplates(array $locales): array
    {
        try {
            $dir = wa('shop')->getAppPath('plugins/burningbonus/templates/notification-templates/');
            $period = wa('shop')->getPlugin('burningbonus')->getSettings('period');
        } catch (waException $e) {
            return ['' => ['subject' => '', 'email' => '', 'sms' => '']];
        }
        $period = in_array($period, ['monthly', 'weekly'], true) ? $period : 'monthly';

        $read = function (string $locale, string $name) use ($dir): ?string {
            $file = '' === $locale ? "$dir$name.html" : "$dir$locale/$name.html";
            return is_file($file) && is_readable($file) ? (string)file_get_contents($file) : null;
        };
        $load = function (string $locale) use ($read, $period): ?array {
            $result = [
                'subject' => $read($locale, 'email-subject'),
                'email'   => $read($locale, "email-$period"),
                'sms'     => $read($locale, "sms-$period"),
            ];
            if (null === $result['email'] && null === $result['sms']) return null;
            return array_map(fn($text) => trim((string)$text), $result);
        };

        $result = [];
        foreach ($locales as $locale) {
            $templates = 'en_US' === $locale ? $load('') : $load($locale);
            if ($templates) $result[$locale] = $templates;
        }

        // версия по умолчанию — на языке пользователя, если для него нет шаблонов — английская
        $result[''] = $result[wa()->getLocale()] ?? $load('') ?? ['subject' => '', 'email' => '', 'sms' => ''];

        return $result;
    }
}
