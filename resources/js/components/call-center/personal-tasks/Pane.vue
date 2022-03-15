<template>
    <div class="tasks-pane">
        <section>
            <sticky-footer>
                <tasks-list 
                    ref="table"
                    @selection-changed="setActiveItem"
                    @handle-task="handleTask" />
                <div slot="footer">
                    <el-button
                        v-if="$canCreate('personal-tasks')"
                        @click="create">
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$canUpdate('personal-tasks')"
                        :disabled="activeItem === null || !$canManage('personal-tasks.update', [activeItem.clinic_id])"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$canDelete('personal-tasks')"
                        :disabled="activeItem === null || !$canManage('personal-tasks.delete', [activeItem.clinic_id])"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </sticky-footer>
        </section>
    </div>
</template>

<script>
import TasksList from './List.vue';
import ManageMixin from '@/mixins/manage';
import FormCreate from './Create.vue';
import FormEdit from './Edit.vue';
import FormHandle from './Handle.vue';
import SelectContactMixin from '../mixins/select-contact';

export default {
    mixins: [
        ManageMixin,
        SelectContactMixin,
    ],
    components: {
        TasksList,
    },
    methods: {
        getFilterUid() {
            return 'call-center-personal-tasks';
        },
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить задание'),
                editHeader: __('Изменить задание'),
                width: '750px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить это задание?'),
                deleted: __('Задание было успешно удалено'),
                created: __('Задание было успешно добавлено'),
                updated: __('Задание было успешно обновлено'),
            };
        },
        handleTask(task) {
            this.$modalComponent(FormHandle, {
                item: task,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, model) => {
                    dialog.close();
                    this.$info(__('Задание успешно обновлено'));
                    this.refresh();
                },
            }, {
                header: __('Задание'),
                width: '550px',
            });
        },
    },
}
</script>