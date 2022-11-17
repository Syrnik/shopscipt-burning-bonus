<template>
  <wa-field html-name="Текст сообщения<br><span class='hint'>HTML+Smarty</span>">
    <div class="value ace-fix">
      <div class="ace bordered">
        <WaAceEditor :lang="['css','smarty']" :content="messages[transport]"
                     @update:content="setContent($event)"
                     @update:editor="editor=$event" :base-path="wa_url" style="min-height: 50px"/>
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
import {inject, watch} from "vue";

const props = defineProps({modelValue: String, transport: {type: String, default: 'email'}});
const emit = defineEmits(['update:modelValue']);

/** @type String */
const wa_url = inject('wa_url');

const messages = {
  email: props.transport === 'email' && typeof props.modelValue !== 'string' ? props.modelValue : '',
  sms: props.transport === 'sms' && typeof props.modelValue !== 'string' ? props.modelValue : ''
}

let editor = null;

function setContent(content) {
  if (content !== false) messages[props.transport] = typeof content === 'string' ? content : '';
  emit('update:modelValue', messages[props.transport]);
}

watch(() => props.transport, () => setContent(false));

</script>
