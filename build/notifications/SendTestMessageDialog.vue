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
                                        :type="transport === 'email' ? 'email' : 'phone'" class="long"></div>
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
                <td style="text-align: center">{{ numFormat('0.####', r.balance) }}</td>
                <td style="text-align: center">{{ numFormat('0.####', r.to_burn) }}</td>
                <td style="text-align: center">{{ numFormat('0.####', r.balance-r.to_burn) }}</td>
              </tr>
            </table>
            <preloader v-if="loading_data"/>
          </div>
        </div>
      </div>
      <div class="dialog-buttons">
        <div class="dialog-buttons-gradient">
          <button class="button blue" type="button" :disabled="!selected_contact">Проверить отправку</button>
          или
          <a href="#" @click.prevent="emit('close')">отмена</a>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {inject, reactive, ref} from "vue";
import numFormat from 'number-formatter';
import Preloader from "./Preloader";

const emit = defineEmits(['close']);
const props = defineProps({transport: {type: String, default: 'email'}});

const test_data = reactive([]);
const references = inject('references');

const loading_data = ref(true);
const selected_contact = ref(0);
const recipient_address = ref(props.transport === 'email' ? references.default_email_address : "");

function loadContactsForTest() {
  loading_data.value = true;
  $.shop.getJSON(
      '?plugin=burningbonus&module=notifications&action=gettestdata',
      r => {
        if (r && r.status && r.status === 'ok') test_data.splice(0, Infinity, ...r.data)
      }
  ).always(() => loading_data.value = false);
}

loadContactsForTest();

</script>
