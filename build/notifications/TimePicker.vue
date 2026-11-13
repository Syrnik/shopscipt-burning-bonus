<template>
  <wa-select>
    <select :value="modelValue ?? ''" @change="emit('update:modelValue', $event.target.value || null)">
      <option value="">не важно</option>
      <option v-for="t in options" :value="t">{{ t }}</option>
    </select>
  </wa-select>
  <br v-if="!ui2"><span class="hint" :class="{'s-burningbonus-block-hint': ui2}">Как только скрипт в указанный день запустится<template
    v-if="modelValue"> после <b>{{ modelValue }}</b></template>, так
  уведомления и отправятся. Если в указанный день скрипт не отработает, то и уведомления уже не отправятся.</span>
</template>

<script setup>
import {inject} from "vue";
import WaSelect from "../components/wa-form/wa-select.vue";

defineProps(['modelValue']);
const emit = defineEmits(['update:modelValue']);
const ui2 = inject('ui2', false);
const options = [];
for (let i = 0; i < 24; i = i + 2) {
  const hr = ('0' + i).slice(-2);
  options.push(hr + ':00');
}
</script>
