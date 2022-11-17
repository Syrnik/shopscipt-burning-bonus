<template>
  <wa-field name="Включено">
    <wa-simple-checkbox :model-value="modelValue" @update:modelValue="emits('update:modelValue', $event)" />
  </wa-field>
</template>

<script>
export default {
  name: "StatusSwitch"
}
</script>

<script setup>

import WaField from '../components/wa-form/wa-field'
import WaSimpleCheckbox from '../components/wa-form/wa-simple-checkbox'

const props = defineProps({modelValue: {type: Boolean}});
const emits = defineEmits(['update:modelValue']);
</script>
