<template>
    <div class="create-clinics sections-wrapper">
        <section>
            <clinic-form 
                :model="model"
                :workspace="workspace">
                <div 
                    slot="buttons"
                    class="mt-20">
                    <el-button @click="add">
                        {{ __('Добавить клинику') }}
                    </el-button>
                </div>
            </clinic-form>
        </section>
        <template v-if="hasClinics">
            <hr />
            <section>
                <workspace-clinics-list 
                    ref="table"
                    :workspace="repo"
                    @selection-changed="setActiveItem"
                    @loaded="refreshed" />
                <div class="mt-20">
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
            </section>
        </template>
        <div class="dialog-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                v-if="hasClinics"
                type="primary"
                @click="complete">
                {{ __('Завершить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import Workspace from '@/models/workspace';
import WorkspaceClinic from '@/models/workspace/clinic';
import ClinicForm from './clinics/Form.vue';
import FormEdit from './clinics/FormEdit.vue';
import WorkspaceClinicsList from './clinics/List.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ClinicForm,
        WorkspaceClinicsList,
    },
    props: {
        workspace: Object,
    },
    data() {
        return {
            hasClinics: false,
            model: new WorkspaceClinic({
                workspace_id: this.workspace.id,
            }),
            repo: new Workspace({
                id: this.workspace.id,
            }),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        add() {
            this.saveClinic(() => {
                this.model = new WorkspaceClinic({
                    workspace_id: this.workspace.id,
                });
                this.$info(__('Кабинет успешно добавлен в клинику'));
                this.hasClinics = true;
                this.refresh();
            });
        },
        saveClinic(then) {
            this.$clearErrors();
            this.model.save().then((response) => {
                then();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        complete() {
            this.$emit('completed');
        },
        getModalOptions() {
            return {
                editForm: FormEdit,
                editProps: () => ({workspace: this.workspace}),
                editHeader: __('Изменить кабинет в клинике'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                updated: __('Данные кабинета в клинике были успешно обновлены'),
            };
        },
    },
}
</script>