<template>
  <wa-field :name="t('burningbonus.Message language')">
    <div class="value">
      <div class="s-burningbonus-version-line">
        <wa-select>
          <select :value="modelValue" @change="emit('update:modelValue', $event.target.value)">
            <option v-for="v in sortedVersions" :key="v.locale" :value="v.locale">{{ versionName(v.locale) }}</option>
          </select>
        </wa-select>
        <wa-select v-if="available.length">
          <select ref="addSelect" :class="{'s-burningbonus-add-language': !ui2}" @change="add($event.target.value)">
            <option value="" selected>{{ t('burningbonus.+ Add language') }}</option>
            <option v-for="l in available" :key="l.id" :value="l.id">{{ l.name }}</option>
          </select>
        </wa-select>
        <button v-if="ui2 && modelValue" type="button" class="button light-gray small" @click.prevent="remove">
          <wa-icon name="delete"/>
          {{ t('burningbonus.Delete version') }}
        </button>
        <a v-else-if="modelValue" href="#" class="s-burningbonus-delete-version" @click.prevent="remove">
          <wa-icon name="delete"/>{{ t('burningbonus.Delete version') }}</a>
      </div>
      <span v-if="!modelValue" class="hint s-burningbonus-block-hint">
        {{ t('burningbonus.Customers whose language has no version of its own receive this text.') }}
      </span>
    </div>
  </wa-field>
</template>

<script>
export default {
  name: "VersionSelector"
}
</script>

<script setup>
import WaField from '../components/wa-form/wa-field.vue';
import WaSelect from '../components/wa-form/wa-select.vue';
import WaIcon from '../components/wa-form/wa-icon.vue';
import {computed, inject, ref} from "vue";
import {t} from "../components/i18n";
import {localeName} from "./locales";

/**
 * Переключатель языковых версий уведомления: выбор версии, добавление языка, удаление версии.
 * Сами версии меняет родитель по событиям add/remove.
 */
const props = defineProps({
  /** @type {Array<{locale: string}>} */
  versions: {type: Array, required: true},
  /** локаль выбранной версии, '' — версия по умолчанию */
  modelValue: {type: String, default: ''}
});
const emit = defineEmits(['update:modelValue', 'add', 'remove']);

const references = inject('references');
const ui2 = inject('ui2', false);
const addSelect = ref(null);

const versionName = locale => locale ? localeName(references, locale) : t('burningbonus.Default');

/** По умолчанию — первой, остальные по названию языка */
const sortedVersions = computed(() => [...props.versions].sort((a, b) => {
  if (!a.locale || !b.locale) return a.locale ? 1 : (b.locale ? -1 : 0);
  return versionName(a.locale).localeCompare(versionName(b.locale));
}));

/** Языки установки, для которых версии ещё нет */
const available = computed(() => (references.locales ?? []).filter(l => !props.versions.some(v => v.locale === l.id)));

function add(locale) {
  // список — это меню действий: после выбора возвращаем его к «+ Добавить язык»
  if (addSelect.value) addSelect.value.value = '';
  if (locale) emit('add', locale);
}

function remove() {
  const question = t('burningbonus.Delete the %s version?', versionName(props.modelValue));
  const confirmed = ui2 && $.waDialog && $.waDialog.confirm
      ? new Promise(resolve => $.waDialog.confirm({
        title: question,
        success_button_title: t('burningbonus.Delete'),
        success_button_class: 'danger',
        cancel_button_title: t('burningbonus.Cancel'),
        cancel_button_class: 'light-gray',
        onSuccess: () => resolve(true),
        onCancel: () => resolve(false)
      }))
      : Promise.resolve(window.confirm(question));
  const locale = props.modelValue;
  confirmed.then(yes => yes && emit('remove', locale));
}
</script>
