<template>
    <div class="clinics-container">
        <clinics-list
            ref="table"
            :employee="employee"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <el-button
                @click="create">
                {{ __('Добавить') }}
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
        </div>
        <slot name="buttons"></slot>
    </div>
</template>

<script>
import ClinicsList from './clinics/List.vue';
import FormCreate from './clinics/FormCreate.vue';
import FormEdit from './clinics/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ClinicsList,
    },
    props: {
        employee: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    limitClinics: this.$isUpdateLimited('employees'),
                    employee: this.employee,
                },
                editForm: FormEdit,
                editProps: {
                    limitClinics: this.$isUpdateLimited('employees'),
                },
                createHeader: __('Добавить сотрудника в клинику'),
                editHeader: __('Изменить сотрудника в клинике'),
                width: '900px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку клиник сотрудника'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить сотрудника из этой клиники?'),
                deleted: __('Сотрудник был успешно удален из клиники'),
                created: __('Сотрудник был успешно добавлен в клинику'),
                updated: __('Данные сотрудника в клинике были успешно обновлены'),
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
    },
}
</script>
