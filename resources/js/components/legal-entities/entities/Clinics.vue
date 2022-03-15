<template>
    <div class="clinics-container">
        <legal-entity-clinics-list 
            ref="table"
            :legal-entity="legalEntity"
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
import LegalEntityClinicsList from './clinics/List.vue';
import FormCreate from './clinics/FormCreate.vue';
import FormEdit from './clinics/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        LegalEntityClinicsList,
    },
    props: {
        legalEntity: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    legalEntity: this.legalEntity,
                },
                editForm: FormEdit,
                editProps: () => ({
                    legalEntity: this.legalEntity,
                }),
                modal: this.modalComponent,
                createHeader: __('Добавить корп. клиента в клинику'),
                editHeader: __('Изменить корп. клиента в клинике'),
                backText: __('Вернуться к списку клиник корп. клиента'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                created: __('Корп. клиент успешно добавлен в клинику'),
                updated: __('Данные корп. клиента в клинике были успешно обновлены'),
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
            this.$emit('legal-entity-updated');
        },
    },
}
</script>