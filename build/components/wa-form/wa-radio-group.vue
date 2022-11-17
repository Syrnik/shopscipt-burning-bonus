<template>
  <slot name="header"/>
  <div class="value" v-for="(opt,idx) in options" :class="{'no-shift': !idx && !$slots.header}"><label
      :class="{'disabled':!!opt.disabled}"><input
      type="radio" :value="opt.value" v-model="selection" :disabled="!!opt.disabled"><template v-if="opt.iconClass"><i :class="opt.iconClass"></i></template>{{
      opt.title
    }} <span class="hint" v-if="!!opt.description" v-html="' &mdash; ' + opt.description"></span></label>
  </div>
  <slot/>
</template>

<script setup>
import {computed} from "vue";

const props = defineProps({
  options: {type: Array, default: () => []},
  modelValue: {}
});

const emit = defineEmits(['update:modelValue']);

const selection = computed({
  get() {
    return props.modelValue;
  },
  set(v) {
    emit('update:modelValue', v);
  }
});

</script>
