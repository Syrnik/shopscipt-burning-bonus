<template>
  <ul :class="ui2 ? 'menu large' : 'menu-v stack with-icons'">
    <li :class="{selected: !selectedId, 'bottom-padded': ui2 && notifications.length}">
      <a href="#" :class="{small: !ui2}" @click.prevent="emit('select', null)">
        <wa-icon name="add"/><span>{{ t('burningbonus.New notification') }}</span></a>
    </li>
    <li v-for="n in notifications" :key="n.id" :class="{selected: n.id === selectedId}">
      <a href="#" @click.prevent="emit('select', n.id)">
        <wa-icon :name="n.transport"/>
        <span>{{ !!n.name ? n.name : t('burningbonus.(untitled)') }}</span></a>
    </li>
  </ul>
</template>

<script>
export default {
  name: "MessageList"
}
</script>

<script setup>
import {inject} from "vue";
import WaIcon from "../components/wa-form/wa-icon.vue";
import {t} from "../components/i18n";

defineProps({
  notifications: {type: Array, default: () => []},
  selectedId: {type: Number}
});

const emit = defineEmits(['select']);
const ui2 = inject('ui2', false);
</script>
