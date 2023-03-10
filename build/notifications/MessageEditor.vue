<template>
  <form action="?plugin=burningbonus&module=notification&action=save" ref="form" @submit.prevent="saveNotification">
    <header>
      <div class="s-burningbonus-notifications-message-editor-title">
        <h2>{{ notificationName }}</h2>
      </div>
      <div v-if="notificationId" class="s-burningbonus-notifications-message-editor-delete-wrapper">
        <a href="#" @click.prevent="emit('delete', notification.id)"><i class="icon16 no"></i>Удалить</a>
      </div>
    </header>
    <Form.Field name="Название уведомления">
      <div class="value"><input type="text" class="long" v-model.trim="notification.name"></div>
    </Form.Field>
    <status-switch v-model="notification.status"/>
    <transport-selector :allow-change="!notification.id" v-model="notification.transport"/>
    <sender-input v-model="notification.from" :transport="notification.transport ?? 'email'"/>
    <div class="field-group">
      <scheduled-days :value="{day:notification.schedule_day, type:notification.schedule_type}" @update="setSchedule($event)" />
      <Form.SimpleField name="Время отправки">
        после
        <time-picker v-model="notification.scheduled_time"/>
      </Form.SimpleField>
    </div>
    <Form.SimpleField name="Тема сообщения" v-if="notification.transport === 'email'">
      <input type="text" class="long" v-model="notification.subject" required>
    </Form.SimpleField>
    <content-editor v-model="notification.body" :transport="notification.transport"/>
    <Form.SimpleField>
      <button class="green submit button" type="submit" :disabled="submitting">Сохранить</button>
      <button v-if="notificationId" class="button" type="button" :disabled="submitting"
              @click.prevent="dialog_open=true">Проверить отправку
      </button>
      <i class="icon16 loading" v-if="submitting"></i>
    </Form.SimpleField>
  </form>
  <preloader v-if="!disablePreloader && (loading || submitting)"/>
  <Teleport to="body">
    <send-test-message-dialog :notification="notification" v-if="dialog_open" @close="dialog_open=false"/>
  </Teleport>
</template>

<script>
export default {
  name: "MessageEditor",
}
</script>

<script setup>
import * as Form from '../components/wa-form';
import {computed, inject, reactive, ref, watch} from "vue";
import TransportSelector from "./TransportSelector";
import StatusSwitch from "./StatusSwitch";
import SenderInput from "./SenderInput";
import ContentEditor from "./ContentEditor";
import Preloader from "./Preloader";
import ScheduledDays from "./ScheduledDays";
import TimePicker from "./TimePicker";
import SendTestMessageDialog from "./SendTestMessageDialog";

const props = defineProps({
  notificationId: {type: Number},
  disablePreloader: {type: Boolean, default: false}
})

const references = inject('references');
// const wa_url = inject('wa_url');
//const wa_url = inject('wa_url');

const submitting = ref(false);
const loading = ref(false);
const dialog_open = ref(false);

const emit = defineEmits(['update:notification', 'delete']);

const form = ref(null);

const notification = reactive(Object.assign({}, references.notification_template));

const notificationName = computed(() => {
  if (!!notification.name) return notification.name;
  if ((typeof notification.name === 'string') && props.notificationId) return "<без названия>";
  return "Новое уведомление"
});

const message_bodies = reactive({
  email: references.body_templates.email,
  sms: references.body_templates.sms
});



if (props.notificationId) loadNotification(props.notificationId);

function setSchedule(e) {
  notification.schedule_day = e.day;
  notification.schedule_type = e.type;
}



watch(() => props.notificationId, (newId, oldId) => {
  if ((newId === oldId) && (newId !== notification.id)) return;
  loadNotification(newId);
});

function saveNotification() {
  $.shop.jsonPost(
      form.value.getAttribute('action'),
      {data: JSON.stringify(notification)},
      r => {
        if (r && r.status && r.status === 'ok') {
          if (!notification.id) notification.id = r.data.id;
          if (typeof notification.name !== 'string') notification.name = '';
          emit('update:notification', {
            id: notification.id,
            transport: notification.transport,
            name: notification.name
          });
        }
      }
  );
}

function loadNotification(id) {
  if (id) {
    loading.value = true;
    $.shop.getJSON(
        `?plugin=burningbonus&module=notification&action=get&id=${id}`,
        r => {
          if (r.status && r.status === 'ok' && r.data) Object.assign(notification, r.data);
        }
    ).always(() => loading.value = false);
  } else {
    Object.assign(notification, references.notification_template, {body:references.body_templates.email});
  }
}

</script>
