<template>
  <i class="icon16" :class="iconClass"></i>
</template>

<script setup>
import {computed} from "vue";

const props = defineProps({value:String});
const iconClass = computed(()=>{
  switch (props.value) {
    case 'sms': return['mobile'];
    case 'email': return ['email'];
  }
  return [];
})
</script>
