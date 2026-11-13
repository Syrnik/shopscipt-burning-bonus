<template>
  <div class="s-burningbonus-notifications-preloader">
    <div v-if="ui2" class="spinner custom-p-16"></div>
    <img v-else :src="references.wa_app_static_url + 'plugins/burningbonus/img/loading128.gif'" width="128"
         height="128"/>
  </div>
</template>

<script setup>
import {inject} from "vue";

const references = inject('references');
const ui2 = inject('ui2', false);
</script>
