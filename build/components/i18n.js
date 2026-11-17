/**
 * Переводы для Vue-части плагина.
 *
 * Строки размечаются _wp() в Smarty-шаблоне (templates/actions/notifications/Notifications.html) и кладутся
 * в штатный словарь $.wa.locale под ключами 'burningbonus.<английская msgid>'. Ключи в вызовах t()/tn() —
 * только строковые литералы: перед сборкой build/check-locale-keys.js сверяет их со словарём в шаблоне.
 */

const PREFIX = 'burningbonus.';

function lookup(key) {
    const locale = window.$?.wa?.locale;
    return locale && Object.prototype.hasOwnProperty.call(locale, key) ? locale[key] : undefined;
}

function missing(key) {
    console.warn(`[burningbonus] missing translation: ${key}`);
    return key.startsWith(PREFIX) ? key.slice(PREFIX.length) : key;
}

/** Подстановка %s/%d по порядку, как sprintf для простых случаев */
function format(str, args) {
    let i = 0;
    return str.replace(/%[sd]/g, m => (i < args.length ? String(args[i++]) : m));
}

/**
 * Перевод строки
 * @param {string} key 'burningbonus.<msgid>'
 * @param {...(string|number)} args значения для %s/%d
 * @return {string}
 */
export function t(key, ...args) {
    const value = lookup(key);
    const str = value === undefined ? missing(key) : (Array.isArray(value) ? value[0] : value);
    return args.length ? format(str, args) : str;
}

/**
 * Множественная форма через штатный $_(n, key). В словаре под ключом — массив форм для 1, 2 и 5.
 * Правило выбора формы у $_ славянское: для английского числа 21, 31… получат единственное число.
 * @param {number} n
 * @param {string} key
 * @return {string}
 */
export function tn(n, key) {
    const value = lookup(key);
    let str;
    if (value === undefined) str = missing(key);
    else if (typeof value === 'string' || typeof window.$_ !== 'function') str = Array.isArray(value) ? value[0] : value;
    else str = window.$_(n, key);
    return str.replace(/%d/g, String(n));
}

/**
 * Разбивает переведённую строку по маркерам {name}: 'после {time}' → ['после ', {slot: 'time'}, '']
 * @param {string} str
 * @return {Array<string|{slot: string}>}
 */
export function splitMarkers(str) {
    return str.split(/\{(\w+)}/).map((part, i) => (i % 2 ? {slot: part} : part));
}

/**
 * Текст до и после единственного маркера: для разметки, где элемент нельзя передать слотом
 * (например, радиокнопка с подписью и поле ввода после неё)
 * @param {string} str
 * @param {string} marker
 * @return {[string, string]}
 */
export function around(str, marker) {
    const pos = str.indexOf(`{${marker}}`);
    if (pos < 0) return [str, ''];
    return [str.slice(0, pos), str.slice(pos + marker.length + 2)];
}
