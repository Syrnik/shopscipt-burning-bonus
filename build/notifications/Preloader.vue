<template>
  <div class="s-burningbonus-notifications-preloader"><img
      :src="references.wa_app_static_url + 'plugins/burningbonus/img/loading128.gif'" width="128" height="128"/></div>
</template>

<script setup>
import {inject} from "vue";

const references = inject('references');
</script>
