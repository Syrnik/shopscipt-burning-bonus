<template>
  <wa-field html-name="Текст сообщения<br><span class='hint'>HTML+Smarty</span>">
    <div class="value ace-fix">
      <div class="ace bordered">
        <WaAceEditor :lang="['css','smarty']" v-model:content="messageBody"
                     :options="{maxLines: Infinity}"
                     @update:editor="editor=$event" :base-path="wa_url" style="min-height: 150px"/>
      </div>
    </div>
  </wa-field>
</template>

<script>
export default {
  name: "ContentEditor"
}
</script>

<script setup>
import WaField from "../components/wa-form/wa-field";
import WaAceEditor from "../components/wa-ace-editor";
import {computed, inject, nextTick, reactive, watch} from "vue";

const props = defineProps({modelValue: String, transport: {type: String, default: 'email'}});
const emit = defineEmits(['update:modelValue']);

/** @type String */
const wa_url = inject('wa_url');
const references = inject('references');

const messages = reactive({
  email: references.body_templates?.email ?? '',
  sms: references.body_templates?.sms ?? ''
});

const messageBody = computed({
  get() {
    return messages[props.transport];
  },
  set(v) {
    messages[props.transport] = v;
    emit('update:modelValue', v);
  }
});

let editor = null;

watch(()=>props.modelValue, v=>messages[props.transport]=v);


</script>
