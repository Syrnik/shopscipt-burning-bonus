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
              <div class="value"><input type="text" class="long"></div>
            </div>
          </div>
          <p class="clear-left message">Выберите заказ для получения тестовых данных</p>
        </div>
        <table class="zebra"></table>
      </div>
      <div class="dialog-buttons">
        <div class="dialog-buttons-gradient">
          <button class="button blue" type="button">Проверить отправку</button>
          или
          <a href="#" @click.prevent="emit('close')">отмена</a>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const emit = defineEmits(['close']);
</script>
