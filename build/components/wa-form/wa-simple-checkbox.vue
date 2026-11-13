<template>
  <div class="value no-shift"><label><wa-checkbox :model-value="modelValue"
                                                  @update:modelValue="emit('update:modelValue', $event)"/>
    <template v-if="!!$slots.default"> &mdash; </template>
    <slot/></label>
    <br v-if="!!$slots.description">
    <slot name="description"/>
  </div>
</template>

<script setup>
import WaCheckbox from "./wa-checkbox.vue";

defineProps({modelValue: {}});
const emit = defineEmits(['update:modelValue']);
</script>
