<template>
  <span v-if="ui2" class="wa-radio"><input type="radio" :value="value" v-model="selection"
                                           :disabled="disabled"><span></span></span>
  <input v-else type="radio" :value="value" v-model="selection" :disabled="disabled">
</template>

<script setup>
import {computed, inject} from "vue";

const props = defineProps({modelValue: {}, value: {}, disabled: {type: Boolean, default: false}});
const emit = defineEmits(['update:modelValue']);
const ui2 = inject('ui2', false);

const selection = computed({
  get: () => props.modelValue,
  set: v => emit('update:modelValue', v)
});
</script>
