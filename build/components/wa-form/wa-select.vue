<template>
  <div v-if="ui2" class="wa-select"><slot/></div>
  <slot v-else/>
</template>

<script setup>
import {inject} from "vue";

const ui2 = inject('ui2', false);
</script>
