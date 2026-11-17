<template>
  {{ after[0] }}
  <wa-select>
    <select :value="modelValue ?? ''" @change="emit('update:modelValue', $event.target.value || null)">
      <option value="">{{ t('burningbonus.any time') }}</option>
      <option v-for="time in options" :value="time">{{ time }}</option>
    </select>
  </wa-select>
  {{ after[1] }}
  <br v-if="!ui2"><span class="hint" :class="{'s-burningbonus-block-hint': ui2}">
    <i18n-text v-if="modelValue"
               :text="t('burningbonus.Notifications are sent as soon as the script runs on the specified day after {time}.')">
      <template #time><b>{{ modelValue }}</b></template>
    </i18n-text>
    <template v-else>{{ t('burningbonus.Notifications are sent as soon as the script runs on the specified day.') }}</template>
    {{ t('burningbonus.If the script does not run on that day, the notifications will not be sent.') }}</span>
</template>

<script setup>
import {inject} from "vue";
import WaSelect from "../components/wa-form/wa-select.vue";
import I18nText from "../components/i18n-text";
import {around, t} from "../components/i18n";

defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue']);
const ui2 = inject('ui2', false);
// текст до и после списка со временем: порядок слов задаёт перевод
const after = around(t('burningbonus.after {time}'), 'time');
const options = [];
for (let i = 0; i < 24; i = i + 2) {
  const hr = ('0' + i).slice(-2);
  options.push(hr + ':00');
}
</script>
