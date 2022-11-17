<template>
  <ul class="menu-v stack with-icons">
    <li :class="{selected: selectedId  === null}">
      <a href="#" class="small" @click.prevent="emit('select', null)"><i class="icon10 add"></i>Новое уведомление</a>
    </li>
    <li v-for="n in notifications" :class="{selected: n.id === selectedId}">
      <a href="#" @click.prevent="emit('select', n.id)">
        <transport-icon :value="n.transport"/>
        {{ !!n.name ? n.name : '(без названия)' }}</a>
    </li>
  </ul>
</template>

<script>
export default {
  name: "MessageList"
}
</script>

<script setup>
import TransportIcon from "./TransportIcon";

const props = defineProps({
  notifications: {type: Array, default: () => []},
  selectedId: {type: Number}
});

const emit = defineEmits(['select']);

</script>
