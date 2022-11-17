<template>
  <Form.Field name="Транспорт">
    <Form.RadioGroup v-if="allowChange" :options="options" :model-value="modelValue"
                     @update:modelValue="emits('update:modelValue', $event)"/>
    <div class="value" v-else><template v-if="transport.iconClass"><i :class="transport.iconClass"></i></template>{{ transport.title }}</div>
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

const props = defineProps(['allowChange', 'modelValue']);
const emits = defineEmits(['update:modelValue']);

const options = [
  {value: 'email', title: 'E-mail', iconClass: ['icon16', 'email']},
  {value: 'sms', title: 'SMS', iconClass: ['icon16', 'mobile']}
];

const transport = computed(() => props.modelValue ? options.find(o => o.value === props.modelValue) : {});

</script>
