<template>
    <div class="clinics-container">
        <specialization-clinics-list 
            ref="table"
            :specialization="specialization"
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
import SpecializationClinicsList from './clinics/List.vue';
import FormCreate from './clinics/FormCreate.vue';
import FormEdit from './clinics/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        SpecializationClinicsList,
    },
    props: {
        specialization: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    specialization: this.specialization,
                },
                editForm: FormEdit,
                editProps: () => ({
                    specialization: this.specialization,
                }),
                modal: this.modalComponent,
                createHeader: __('Добавить специализацию в клинику'),
                editHeader: __('Изменить специализацию в клинике'),
                backText: __('Вернуться к списку клиник специализации'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                created: __('Специализация была успешно добавлена в клинику'),
                updated: __('Данные специализации в клинике были успешно обновлены'),
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