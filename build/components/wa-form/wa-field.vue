<template>
  <div class="field">
    <div class="name" v-if="!!htmlName" v-html="htmlName"></div>
    <div class="name" v-else-if="!!name">{{ name }}</div>
    <slot />
  </div>
</template>

<script setup>
defineProps(['htmlName', 'name'])
</script>
