<template>
    <div class="clinics-container">
        <notification-template-settings-clinic-list
            ref="table"
            :notification-template="notificationTemplate"
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
import NotificationTemplateSettingsClinicList from './clinics/List.vue';
import FormCreate from './clinics/FormCreate.vue';
import FormEdit from './clinics/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        NotificationTemplateSettingsClinicList,
    },
    props: {
        notificationTemplate: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    notificationTemplate: this.notificationTemplate,
                },
                editForm: FormEdit,
                editProps: () => ({
                    notificationTemplate: this.notificationTemplate,
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
