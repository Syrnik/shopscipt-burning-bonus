<template>
  <div class="dialog" :class="{'dialog-opened s-burningbonus-dialog': ui2}" :style="ui2 ? null : {display: 'block'}">
    <div class="dialog-background" @click="emitClose"></div>
    <div :class="ui2 ? 'dialog-body' : 'dialog-window'">
      <header v-if="ui2" class="dialog-header"><h1>Пробное сообщение</h1></header>
      <div class="dialog-content">
        <div :class="{'dialog-content-indent': !ui2}">
          <h1 v-if="!ui2">Пробное сообщение</h1>
          <div class="fields form">
            <div class="field">
              <div class="name">Кому</div>
              <div class="value"><input v-model.trim="recipient_address"
                                        :type="notification.transport === 'email' ? 'email' : 'tel'" class="long">
              </div>
            </div>
          </div>
          <div style="position: relative; min-height: 4em">
            <template v-if="test_data.length">
              <p class="clear-left message" style="margin: 1.5em 0 0.5em">Выберите контакт для получения тестовых
                данных</p>
              <table :class="ui2 ? 'small' : 'zebra'">
                <thead>
                <tr>
                  <th></th>
                  <th>Имя</th>
                  <th style="text-align: center">Всего бонусов сейчас</th>
                  <th style="text-align: center">Сгорит</th>
                  <th style="text-align: center">Останется</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="r in test_data" :key="r.contact_id" style="cursor: pointer"
                    @click="selected_contact = r.contact_id">
                  <td><input type="radio" :value="r.contact_id" v-model="selected_contact"></td>
                  <td>{{ !!r.customer_name ? r.customer_name : `(contact id=${r.contact_id})` }}</td>
                  <td style="text-align: center">{{ numFormat('0.####', r.actual_balance) }}</td>
                  <td style="text-align: center">{{ numFormat('0.####', r.to_burn) }}</td>
                  <td style="text-align: center">{{ numFormat('0.####', r.actual_balance - r.to_burn) }}</td>
                </tr>
                </tbody>
              </table>
            </template>
            <p v-else-if="!loading_data" class="clear-left" style="margin: 1.5em 0 0">
              {{ load_error || 'Для пробной отправки нужен хотя бы один покупатель, у которого в ближайшее списание сгорят бонусы. Сейчас таких нет.' }}
            </p>
            <preloader v-if="loading_data"/>
          </div>
        </div>
        <preloader v-if="sending_message"/>
      </div>
      <div :class="ui2 ? 'dialog-footer' : 'dialog-buttons'">
        <div :class="{'dialog-buttons-gradient': !ui2}">
          <template v-if="ui2">
            <button v-if="!sent" class="button" type="button" :disabled="!form_valid || sending_message"
                    @click.prevent="sendMessage">Проверить отправку
            </button>
            <button v-else class="button" type="button" @click.prevent="clearSent">Попробовать ещё раз</button>
            <button class="button light-gray" type="button" @click.prevent="emitClose">Закрыть</button>
          </template>
          <template v-else>
            <button v-if="!sent" class="button blue" type="button" :disabled="!form_valid || sending_message"
                    @click.prevent="sendMessage">
              Проверить отправку
            </button>
            <a v-else href="#" @click.prevent="clearSent">попробовать ещё раз</a>
            или
            <a href="#" @click.prevent="emitClose">отмена</a>
          </template>
          <span :class="ui2 ? 'state-error' : 'red error'" v-if="errors.length"
                style="display: inline-block;margin-left: 1em"><b>Ошибка:</b> {{ errors[0] }}</span>
          <span :class="ui2 ? 'state-success' : 'green'" v-else-if="sent"
                style="display: inline-block;margin-left: 1em">Уведомление отправлено</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed, inject, reactive, ref} from "vue";
import numFormat from 'number-formatter';
import Preloader from "./Preloader.vue";
import ErrorParser from "../components/webasyst-error-parser";

const emit = defineEmits(['close']);
const props = defineProps({notification: {type: Object}});

const test_data = reactive([]);
const references = inject('references');
const ui2 = inject('ui2', false);

const loading_data = ref(true);
const load_error = ref('');
const sending_message = ref(false);
const selected_contact = ref(0);
const sent = ref(false);
const recipient_address = ref(props.notification.transport === 'email' ? references.default_email_address : "");
const form_valid = computed(() => selected_contact.value && !!recipient_address.value);
const errors = reactive([]);

function loadContactsForTest() {
  loading_data.value = true;
  load_error.value = '';
  $.getJSON('?plugin=burningbonus&module=notifications&action=gettestdata')
      .done(r => {
        if (r && r.status === 'ok') test_data.splice(0, Infinity, ...(r.data ?? []));
        else load_error.value = ErrorParser.parse(r)[0];
      })
      .fail(xhr => load_error.value = ErrorParser.parse(xhr)[0])
      .always(() => loading_data.value = false);
}

function sendMessage() {
  sending_message.value = true;
  errors.splice(0, Infinity);
  $.shop.jsonPost(
      '?plugin=burningbonus&module=notification&action=sendtest',
      {
        contact_id: selected_contact.value,
        recipient: recipient_address.value,
        notification: props.notification
      },
      r => true,
      r => {
        errors.splice(0, Infinity, ...ErrorParser.parse(r));
        return false;
      }
  ).always(() => {
    sending_message.value = false;
    sent.value = true;
  });
}

function clearSent() {
  sent.value = false;
  errors.splice(0, Infinity);
}

function emitClose() {
  emit('close');
}

loadContactsForTest();

</script>
