<template>
    <div class="clinics-container">
        <company-clinics-list
            ref="table"
            :company="company"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <template>
                <el-button
                    @click="create">
                    {{ __('Добавить клинику') }}
                </el-button>
                <el-button
                    :disabled="activeItem === null || !$canManage('insurance.update', [activeItem.clinic_id])"
                    @click="edit">
                    {{ __('Редактировать') }}
                </el-button>
                 <el-button
                    :disabled="activeItem === null || !$canManage('insurance.delete', [activeItem.clinic_id])"
                    @click="remove">
                    {{ __('Удалить') }}
                </el-button>
            </template>
        </div>
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="updateModel">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import CompanyClinicsList from './clinics/List.vue';
import FormCreate from './clinics/FormCreate.vue';
import FormEdit from './clinics/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CompanyClinicsList,
    },
    props: {
        company: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    company: this.company,
                },
                editForm: FormEdit,
                editProps: () => ({
                    company: this.company,
                }),
                modal: this.modalComponent,
                createHeader: __('Добавить компанию в клинику'),
                editHeader: __('Изменить компанию в клинике'),
                backText: __('Вернуться к списку клиник компании'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                created: __('Компания была успешно добавлена в клинику'),
                updated: __('Данные компании в клинике были успешно обновлены'),
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
            this.$emit('company-updated');
        },
    },
}
</script>
