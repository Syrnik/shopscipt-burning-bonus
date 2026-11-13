<template>
  <div v-if="wrapClass" :class="wrapClass"><slot/></div>
  <slot v-else/>
</template>

<script setup>
import {computed, inject} from "vue";

/**
 * Обёртка, которая нужна только в одном из интерфейсов: если для текущего UI класс не задан,
 * содержимое выводится без лишнего div, и разметка другого интерфейса не меняется.
 */
const props = defineProps({ui2: String, legacy: String});
const isUi2 = inject('ui2', false);
const wrapClass = computed(() => isUi2 ? props.ui2 : props.legacy);
</script>
