<template>
    <calls-list 
        ref="table"
        :filters="filters"
        @header-filter-updated="filtersUpdated"
        @selection-changed="setActiveItem"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$can('calls.create')"
                @click="create">
                {{ __('Добавить звонок') }}
            </el-button>
            <el-button
                v-if="$canUpdate('calls')"
                :disabled="activeItem === null || !$canManage('calls.update', [activeItem.clinic_id])"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="!itemDeleted && $canDelete('calls')"
                :disabled="activeItem === null || !$canManage('calls.delete', [activeItem.clinic_id])"
                @click="deleteCall">
                {{ __('Удалить') }}
            </el-button>
            <el-button
                v-if="itemDeleted && $canUpdate('calls')"
                :disabled="activeItem === null || !$canManage('calls.update', [activeItem.clinic_id])"
                @click="restoreCall">
                {{ __('Восстановить') }}
            </el-button>
            <el-button
                v-if="$can('action-logs.access')"
                :disabled="activeItem === null"
                @click="showLog">
                {{ __('Операции') }}
            </el-button>
        </div>
    </calls-list>
</template>
<script>
import CallsList from './CallsList.vue';
import CallLog from '@/components/action-log/Call.vue';
import FormCreate from '@/components/call-center/calls-appointments/calls/FormCreate.vue';
import FormEdit from '@/components/call-center/calls-appointments/calls/FormEdit.vue';
import FormDelete from '@/components/call-center/calls-appointments/calls/FormDelete.vue';
import CONSTANTS  from '@/constants';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CallsList,
    },
    props: {
        tableFilters: Object,
    },
    data() {
        return {
            filters: this.tableFilters,
        };
    },
    computed: {
        itemDeleted() {
            return this.activeItem !== null && this.activeItem.call_delete_reason_id;
        },
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить звонок'),
                editHeader: __('Изменить звонок'),
                width: '1200px',
            };
        },
        getMessages() {
            return {
                created: __('Звонок был успешно добавлен'),
                updated: __('Звонок был успешно обновлен'),
            };
        },
        deleteCall() {
            this.$modalComponent(FormDelete, {
                item: this.activeItem,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                deleted: (dialog) => {
                    dialog.close();
                    this.refresh();
                },
            }, {
                header: __('Удаление звонка'),
                width: '500px',
            });  
        },
        restoreCall() {
            this.$confirm(__('Восстановить звонок?'), () => {
                this.activeItem.unset(['call_delete_reason_id', 'delete_reason_comment']);
                this.activeItem.save().then(() => {
                    this.$info(__('Звонок успешно восстановлен'));
                    this.refresh();
                });
            });
        },
        filtersUpdated(updates) {
            this.$emit('header-filter-updated', updates);
        },
        showLog() {
            this.$modalComponent(CallLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения звонка'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
    watch: {
        tableFilters(val) {
            this.filters = val;
        },
    },
}
</script>