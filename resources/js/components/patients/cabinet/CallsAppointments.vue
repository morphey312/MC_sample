<template>
    <page
        :title="__('Звонки и записи: {name}', {name: patient.full_name})"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <a
                    href="#"
                    @click.prevent="showSignalRecord">
                    <svg-icon
                        name="report-alt"
                        class="icon-small icon-blue">
                        {{ __('Сигнальные обозначения') }}
                    </svg-icon>
                </a>
            </div>
        </template>
        <el-tabs v-model="activeTab" class="tab-group-beige shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                :label="__('Записи')"
                name="appointments" >
                <section class="shrinkable pt-0">
                    <appointments-table
                        :table-filters="appointmentFilters"
                        @header-filter-updated="syncAppointmentFilters" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Звонки')"
                name="calls" >
                <section class="shrinkable pt-0">
                    <calls-table
                        :table-filters="callsFilters"
                        @header-filter-updated="syncCallsFilters" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('SMS')"
                name="sms" >
                <section class="shrinkable pt-0">
                    <sms-table
                        :table-filters="SMSFilters"
                        @header-filter-updated="syncSMSFilters" />
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import CabinetMixin from './mixins/cabinet';
import AppointmentsTable from './calls-appointments/Appointments.vue';
import CallsTable from './calls-appointments/Calls.vue';
import SmsTable from './calls-appointments/SMS.vue';

export default {
    mixins: [
        CabinetMixin,
    ],
    components: {
        AppointmentsTable,
        CallsTable,
        SmsTable,
    },
    data() {
        return {
            activeTab: 'appointments',
            appointmentFilters: {
                patient: this.patient.id,
            },
            callsFilters: {
                patient: this.patient.id,
            },
            SMSFilters: {
                patient: this.patient.id,
            },
        };
    },
    methods: {
        syncAppointmentFilters(updates) {
            this.appointmentFilters = _.onlyFilled({
                ...this.filters,
                ...updates,
                patient: this.patient.id,
            });
        },
        syncCallsFilters(updates) {
            this.callsFilters = _.onlyFilled({
                ...this.filters,
                ...updates,
                patient: this.patient.id,
            });
        },
        syncSMSFilters(updates) {
            this.SMSFilters = _.onlyFilled({
                ...this.filters,
                ...updates,
                patient: this.patient.id,
            });
        },
    }
};
</script>
