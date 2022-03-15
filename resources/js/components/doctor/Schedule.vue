<template>
    <page
        :title="__('Листы записи пациентов')"
        type="flex">
        <content-placeholder v-if="loading" />
        <template v-else>
            <template v-if="showSchedule">
                <template slot="header-addon">
                    <header-buttons
                        ref="buttons"
                        @schedule-add-selected="addDaySheets">
                        <toggle-link v-model="displayFilter" slot="filter">
                            <svg-icon name="filter-alt" class="icon-small icon-blue">
                                {{ __('Фильтр') }}
                            </svg-icon>
                        </toggle-link>
                    </header-buttons>
                </template>
                <drawer :open="displayFilter">
                    <section class="grey filter">
                        <schedule-filter
                            :filters="filterList"
                            @apply-filters="applyFilters"
                         />
                    </section>
                </drawer>
                <section class="grey-cap-schedule shrinkable">
                    <div
                        class="day-sheet-appointment-content"
                        :class="{'filter-collapsed': !displayFilter}">
                        <time-block
                            :time-range="timeRange"
                            @change-sheets-date="updateParamsDate"
                        />
                        <schedule-grid
                            ref="grid"
                            :params="params"
                            :filters="filters"
                            :time-range="timeRange"
                            :readonly="true"
                            @params-updated="updateParams"
                            @grid-changed="createTimeList"
                            @remove-param="removeParam"
                            @remove-all-params="removeAllParams"
                        />
                    </div>
                </section>
            </template>
            <start-screen v-else
                :doctor="$store.state.user.employee_id"
                @schedule-selected="addDaySheets" />
        </template>
    </page>
</template>

<script>
import AppointmentStatus from '@/repositories/appointment/status';
import ScheduleGrid from '../appointments/schedule/Grid.vue';
import ScheduleFilter from '../appointments/schedule/parts/Filter.vue';
import TimeBlock from '../appointments/schedule/parts/TimeBlock.vue';
import StartScreen from '../appointments/schedule/parts/StartScreen.vue';
import HeaderButtons from './schedule/HeaderButtons.vue';
import DaySheetRepository from '@/repositories/day-sheet';
import GridMixin from '../appointments/mixin/grid';
import StatusMixin from '@/components/appointments/mixin/status';
import lts from '@/services/lts';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        GridMixin,
        StatusMixin,
    ],
    components: {
        ScheduleGrid,
        ScheduleFilter,
        TimeBlock,
        StartScreen,
        HeaderButtons,
    },
    data() {
        return {
            filterList: {},
            filters: {},
            displayFilter: false,
            loading: true,
            statuses: [],
            restrictedStatuses: [
                CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP,
                CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_RECEPTION,
            ],
        };
    },
    created() {
        this.listenAppointmentSelected = (appointment) => {
            if (this.isStatusAllowNavigate(appointment)) {
                this.$router.push({name: 'doctor-appointment', params: {appointmentId: appointment.id}});
            }
        }
    },
    mounted() {
        this.getAppointmentStatuses().then(() => {
            this.getDoctorSheets();
        });

        this.$eventHub.$on('appointment-selected', this.listenAppointmentSelected);
    },
    beforeDestroy() {
        this.$eventHub.$off('appointment-selected', this.listenAppointmentSelected);
    },
    methods: {
        getDoctorSheets() {
            let repository = new DaySheetRepository();
            let filters = {
                date: this.$moment().format('YYYY-MM-DD'),
                or: {
                    employees: this.$store.state.user.employee_id,
                    day_sheet_employee: this.$store.state.user.employee_id,
                },
            };
            let scopes = ['default', 'locks', 'owner', 'appointments', 'limitations'];
            repository.fetch(filters, [], scopes).then((response) => {
                response.rows.forEach((item) => {
                    this.params.push({
                        workspace_id: item.workspace_id,
                        date: item.date,
                        day_sheet_owner_id: item.doctor.id,
                        day_sheet_owner_type: item.day_sheet_owner_type,
                        clinic_id: item.clinic_id,
                    });
                });
                this.loading = false;
                if (this.showSchedule) {
                    this.getData(lts.scheduleColumnsCollapsed === true);
                }
            });
        },
        applyFilters(filters) {
            this.filters = filters;
        },
        getAppointmentStatuses() {
            let status = new AppointmentStatus();
            return status.fetchList().then((response) => {
                this.statuses = response;
                return Promise.resolve();
            });
        },
        isStatusAllowNavigate(appointment) {
            return this.getStatusesBySystemStatus(this.statuses, this.restrictedStatuses)
                        .indexOf(appointment.appointment_status_id) === -1;
        },
    },
    watch: {
        showSchedule(val) {
            if (val && this.$refs.grid !== undefined) {
                this.filterList = this.$refs.grid.filterList;
            }
        }
    }
}
</script>
