<template>
  <wa-field name="Когда отправлять">
    <div class="value"><input type="radio" value="monthly" v-model="schedule_type" @change.prevent="emitUpdates">
      Каждое <input type="number" min="1" max="31" step="1" class="short numerical"
                    v-model.number="schedule_days.monthly" @input="emitUpdates"
                    :disabled="schedule_type !== 'monthly'" required> число
      месяца
    </div>
    <div class="value"><input type="radio" value="weekly" v-model="schedule_type" @change.prevent="emitUpdates"> По
      <select v-model="schedule_days.weekly" @change="emitUpdates" :disabled="schedule_type !== 'weekly'" required>
        <option v-for="d in dow" :value="d.day">{{ d.name }}</option>
      </select></div>
  </wa-field>
</template>

<script setup>
import WaField from "../components/wa-form/wa-field";
import {reactive, ref} from "vue";

const props = defineProps({
  day: {type: Number, default: 1},
  type: {type: String, default: 'monthly'}
});

const emit = defineEmits(['update:type', 'update:day']);

const schedule_type = ref(props.type);

const schedule_days = reactive({
  monthly: props.type === 'monthly' ? props.day : 1,
  weekly: props.type === 'weekly' ? props.day : 1
});

const dow = [
  {day: 1, name: 'понедельникам'},
  {day: 2, name: 'вторникам'},
  {day: 3, name: 'средам'},
  {day: 4, name: 'четвергам'},
  {day: 5, name: 'пятницам'},
  {day: 6, name: 'субботам'},
  {day: 7, name: 'воскресеньям'},
];

function emitUpdates() {
  emit('update:type', schedule_type.value);
  emit('update:day', schedule_days[schedule_type.value]);
}

</script>
