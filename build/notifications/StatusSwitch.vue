<template>
  <wa-field :name="t('burningbonus.Enabled')">
    <wa-simple-checkbox :model-value="modelValue" @update:modelValue="emits('update:modelValue', $event)" />
  </wa-field>
</template>

<script>
export default {
  name: "StatusSwitch"
}
</script>

<script setup>

import WaField from '../components/wa-form/wa-field.vue'
import WaSimpleCheckbox from '../components/wa-form/wa-simple-checkbox.vue'
import {t} from '../components/i18n'

defineProps({modelValue: {type: Boolean}});
const emits = defineEmits(['update:modelValue']);
</script>
