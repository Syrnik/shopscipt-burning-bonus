<template>
  <header class="s-burningbonus-notifications-page-header">
    <h1 class="s-burningbonus-notifications-title">Уведомления о сгорании бонусов</h1>
  </header>

  <div class="s-burningbonus-notifications-body">
    <aside class="s-burningbonus-notifications-sidebar">
      <message-list :notifications="notifications" :selected-id="selected" @select="selected=$event"/>
    </aside>
    <main class="s-burningbonus-notifications-main">
      <message-editor :notification-id="selected" :disable-preloader="deleting"
                      @update:notification="updateNotification($event)" @delete="deleteNotification($event)"/>
    </main>
    <preloader v-if="deleting"/>
  </div>
</template>

<script>
export default {
  name: "Burningbonus Notifications Setup",
}
</script>

<script setup>
import MessageEditor from "./MessageEditor";
import MessageList from "./MessageList";
import {reactive, ref} from "vue";
import Preloader from "./Preloader";

const props = defineProps({notificationsList: {type: Array, default: () => []}});

/** @type Array */
const notifications = reactive(props.notificationsList);
const deleting = ref(false);
const selected = ref(null);
if (notifications.length) selected.value = notifications[0].id;

function updateNotification(item) {
  let index = notifications.findIndex(n => n.id === item.id);
  if (index >= 0) notifications[index] = item;
  else {
    notifications.push(item);
    selected.value = item.id;
  }
}

function deleteNotification(id) {
  if (id && window.confirm("Удалить уведомление?")) {
    deleting.value = true;
    $.shop.jsonPost(
        '?plugin=burningbonus&module=notification&action=delete',
        {id: id},
        r => {
          if (r && r.status && r.status === 'ok') {
            const index = notifications.findIndex(n => n.id === id);
            notifications.splice(index, 1);
            if (!notifications.length) selected.value = null;
            else if (index) {
              if (index + 1 > notifications.length) selected.value = notifications[notifications.length - 1].id;
              else selected.value = notifications[index].id;
            } else selected.value = 0;
          }
        }
    ).always(() => deleting.value = false);
  }
}
</script>
