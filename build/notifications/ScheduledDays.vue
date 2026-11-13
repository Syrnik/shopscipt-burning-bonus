<template>
  <wa-field name="Когда отправлять">
    <div class="value">
    <div class="s-burningbonus-line"><label><wa-radio value="monthly" v-model="schedule_type" @update:modelValue="emitUpdate"/>
      Каждое</label> <input type="number" min="1" max="31" step="1" class="numerical" :class="ui2 ? 'shortest' : 'short'"
                    v-model.number="month_day" @input="emitUpdate"
                    :disabled="schedule_type !== 'monthly'" required> число
      месяца
    </div>
    <div class="s-burningbonus-line"><label class="s-burningbonus-weekly-label"><wa-radio value="weekly" v-model="schedule_type"
                                                                          @update:modelValue="emitUpdate"/>
      По</label>
      <wa-select>
        <select v-model="week_day" @change="emitUpdate" :disabled="schedule_type !== 'weekly'" required>
          <option v-for="d in dow" :value="d.day">{{ d.name }}</option>
        </select>
      </wa-select></div>
    </div>
  </wa-field>
</template>

<script setup>
import WaField from "../components/wa-form/wa-field.vue";
import WaRadio from "../components/wa-form/wa-radio.vue";
import WaSelect from "../components/wa-form/wa-select.vue";
import {inject, ref, watch} from "vue";

const props = defineProps({
  value: {type: Object, default: () => ({day: 1, type: 'monthly'})},
});

const emit = defineEmits(['update']);
const ui2 = inject('ui2', false);

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
