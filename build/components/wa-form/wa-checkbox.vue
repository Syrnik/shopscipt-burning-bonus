<template>
  <span v-if="ui2" class="wa-checkbox"><input type="checkbox" v-model="val"><span><span class="icon"><i
      class="fas fa-check"></i></span></span></span>
  <input v-else type="checkbox" v-model="val">
</template>

<script setup>
import {computed, inject} from "vue";

const props = defineProps({modelValue: {}});
const emit = defineEmits(['update:modelValue']);
const ui2 = inject('ui2', false);

const val = computed({
  get: () => !!props.modelValue,
  set: v => emit('update:modelValue', v)
});
</script>
