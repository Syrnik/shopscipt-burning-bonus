<template>
  <div v-if="ui2" class="flexbox s-burningbonus-ui2">
    <aside class="sidebar blank width-23rem mobile-friendly">
      <message-list :notifications="notifications" :selected-id="selected" @select="selected=$event"/>
    </aside>
    <main class="content box contentbox">
      <div class="article wider">
        <div class="article-body s-burningbonus-notifications-content">
          <header class="custom-mb-24">
            <h1>{{ t('burningbonus.Bonus expiry notifications') }}</h1>
          </header>
          <message-editor :notification-id="selected" :disable-preloader="deleting"
                          @update:notification="updateNotification($event)" @delete="deleteNotification($event)"/>
          <preloader v-if="deleting"/>
        </div>
      </div>
    </main>
  </div>
  <template v-else>
    <header class="s-burningbonus-notifications-page-header">
      <h1 class="s-burningbonus-notifications-title">{{ t('burningbonus.Bonus expiry notifications') }}</h1>
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
</template>

<script>
export default {
  name: "Burningbonus Notifications Setup",
}
</script>

<script setup>
import MessageEditor from "./MessageEditor.vue";
import MessageList from "./MessageList.vue";
import {inject, reactive, ref} from "vue";
import Preloader from "./Preloader.vue";
import {t} from "../components/i18n";

const props = defineProps({notificationsList: {type: Array, default: () => []}});
const ui2 = inject('ui2', false);

/** @type Array */
const notifications = reactive(props.notificationsList);
const deleting = ref(false);
const selected = ref(notifications.length ? notifications[0].id : null);

function updateNotification(item) {
  let index = notifications.findIndex(n => n.id === item.id);
  if (index >= 0) notifications[index] = item;
  else {
    notifications.push(item);
    selected.value = item.id;
  }
}

function confirmDelete() {
  if (ui2 && $.waDialog && $.waDialog.confirm) {
    return new Promise(resolve => $.waDialog.confirm({
      title: t('burningbonus.Delete notification?'),
      success_button_title: t('burningbonus.Delete'),
      success_button_class: 'danger',
      cancel_button_title: t('burningbonus.Cancel'),
      cancel_button_class: 'light-gray',
      onSuccess: () => resolve(true),
      onCancel: () => resolve(false)
    }));
  }
  return Promise.resolve(window.confirm(t('burningbonus.Delete notification?')));
}

function deleteNotification(id) {
  if (!id) return;
  confirmDelete().then(confirmed => {
    if (!confirmed) return;
    deleting.value = true;
    $.shop.jsonPost(
        '?plugin=burningbonus&module=notification&action=delete',
        {id: id},
        () => {
          const index = notifications.findIndex(n => n.id === id);
          if (index < 0) return;
          notifications.splice(index, 1);
          // выбираем соседнее уведомление, а если их не осталось — форму нового
          selected.value = notifications[Math.min(index, notifications.length - 1)]?.id ?? null;
        }
    ).always(() => deleting.value = false);
  });
}
</script>
