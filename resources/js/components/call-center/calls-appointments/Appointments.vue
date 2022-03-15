<template>
    <appointments-list
        ref="table"
        v-loading="loading"
        :filters="filters"
        @header-filter-updated="filtersUpdated"
        @selection-changed="setActiveItem"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$canCreate('appointments')"
                @click="create">
                {{ __('Добавить запись') }}
            </el-button>
            <el-button
                v-if="$canUpdate('appointments')"
                :disabled="activeItem === null || !$canManage('appointments.update', [activeItem.clinic_id])"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$can('action-logs.access')"
                @click="showLog"
                :disabled="activeItem === null">
                {{ __('Операции') }}
            </el-button>
            <form-button
                :text="__('Экспорт в excel')"
                icon="download"
                @click="exportExcel(__('Записи на прием'))" />
            <el-button
                v-if="$can('patient-cabinet.access')"
                :disabled="activeItem === null || activeItem.patient === null"
                @click="goPatientCabinet">
                {{ __('Кабинет пациента') }}
            </el-button>
        </div>
    </appointments-list>
</template>


<script>
import AppointmentsList from './appointments/List.vue';
import AppointmentManager from '@/components/appointments/mixin/manager';
import ManageMixin from '@/mixins/manage';
import AppointmentLog from '@/components/action-log/Appointment.vue';
import ExportXLSXMixin from "@/mixins/export-xlsx-list";
import AppointmentRepository from "@/repositories/appointment";
import * as appointmentGenerator from '@/components/appointments/generators/appointments';

export default {
    mixins: [
        ManageMixin,
        AppointmentManager,
        ExportXLSXMixin,
    ],
    components: {
        AppointmentsList,
    },
    props: {
        tableFilters: Object,
    },
    data() {
        return {
            filters: this.tableFilters,
            loading: false,
            reportRepository: new AppointmentRepository(),
            fileGenerator: appointmentGenerator,
        };
    },
    methods: {
        filtersUpdated(updates) {
            this.$emit('header-filter-updated', updates);
        },
        create() {
            this.$router.push({name: 'appointment-schedule'});
        },
        edit() {
            this.makeDaySheetData(this.activeItem, true).then(() => {
                this.editAppointment((appointment) => {
                    this.activeItem = appointment;
                    this.daySheetData = {};
                }, this.activeItem);
            });
        },
        showLog() {
            this.$modalComponent(AppointmentLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения записи'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        goPatientCabinet() {
            let routeData = this.$router.resolve({name: 'patient-cabinet', params: {patientId: this.activeItem.patient_id}});
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
