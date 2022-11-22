<template>
  <select :value="modelValue" @change="emit('update:modelValue', $event.target.value)">
    <option value="">не важно</option>
    <option v-for="t in options" :value="t">{{ t }}</option>
  </select><br><span class="hint">Как только скрипт в указанный день запустится<template
    v-if="modelValue"> после <b>{{ modelValue }}</b></template>, так
  уведомления и отправятся. Если в указанный день скрипт не отработает, то и уведомления уже не отправятся.</span>
</template>

<script setup>
const props = defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue']);
const options = [];
for (let i = 0; i < 24; i=i+2) {
  const hr = ('0' + i).slice(-2);
  options.push(hr + ':00');
}
</script>
