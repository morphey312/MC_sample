<template>
    <div class="clinics-container">
        <workspace-clinics-list
            ref="table"
            :workspace="workspace"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <template>
                <el-button
                    @click="create">
                    {{ __('Добавить клинику') }}
                </el-button>
                <el-button
                    :disabled="activeItem === null || !$canManage('workspaces.update', [activeItem.clinic_id])"
                    @click="edit">
                    {{ __('Редактировать') }}
                </el-button>
                 <el-button
                    :disabled="activeItem === null || !$canManage('workspaces.delete', [activeItem.clinic_id])"
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
import WorkspaceClinicsList from './clinics/List.vue';
import FormCreate from './clinics/FormCreate.vue';
import FormEdit from './clinics/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        WorkspaceClinicsList,
    },
    props: {
        workspace: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    workspace: this.workspace,
                },
                editForm: FormEdit,
                editProps: () => ({
                    workspace: this.workspace,
                }),
                modal: this.modalComponent,
                createHeader: __('Добавить кабинет в клинику'),
                editHeader: __('Изменить кабинет в клинике'),
                backText: __('Вернуться к списку клиник кабинета'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                created: __('Кабинет был успешно добавлен в клинику'),
                updated: __('Данные кабинета в клинике были успешно обновлены'),
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
            this.$emit('workspace-updated');
        },
    },
}
</script>
