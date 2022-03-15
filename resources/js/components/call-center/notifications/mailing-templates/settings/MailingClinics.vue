<template>
    <div class="clinics-container">
        <notification-mailing-template-settings-clinic-list
            ref="table"
            :notification-mailing-template="notificationMailingTemplate"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <template>
                <el-button
                    @click="create">
                    {{ __('Добавить клинику') }}
                </el-button>
                <el-button
                    :disabled="activeItem === null"
                    @click="edit">
                    {{ __('Редактировать') }}
                </el-button>
                <el-button
                    :disabled="activeItem === null"
                    @click="remove">
                    {{ __('Удалить') }}
                </el-button>
            </template>
        </div>
    </div>
</template>
<script>
import NotificationMailingTemplateSettingsClinicList from './mailing-clinics/List.vue';
import FormCreate from './mailing-clinics/FormCreate.vue';
import FormEdit from './mailing-clinics/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        NotificationMailingTemplateSettingsClinicList,
    },
    props: {
        notificationMailingTemplate: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    notificationMailingTemplate: this.notificationMailingTemplate,
                },
                editForm: FormEdit,
                editProps: () => ({
                    notificationMailingTemplate: this.notificationMailingTemplate,
                }),
                modal: this.modalComponent,
                createHeader: __('Добавить клинику к настройкам'),
                editHeader: __('Изменить клинику настроек'),
                backText: __('Вернуться к списку клиник'),
                width: '600px',
            };
        },
        getMessages() {
            return {
                created: __('Клиника была успешно добавлена к настройкам шаблона'),
                updated: __('Данные клиники в настройках шаблона были успешно обновлены'),
                deleteConfirmation: __('Вы уверены, что хотите удалить эту запись?')
            };
        },
        onCreated(model) {
            this.$emit('clinics-updated');
        },
        onUpdated(model) {
            this.$emit('clinics-updated');
        },
        onDeleted(attributes) {
            this.$emit('clinics-updated');
        },
        cancel() {
            this.$emit('cancel');
        },
        updateModel() {
            this.$emit('specialization-updated');
        },
    },
}
</script>
