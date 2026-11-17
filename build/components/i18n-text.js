import {Fragment, h} from 'vue';
import {splitMarkers} from './i18n';

/**
 * Переведённая фраза с элементами внутри: маркеры {name} заменяются одноимёнными слотами.
 * Порядок слов задаёт перевод, а не шаблон компонента.
 *
 * <i18n-text :text="t('burningbonus.after {time}')"><template #time><b>{{ time }}</b></template></i18n-text>
 */
export default {
    name: 'I18nText',
    props: {text: {type: String, required: true}},
    setup(props, {slots}) {
        return () => h(Fragment, splitMarkers(props.text).map(part => {
            if (typeof part === 'string') return part;
            return slots[part.slot] ? h(Fragment, slots[part.slot]()) : `{${part.slot}}`;
        }));
    }
};
