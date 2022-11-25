<template>
  <wa-field name="Когда отправлять">
    <div class="value"><input type="radio" value="monthly" v-model="schedule_type"
                              @change.prevent="emitUpdate">
      Каждое <input type="number" min="1" max="31" step="1" class="short numerical"
                    v-model.number="month_day" @input="emitUpdate"
                    :disabled="schedule_type !== 'monthly'" required> число
      месяца
    </div>
    <div class="value"><input type="radio" value="weekly" v-model="schedule_type"
                              @change.prevent="emitUpdate"> По
      <select v-model="week_day" @change="emitUpdate" :disabled="schedule_type !== 'weekly'" required>
        <option v-for="d in dow" :value="d.day" :selected="d.day === week_day">{{ d.name }}</option>
      </select></div>
  </wa-field>
</template>

<script setup>
import WaField from "../components/wa-form/wa-field";
import {ref, watch} from "vue";

const props = defineProps({
  value: {type: Object, default: () => ({day: 1, type: 'monthly'})},
});

const emit = defineEmits(['update']);

const schedule_type = ref(props.value.type);

const week_day = ref(schedule_type.value === 'weekly' ? props.value.day : 1);
const month_day = ref(schedule_type.value === 'monthly' ? props.value.day : 1)

const dow = [
  {day: 1, name: 'понедельникам'},
  {day: 2, name: 'вторникам'},
  {day: 3, name: 'средам'},
  {day: 4, name: 'четвергам'},
  {day: 5, name: 'пятницам'},
  {day: 6, name: 'субботам'},
  {day: 7, name: 'воскресеньям'},
];

function emitUpdate() {
  emit('update', {type: schedule_type.value, day: schedule_type.value === 'monthly' ? month_day.value : week_day.value});
}

watch(() => props.value, v => {
  schedule_type.value = v.type;
  if(v.type === 'monthly') month_day.value=v.day;
  else week_day.value=v.day;
})

</script>
