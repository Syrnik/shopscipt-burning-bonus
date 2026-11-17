<template>
  <Form.Field :name="t('burningbonus.Delivery method')">
    <Form.RadioGroup v-if="allowChange" :options="options" :model-value="modelValue"
                     @update:modelValue="emits('update:modelValue', $event)"/>
    <div class="value" v-else>
      <Form.Icon v-if="transport.icon" :name="transport.icon" class="s-burningbonus-option-icon"/>
      {{ transport.title }}
    </div>
  </Form.Field>
</template>

<script>
export default {
  name: "TransportSelector"
}
</script>

<script setup>

import * as Form from '../components/wa-form';
import {computed} from "vue";
import {t} from "../components/i18n";

const props = defineProps(['allowChange', 'modelValue']);
const emits = defineEmits(['update:modelValue']);

const options = [
  {value: 'email', title: 'E-mail', icon: 'email'},
  {value: 'sms', title: 'SMS', icon: 'sms'}
];

const transport = computed(() => options.find(o => o.value === props.modelValue) ?? {});

</script>
