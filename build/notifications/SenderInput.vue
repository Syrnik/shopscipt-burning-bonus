<template>
  <wa-field name="Отправитель">
    <div class="value">
      <select v-model="senders[transport].type" @change="EmitChanges">
        <option v-for="opt in options[transport]" :value="opt.value">{{ opt.title }}</option>
      </select>
      <input v-if="senders[transport].type === 'custom'" :type="transport==='email' ? 'email' :'text'" class="long"
             v-model.trim="senders[transport].value"
             @input="EmitChanges">
    </div>
  </wa-field>
</template>

<script>
export default {
  name: "SenderInput"
}
</script>

<script setup>
import WaField from '../components/wa-form/wa-field';
import {inject, reactive, watch} from "vue";

const references = inject('references');

const props = defineProps(['transport', 'modelValue']);
const emits = defineEmits(['update:modelValue']);

const senders = reactive({
  email: {
    value: props.transport === 'email' ? (props.modelValue ?? '') : '',
    type: (props.transport === 'email') && !!props.modelValue ? 'custom' : 'default'
  },
  sms: {
    value: props.transport === 'sms' ? (props.modelValue ?? '') : '',
    type: (props.transport === 'sms') && !!props.modelValue ? 'custom' : 'default'
  }
});

const options = {
  email: [
    {value: 'default', title: references.default_email_address ?? 'Адрес по-умолчанию'},
    {value: 'custom', title: 'Другой email…'}
  ],
  sms: [
    {value: 'default', title: 'По умолчанию (назначается SMS-шлюзом)'},
    {value: 'custom', title: 'Другой ID…'}
  ],
};

function EmitChanges() {
  emits('update:modelValue', senders[props.transport].type === 'default' ? '' : senders[props.transport].value);
}

watch(() => props.transport, () => {
  EmitChanges();
});

if (typeof props.modelValue === 'undefined') EmitChanges();
</script>
