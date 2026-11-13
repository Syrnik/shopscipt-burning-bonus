<template>
  <wa-field html-name="Текст сообщения<br><span class='hint'>HTML+Smarty</span>">
    <div class="value ace-fix">
      <div class="ace bordered">
        <WaAceEditor :lang="['css','smarty']" :content="modelValue ?? ''"
                     @update:content="emit('update:modelValue', $event)"
                     :theme="theme" :options="{maxLines: Infinity}" :base-path="wa_url" style="min-height: 150px"/>
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
import WaField from "../components/wa-form/wa-field.vue";
import WaAceEditor from "../components/wa-ace-editor.js";
import {inject, onBeforeUnmount, ref} from "vue";

defineProps({modelValue: String});
const emit = defineEmits(['update:modelValue']);

/** @type String */
const wa_url = inject('wa_url');
const ui2 = inject('ui2', false);

// В UI 2.0 есть тёмная тема, редактор должен ей соответствовать (как в waEditorAceInit)
const currentTheme = () => ui2 && document.documentElement.dataset.theme === 'dark' ? 'monokai' : 'eclipse';
const theme = ref(currentTheme());
const onThemeChange = () => theme.value = currentTheme();
document.documentElement.addEventListener('wa-theme-change', onThemeChange);
onBeforeUnmount(() => document.documentElement.removeEventListener('wa-theme-change', onThemeChange));
</script>
