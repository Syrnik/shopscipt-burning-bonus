<template>
  <!--
    В UI 2.0 FontAwesome работает в режиме SVG+JS и подменяет <i> на <svg>. Если <i> принадлежит Vue,
    после подмены он оказывается вне документа и любое обновление рядом падает с ошибкой insertBefore.
    Поэтому Vue владеет только обёрткой, а сам <i> вставляется через v-html.
  -->
  <span v-if="ui2" class="icon" :key="name" v-html="`<i class='${icon.ui2}'></i>`"></span>
  <i v-else :class="icon.legacy"></i>
</template>

<script setup>
import {computed, inject} from "vue";

const ICONS = {
  email: {legacy: 'icon16 email', ui2: 'fas fa-envelope'},
  sms: {legacy: 'icon16 mobile', ui2: 'fas fa-mobile-alt'},
  add: {legacy: 'icon10 add', ui2: 'fas fa-plus-circle text-green'},
  delete: {legacy: 'icon16 no', ui2: 'fas fa-trash-alt text-red'},
  loading: {legacy: 'icon16 loading', ui2: 'fas fa-spinner fa-spin'},
  yes: {legacy: 'icon16 yes', ui2: 'fas fa-check-circle text-green'},
};

const props = defineProps({name: String});
const ui2 = inject('ui2', false);
const icon = computed(() => ICONS[props.name] ?? {legacy: '', ui2: ''});
</script>
