<template>
  <div class="dialog" style="display: block">
    <div class="dialog-background"></div>
    <div class="dialog-window">
      <div class="dialog-content">
        <div class="dialog-content-indent">
          <h1>Пробное сообщение</h1>
          <div class="fields form">
            <div class="field">
              <div class="name">Кому</div>
              <div class="value"><input v-model.trim="recipient_address"
                                        :type="notification.transport === 'email' ? 'email' : 'phone'" class="long">
              </div>
            </div>
          </div>
          <p class="clear-left message" style="margin: 1.5em 0 0">Выберите контакт для получения тестовых данных</p>
          <div style="position: relative">
            <table v-if="test_data.length" class="zebra">
              <thead>
              <tr>
                <th></th>
                <th>Имя</th>
                <th style="text-align: center">Всего бонусов сейчас</th>
                <th style="text-align: center">Сгорит</th>
                <th style="text-align: center">Останется</th>
              </tr>
              </thead>
              <tr v-for="r in test_data">
                <td><input type="radio" :value="r.contact_id" v-model="selected_contact"></td>
                <td>{{ !!r.customer_name ? r.customer_name : `(contact id=${r.contact_id})` }}</td>
                <td style="text-align: center">{{ numFormat('0.####', r.actual_balance) }}</td>
                <td style="text-align: center">{{ numFormat('0.####', r.to_burn) }}</td>
                <td style="text-align: center">{{ numFormat('0.####', r.actual_balance-r.to_burn) }}</td>
              </tr>
            </table>
            <preloader v-if="loading_data"/>
          </div>
        </div>
        <preloader v-if="sending_message"/>
      </div>
      <div class="dialog-buttons">
        <div class="dialog-buttons-gradient">
          <button v-if="!sent" class="button blue" type="button" :disabled="!form_valid" @click.prevent="sendMessage">
            Проверить отправку
          </button>
          <a v-else href="#" @click.prevent="clearSent">попробовать ещё раз</a>
          или
          <a href="#" @click.prevent="emitClose">отмена</a>
          <span class="red error" v-if="errors.length"
                style="display: inline-block;margin-left: 1em"><b>Ошибка:</b> {{ errors[0] }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed, inject, reactive, ref} from "vue";
import numFormat from 'number-formatter';
import Preloader from "./Preloader";
import ErrorParser from "../components/webasyst-error-parser";

const emit = defineEmits(['close']);
const props = defineProps({notification: {type: Object}});

const test_data = reactive([]);
const references = inject('references');

const loading_data = ref(true);
const sending_message = ref(false);
const selected_contact = ref(0);
const sent = ref(false);
const recipient_address = ref(props.notification.transport === 'email' ? references.default_email_address : "");
const form_valid = computed(() => selected_contact.value && !!recipient_address.value);
const errors = reactive([]);

function loadContactsForTest() {
  loading_data.value = true;
  $.shop.getJSON(
      '?plugin=burningbonus&module=notifications&action=gettestdata',
      r => {
        if (r && r.status && r.status === 'ok') test_data.splice(0, Infinity, ...r.data)
      }
  ).always(() => loading_data.value = false);
}

function sendMessage() {
  sending_message.value = true;
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
