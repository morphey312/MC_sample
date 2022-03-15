<template>
    <calls-list 
        ref="table"
        v-loading="loading"
        :filters="filters"
        :flex-height="flexHeight"
        @header-filter-updated="filtersUpdated"
        @selection-changed="setActiveItem"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
            <form-button 
                v-if="$canCreate('calls')"
                :text="__('Добавить звонок')"
                icon="plus"
                @click="create" />
            <form-button 
                v-if="$canUpdate('calls')"
                :disabled="activeItem === null || !$canManage('calls.update', [activeItem.clinic_id])"
                :text="__('Редактировать')"
                icon="edit"
                @click="edit" />
            <form-button 
                v-if="$canProcessCalls()"
                :disabled="activeItem === null"
                :text="__('Задать контакт для звонка')"
                icon="menu-patients"
                @click="voipSelectContact" />
            <form-button 
                v-if="!itemDeleted && $canDelete('calls')"
                :disabled="activeItem === null || !$canManage('calls.delete', [activeItem.clinic_id])"
                :text="__('Удалить')"
                icon="delete"
                @click="deleteCall" />
            <form-button 
                v-if="itemDeleted && $canUpdate('calls')"
                :disabled="activeItem === null || !$canManage('calls.update', [activeItem.clinic_id])"
                :text="__('Восстановить')"
                icon="arrow-circle"
                @click="restoreCall" />
            <form-button 
                v-if="$can('action-logs.access')"
                :disabled="activeItem === null"
                :text="__('Операции')"
                icon="menu-marketing"
                @click="showLog" />
            <form-button 
                :text="__('Экспорт в excel')"
                icon="download"
                @click="exportExcel(__('Информационные звонки'))" />
            <form-button 
                v-if="$can('patient-cabinet.access')"
                :disabled="activeItem === null || !activeItem.is_patient_contact"
                :text="__('Кабинет пациента')"
                icon="catalogue"
                @click="goPatientCabinet" />
        </div>
    </calls-list>
</template>


<script>
import CallsList from './calls/List.vue';
import FormCreate from './calls/FormCreate.vue';
import FormEdit from './calls/FormEdit.vue';
import FormDelete from './calls/FormDelete.vue';
import CONSTANTS  from '@/constants';
import ManageMixin from '@/mixins/manage';
import SelectContactMixin from '../mixins/select-contact';
import CallLog from '@/components/action-log/Call.vue';
import CallsRepository from '@/repositories/call';
import * as callsGenerator from '@/components/call-center/calls/generators/calls';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';

export default {
    mixins: [
        ManageMixin,
        SelectContactMixin,
        ExportXLSXMixin,
    ],
    components: {
        CallsList,
    },
    props: {
        tableFilters: Object,
        flexHeight: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            filters: this.tableFilters,
            reportRepository: new CallsRepository(),
            loading: false,
            fileGenerator: callsGenerator,
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
        voipSelectContact() {
            if (this.activeItem.is_patient_contact) {
                this.selectPatientContact(this.activeItem.contact);
            } else if (this.activeItem.is_employee_contact) {
                this.selectEmployeeContact(this.activeItem.contact);
            }
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
        goPatientCabinet() {
            let routeData = this.$router.resolve({name: 'patient-cabinet', params: {patientId: this.activeItem.contact_id}});
            window.open(routeData.href, '_blank');
        },
    },
    watch: {
        tableFilters(val) {
            this.filters = val;
        },
    },
}
</script>