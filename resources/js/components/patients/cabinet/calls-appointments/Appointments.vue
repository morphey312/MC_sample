<template>
    <appointments-list
        ref="table"
        :filters="filters"
        @header-filter-updated="filtersUpdated"
        @selection-changed="setActive"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$can('appointments.create')"
                @click="create">
                {{ __('Добавить запись') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="edit">
                {{ __($can('appointments.update-clinic') || $canUpdate('appointments') ? 'Редактировать' : 'Просмотреть') }}
            </el-button>
            <el-button
                v-if="$can('action-logs.access')"
                @click="showLog"
                :disabled="activeItem === null">
                {{ __('Операции') }}
            </el-button>
        </div>
    </appointments-list>
</template>
<script>
import AppointmentsList from './AppointmentsList.vue';
import AppointmentManager from '@/components/appointments/mixin/manager';
import AppointmentLog from '@/components/action-log/Appointment.vue';
import lts from '@/services/lts';
import CONSTANTS from '@/constants';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
        AppointmentManager,
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
        };
    },
    methods: {
        setActive(item){
            this.setActiveItem(item);
            if (item && item.length != 0) {
                this.lastActiveItemId = item[0].id;
            }
        },
        filtersUpdated(updates) {
            this.$emit('header-filter-updated', updates);
        },
        create() {
            delete lts.appointmentStore;
            lts.appointmentStore = this.getPatientParam();
            let routeData = this.$router.resolve({name: 'appointment-schedule'});
            window.open(routeData.href, '_blank');
        },
        getPatientParam() {
            return {
                patient: {
                    id: this.filters.patient,
                    status: CONSTANTS.USER.TYPE.PATIENT
                },
            };
        },
        edit() {
            this.makeDaySheetData(this.activeItem, true).then(() => {
                this.editAppointment((appointment) => {
                    this.activeItem = appointment;
                    this.daySheetData = {};
                    this.refresh();
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
    },
    watch: {
        tableFilters(val) {
            this.filters = val;
        },
    },
}
</script>
