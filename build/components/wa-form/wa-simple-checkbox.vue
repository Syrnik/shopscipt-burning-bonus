<template>
  <div class="value no-shift"><input type="checkbox"
                        v-model="val">
    <template v-if="!!$slots.default"> &mdash; </template>
    <slot/>
    <br v-if="!!$slots.description">
    <slot name="description"/>
  </div>
</template>

<script setup>
import {computed} from "vue";

const props = defineProps({
  modelValue: {},
})

const emit = defineEmits(['update:modelValue'])

const val = computed({
  get() {
    return props.modelValue;
  },
  set(v) {
    emit('update:modelValue', v);
  }
})
</script>
