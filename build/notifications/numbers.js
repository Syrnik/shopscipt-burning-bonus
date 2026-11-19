/**
 * Форматирование бонусных сумм по разделителям локали бэкенда — как wa_format_number в shop.
 * Разделители приходят из PHP в references.number_format: {decimal_point: ',', thousands_sep: ' '}.
 */

/**
 * До 4 знаков после запятой, без хвостовых нулей: 437.35 → «437,35», 1110 → «1 110»
 * @param {number|string} value
 * @param {{decimal_point?: string, thousands_sep?: string}} [format]
 * @return {string}
 */
export function formatNumber(value, format = {}) {
    const number = Number(value);
    if (!Number.isFinite(number)) return '';

    const decimalPoint = format.decimal_point ?? '.';
    const thousandsSep = format.thousands_sep ?? '';

    // toFixed округляет и убирает хвост float-погрешности вроде 830 - 160.0000001
    const [int, frac = ''] = Math.abs(number).toFixed(4).split('.');
    const fraction = frac.replace(/0+$/, '');
    const sign = number < 0 && (int !== '0' || fraction) ? '-' : '';

    return sign + int.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep) + (fraction ? decimalPoint + fraction : '');
}
