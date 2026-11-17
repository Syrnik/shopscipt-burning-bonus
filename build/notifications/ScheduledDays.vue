<template>
  <wa-field :name="t('burningbonus.When to send')">
    <div class="value">
    <div class="s-burningbonus-line"><label><wa-radio value="monthly" v-model="schedule_type" @update:modelValue="emitUpdate"/>
      {{ monthly[0] }}</label> <input type="number" min="1" max="31" step="1" class="numerical" :class="ui2 ? 'shortest' : 'short'"
                    v-model.number="month_day" @input="emitUpdate"
                    :disabled="schedule_type !== 'monthly'" required> {{ monthly[1] }}
    </div>
    <div v-if="schedule_type === 'monthly' && month_day > 28" class="s-burningbonus-line hint">
      {{ t('burningbonus.In months without day %d, the notification is sent on the last day of the month', month_day) }}
    </div>
    <div class="s-burningbonus-line"><label class="s-burningbonus-weekly-label"><wa-radio value="weekly" v-model="schedule_type"
                                                                          @update:modelValue="emitUpdate"/></label>
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
import {around, t} from "../components/i18n";

const props = defineProps({
  value: {type: Object, default: () => ({day: 1, type: 'monthly'})},
});

const emit = defineEmits(['update']);
const ui2 = inject('ui2', false);

const schedule_type = ref(props.value.type);

const week_day = ref(schedule_type.value === 'weekly' ? props.value.day : 1);
const month_day = ref(schedule_type.value === 'monthly' ? props.value.day : 1)

// текст до и после поля с числом: порядок слов задаёт перевод
const monthly = around(t('burningbonus.On day {input} of each month'), 'input');

const dow = [
  {day: 1, name: t('burningbonus.Every Monday')},
  {day: 2, name: t('burningbonus.Every Tuesday')},
  {day: 3, name: t('burningbonus.Every Wednesday')},
  {day: 4, name: t('burningbonus.Every Thursday')},
  {day: 5, name: t('burningbonus.Every Friday')},
  {day: 6, name: t('burningbonus.Every Saturday')},
  {day: 7, name: t('burningbonus.Every Sunday')},
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
