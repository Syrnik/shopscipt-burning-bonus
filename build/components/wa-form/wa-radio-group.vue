<template>
  <div class="value no-shift">
    <slot name="header"/>
    <div class="s-burningbonus-line" v-for="opt in options"><label
        :class="{'disabled':!!opt.disabled}"><wa-radio :value="opt.value" :model-value="modelValue"
                                                       :disabled="!!opt.disabled"
                                                       @update:modelValue="emit('update:modelValue', $event)"/>
      <wa-icon v-if="opt.icon" :name="opt.icon" class="s-burningbonus-option-icon"/>
      {{ opt.title }} <span class="hint" v-if="!!opt.description" v-html="' &mdash; ' + opt.description"></span></label>
    </div>
    <slot/>
  </div>
</template>

<script setup>
import WaRadio from "./wa-radio.vue";
import WaIcon from "./wa-icon.vue";

defineProps({
  options: {type: Array, default: () => []},
  modelValue: {}
});

const emit = defineEmits(['update:modelValue']);
</script>
