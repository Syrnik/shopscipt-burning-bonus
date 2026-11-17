/**
 * Сверяет ключи переводов Vue-части со словарём в PHP перед сборкой бандла.
 *
 * - ключ используется во Vue (t('burningbonus.…') / tn(n, 'burningbonus.…') / $_(…)), но его нет
 *   в shopBurningbonusPluginJsLocale — ошибка, сборка останавливается;
 * - ключ есть в словаре, но нигде не используется — предупреждение;
 * - ключ в словаре не совпадает с msgid в _wp() рядом с ним — ошибка.
 *
 * Ключи во Vue должны быть строковыми литералами: собранные из частей ('burningbonus.' + name) скрипт не видит.
 */
import {readdirSync, readFileSync, statSync} from 'node:fs';
import {dirname, extname, join, relative} from 'node:path';
import {fileURLToPath} from 'node:url';

const buildDir = dirname(fileURLToPath(import.meta.url));
const pluginDir = join(buildDir, '..');
const dictionaryFile = join(pluginDir, 'lib/classes/shopBurningbonusPluginJsLocale.class.php');
const sourceDirs = ['notifications', 'components'].map(d => join(buildDir, d));
const sourceExt = new Set(['.vue', '.js', '.ts']);

const PREFIX = 'burningbonus.';

function listFiles(dir) {
    return readdirSync(dir).flatMap(name => {
        const path = join(dir, name);
        if (statSync(path).isDirectory()) return listFiles(path);
        return sourceExt.has(extname(name)) ? [path] : [];
    });
}

/** Значение строкового литерала JS/PHP без кавычек: учитываем только \' \" и \\ */
const unquote = s => s.replace(/\\(['"\\])/g, '$1');

// --- ключи, используемые во Vue
const used = new Map(); // key => ['file:line', ...]
const callRe = /\b(?:t|tn|\$_)\(\s*(?:[^'"()]*?,\s*)?(['"])(burningbonus\.(?:\\.|(?!\1).)*?)\1/g;
for (const file of sourceDirs.flatMap(listFiles)) {
    const text = readFileSync(file, 'utf8');
    for (const m of text.matchAll(callRe)) {
        const key = unquote(m[2]);
        const line = text.slice(0, m.index).split('\n').length;
        if (!used.has(key)) used.set(key, []);
        used.get(key).push(`${relative(pluginDir, file)}:${line}`);
    }
}

// --- словарь
// без комментариев: в PHPDoc словаря есть пример записи
const php = readFileSync(dictionaryFile, 'utf8').replace(/\/\*[\s\S]*?\*\//g, '').replace(/^\s*\/\/.*$/gm, '');
const defined = new Set();
const errors = [];
const entryRe = /'(burningbonus\.(?:\\.|[^'])*)'\s*=>\s*(?:_wp\(\s*'((?:\\.|[^'])*)'\s*\)|\[)/g;
for (const m of php.matchAll(entryRe)) {
    const key = unquote(m[1]);
    defined.add(key);
    if (m[2] !== undefined && key.slice(PREFIX.length) !== unquote(m[2])) {
        errors.push(`ключ не совпадает с msgid: '${key}' => _wp('${unquote(m[2])}')`);
    }
}

for (const [key, places] of used) {
    if (!defined.has(key)) errors.push(`нет в словаре: '${key}' (${places.join(', ')})`);
}
const unused = [...defined].filter(key => !used.has(key));

for (const key of unused) console.warn(`[check-locale-keys] не используется: '${key}'`);
if (errors.length) {
    for (const e of errors) console.error(`[check-locale-keys] ${e}`);
    console.error(`[check-locale-keys] словарь: ${relative(pluginDir, dictionaryFile)}`);
    process.exit(1);
}
console.log(`[check-locale-keys] ok: ${used.size} ключей во Vue, ${defined.size} в словаре`);
