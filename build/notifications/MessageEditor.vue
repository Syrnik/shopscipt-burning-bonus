<template>
    <form action="?plugin=burningbonus&module=notification&action=save" ref="form" @submit.prevent="saveNotification">
        <header :class="{'flexbox middle space-12 custom-mb-16': ui2}">
            <div class="s-burningbonus-notifications-message-editor-title" :class="{wide: ui2}">
                <h3 v-if="ui2" class="gray custom-m-0">{{ notificationName }}</h3>
                <h2 v-else>{{ notificationName }}</h2>
            </div>
            <div v-if="notificationId" class="s-burningbonus-notifications-message-editor-delete-wrapper">
                <button v-if="ui2" type="button" class="button light-gray small" :disabled="submitting"
                        @click.prevent="emit('delete', notification.id)">
                    <Form.Icon name="delete"/>
                    {{ t('burningbonus.Delete') }}
                </button>
                <a v-else href="#" @click.prevent="emit('delete', notification.id)"><Form.Icon name="delete"/>{{ t('burningbonus.Delete') }}</a>
            </div>
        </header>
        <Form.Wrap ui2="fields">
            <Form.Wrap ui2="fields-group">
                <Form.SimpleField :name="t('burningbonus.Notification name')">
                    <input type="text" class="long" v-model.trim="notification.name">
                </Form.SimpleField>
                <status-switch v-model="notification.status"/>
                <transport-selector :allow-change="!notification.id" v-model="notification.transport"/>
                <sender-input :key="formKey" v-model="notification.from" :transport="notification.transport ?? 'email'"/>
            </Form.Wrap>
            <Form.Wrap ui2="fields-group" legacy="field-group">
                <scheduled-days :value="{day:notification.schedule_day, type:notification.schedule_type}"
                                @update="setSchedule($event)"/>
                <Form.SimpleField :name="t('burningbonus.Sending time')">
                    <time-picker v-model="notification.scheduled_time"/>
                </Form.SimpleField>
            </Form.Wrap>
            <Form.Wrap ui2="fields-group" legacy="field-group">
                <version-selector :versions="notification.versions" v-model="currentLocale"
                                  @add="addVersion($event)" @remove="removeVersion($event)"/>
                <Form.SimpleField :name="t('burningbonus.Message subject')" v-if="notification.transport === 'email'">
                    <input type="text" class="long" :class="{'s-burningbonus-wide-input': ui2}" v-model="currentVersion.subject" required>
                </Form.SimpleField>
                <content-editor v-model="currentVersion.body"/>
            </Form.Wrap>
            <Form.SimpleField>
                <button class="button green" :class="{submit: !ui2}" type="submit" :disabled="submitting">{{ t('burningbonus.Save') }}
                </button>
                <button v-if="notificationId" class="button" :class="{'light-gray': ui2}" type="button"
                        :disabled="submitting" @click.prevent="dialog_open=true">{{ t('burningbonus.Test sending') }}
                </button>
                <Form.Icon name="loading" v-if="submitting" class="s-burningbonus-after-button"/>
                <span v-else-if="saved" class="s-burningbonus-after-button"><Form.Icon name="yes"/> {{ t('burningbonus.Saved') }}</span>
                <span v-else-if="saveError" class="s-burningbonus-after-button"
                      :class="ui2 ? 'state-error' : 'red error'">{{ saveError }}</span>
            </Form.SimpleField>
        </Form.Wrap>
    </form>
    <preloader v-if="!disablePreloader && loading"/>
    <Teleport to="body">
        <send-test-message-dialog :notification="notification" v-if="dialog_open" @close="dialog_open=false"/>
    </Teleport>
</template>

<script setup>
import * as Form from '../components/wa-form';
import {computed, inject, reactive, ref, watch} from "vue";
import TransportSelector from "./TransportSelector.vue";
import StatusSwitch from "./StatusSwitch.vue";
import SenderInput from "./SenderInput.vue";
import ContentEditor from "./ContentEditor.vue";
import Preloader from "./Preloader.vue";
import ScheduledDays from "./ScheduledDays.vue";
import TimePicker from "./TimePicker.vue";
import SendTestMessageDialog from "./SendTestMessageDialog.vue";
import VersionSelector from "./VersionSelector.vue";
import ErrorParser from "../components/webasyst-error-parser";
import {t} from "../components/i18n";

const props = defineProps({
    notificationId: {type: Number},
    disablePreloader: {type: Boolean, default: false}
})

const references = inject('references');
const ui2 = inject('ui2', false);

const submitting = ref(false);
const loading = ref(false);
const saved = ref(false);
const saveError = ref('');
const dialog_open = ref(false);
/** Меняется при загрузке другого уведомления, чтобы сбросить локальное состояние дочерних полей */
const formKey = ref(0);
let savedTimer = null;

const emit = defineEmits(['update:notification', 'delete']);

const form = ref(null);

/** Тексты по умолчанию: под '' — для версии по умолчанию, под кодом локали — для языков, у которых есть шаблоны */
const textTemplates = references.text_templates ?? {};

const emptyNotification = () => Object.assign({}, references.notification_template, {
    versions: [{locale: '', subject: textTemplates['']?.subject ?? '', body: textTemplates['']?.email ?? ''}]
});

const notification = reactive(emptyNotification());

/** Локаль редактируемой версии, '' — версия по умолчанию. Правки всех версий хранятся в форме до сохранения */
const currentLocale = ref('');
const currentVersion = computed(() => notification.versions.find(v => v.locale === currentLocale.value)
    ?? notification.versions.find(v => !v.locale));

/** Новая версия: шаблон языка, если он есть, иначе копия версии по умолчанию */
function addVersion(locale) {
    if (notification.versions.some(v => v.locale === locale)) return;
    const template = textTemplates[locale];
    const base = notification.versions.find(v => !v.locale) ?? {subject: '', body: ''};
    notification.versions.push(template
        ? {locale, subject: template.subject ?? '', body: template[notification.transport ?? 'email'] ?? ''}
        : {locale, subject: base.subject, body: base.body});
    currentLocale.value = locale;
}

function removeVersion(locale) {
    if (!locale) return;
    const index = notification.versions.findIndex(v => v.locale === locale);
    if (index >= 0) notification.versions.splice(index, 1);
    currentLocale.value = '';
}

const notificationName = computed(() => {
    if (!!notification.name) return notification.name;
    if ((typeof notification.name === 'string') && props.notificationId) return t('burningbonus.<untitled>');
    return t('burningbonus.New notification');
});

if (props.notificationId) loadNotification(props.notificationId);

function setSchedule(e) {
    notification.schedule_day = e.day;
    notification.schedule_type = e.type;
}

watch(() => props.notificationId, newId => {
    // после сохранения нового уведомления его id уже есть в форме, перезагружать нечего
    if (newId && newId === notification.id) return;
    loadNotification(newId);
});

// У нового уведомления текст по умолчанию зависит от транспорта. Не трогаем текст, который уже редактировали,
// и версии-копии языков без своих шаблонов
watch(() => notification.transport, (transport, old) => {
    if (notification.id) return;
    for (const version of notification.versions) {
        const template = textTemplates[version.locale];
        if (!template) continue;
        if (!version.body || version.body === template[old]) version.body = template[transport] ?? '';
    }
});

function saveNotification() {
    submitting.value = true;
    saved.value = false;
    saveError.value = '';
    $.shop.jsonPost(
        form.value.getAttribute('action'),
        {data: JSON.stringify(notification)},
        r => {
            if (!notification.id) notification.id = r.data.id;
            if (typeof notification.name !== 'string') notification.name = '';
            emit('update:notification', {
                id: notification.id,
                transport: notification.transport,
                name: notification.name,
                locales: notification.versions.map(v => v.locale).filter(l => !!l)
            });
            saved.value = true;
            clearTimeout(savedTimer);
            savedTimer = setTimeout(() => saved.value = false, 3000);
        },
        r => {
            saveError.value = ErrorParser.parse(r).join('; ');
            return false;
        }
    ).always(() => submitting.value = false);
}

function loadNotification(id) {
    saved.value = false;
    saveError.value = '';
    currentLocale.value = '';
    if (id) {
        loading.value = true;
        $.getJSON(`?plugin=burningbonus&module=notification&action=get&id=${id}`)
            .done(r => {
                if (r && r.status === 'ok' && r.data) {
                    Object.assign(notification, r.data);
                    formKey.value++;
                }
            })
            .always(() => loading.value = false);
    } else {
        Object.assign(notification, emptyNotification());
        formKey.value++;
    }
}

</script>
