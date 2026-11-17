<template>
  <wa-field :name="t('burningbonus.Sender')">
    <div class="value">
      <wa-select>
        <select v-model="type">
          <option v-for="opt in options[transport]" :value="opt.value">{{ opt.title }}</option>
        </select>
      </wa-select>
      <input v-if="custom" :type="transport==='email' ? 'email' :'text'" class="long s-burningbonus-sender"
             v-model.trim="sender" required>
    </div>
  </wa-field>
</template>

<script>
export default {
  name: "SenderInput"
}
</script>

<script setup>
import WaField from '../components/wa-form/wa-field.vue';
import WaSelect from '../components/wa-form/wa-select.vue';
import {computed, inject, ref, watch} from "vue";
import {t} from "../components/i18n";

const references = inject('references');

const props = defineProps({transport: {type: String, default: 'email'}, modelValue: {}});
const emits = defineEmits(['update:modelValue']);

/**
 * «Другой отправитель» может быть выбран, но ещё не заполнен, поэтому отдельный флаг.
 * При загрузке другого уведомления родитель пересоздаёт компонент через key.
 */
const custom = ref(!!props.modelValue);

const type = computed({
  get: () => custom.value ? 'custom' : 'default',
  set(v) {
    custom.value = v === 'custom';
    if (!custom.value) emits('update:modelValue', '');
  }
});

const sender = computed({
  get: () => props.modelValue ?? '',
  set: v => emits('update:modelValue', v)
});

const options = {
  email: [
    {value: 'default', title: references.default_email_address || t('burningbonus.Default address')},
    {value: 'custom', title: t('burningbonus.Other email…')}
  ],
  sms: [
    {value: 'default', title: t('burningbonus.Default (set by the SMS gateway)')},
    {value: 'custom', title: t('burningbonus.Other ID…')}
  ],
};

watch(() => props.modelValue, v => {
  if (v) custom.value = true;
});

// email-адрес не годится как ID отправителя SMS и наоборот
watch(() => props.transport, () => type.value = 'default');
</script>
