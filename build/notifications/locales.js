/**
 * Языки установки приходят в references.locales: [{id: 'en_US', name: 'English', code: 'EN'}, …].
 * Версия может остаться у языка, который потом отключили в настройках, — тогда показываем код локали.
 */

/**
 * @param {Object} references
 * @param {string} locale
 * @return {{id: string, name: string, code: string}|undefined}
 */
function find(references, locale) {
    return (references.locales ?? []).find(l => l.id === locale);
}

/**
 * Название языка: «English»
 * @param {Object} references
 * @param {string} locale
 * @return {string}
 */
export function localeName(references, locale) {
    return find(references, locale)?.name ?? locale;
}

/**
 * Короткий код для значков в списке уведомлений: «EN»
 * @param {Object} references
 * @param {string} locale
 * @return {string}
 */
export function localeCode(references, locale) {
    return find(references, locale)?.code ?? locale.slice(0, 2).toUpperCase();
}
