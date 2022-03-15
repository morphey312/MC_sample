<template>
    <div class="appointment-wrapper">
        <div class="schedule-header-wrapper" ref="headers">
            <div
                v-for="(day, dayIndex) in dayList"
                :key="dayIndex"
                class="schedule-item-header"
                :class="{'collapsed': isCollapsed(day.id)}">
                <appointment-header
                    :day="day"
                    :collapsed="isCollapsed(day.id)"
                    @toggle-collapse="toggleCollapse(day.id)"
                    @thumb-left="thumbLeft(dayIndex)"
                    @thumb-right="thumbRight(dayIndex)"
                    @switch-day="switchDay(dayIndex, ...arguments)" />
            </div>
        </div>
         <template>
            <div class="schedule-appointments-wrapper" @scroll="scrollTimeAndHeader">
                <div
                    v-for="(day, dayIndex) in dayList"
                    :key="dayIndex"
                    class="schedule-appointments"
                    :class="{'collapsed': isCollapsed(day.id)}">
                    <div v-if="!day.time_sheets.length" class="vac-day" >
                        <img src="/svg/content/vac-day.svg">
                        <span>
                            {{ __("У врача выходной день") }}
                        </span>
                    </div>
                    <appointment-column
                        :time-range="timeRange"
                        :patient="patient"
                        :collapsed="isCollapsed(day.id)"
                        :day="day"
                        :cashier="cashier"
                        :cashboxes="cashboxes"
                        :checkbox-cashboxes="checkboxCashboxes"
                        :readonly="readonly"
                        :inactive-statuses="inactiveStatuses"
                        :appointment-statuses="appointmentStatuses" />
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import AppointmentColumn from './Column.vue';
import AppointmentHeader from './Header.vue';
import TimeBlock from './parts/TimeBlock.vue';
import AppointmentStatus from '@/repositories/appointment/status';
import lts from '@/services/lts';
import StatusMixin from '@/components/appointments/mixin/status';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        StatusMixin,
    ],
    components: {
        AppointmentColumn,
        AppointmentHeader,
        TimeBlock,
    },
    props: {
        dayList: [Array],
        timeRange:  {
            type: [Object, Array],
            required: true
        },
        patient: {
            type: Object,
        },
        cashier: Object,
        cashboxes: {
            type: Array,
            default: () => [],
        },
        readonly: {
            type: Boolean,
            default: false,
        },
        checkboxCashboxes: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            initialLeft: 0,
            collapsed: [],
            inactiveStatuses: [],
            appointmentStatuses: [],
        }
    },
    mounted() {
        if (lts.scheduleColumnsCollapsed === true) {
            this.toggleCollapseAll(true);
        }

        this.$eventHub.$on('columns-collapse', (collapsed) => {
            this.toggleCollapseAll(collapsed);
        });
        this.getStatuses();
    },
    beforeDestroy() {
        this.$eventHub.$off('columns-collapse', this.toggleCollapseAll);
    },
    methods: {
        toggleCollapse(index) {
            let columnIndex = this.collapsed.indexOf(index);

            if(columnIndex === -1) {
                this.collapsed.push(index);
            } else {
                this.collapsed.splice(columnIndex, 1);
            }
        },
        toggleCollapseAll(collapsed = false) {
            if(collapsed) {
                let list = [...this.collapsed];

                _.each(this.dayList, (day) => {
                    let index = this.collapsed.indexOf(day.id);

                    if(index === -1) {
                        list.push(day.id);
                    }
                });

                this.collapsed = list;
                return;
            }

            this.collapsed = [];
        },
        isCollapsed(index) {
            return this.collapsed.indexOf(index) !== -1;
        },
        thumbLeft(index) {
            this.$emit('thumb-left', {index});
        },
        thumbRight(index) {
            this.$emit('thumb-right', {index});
        },
        switchDay(index, {date}) {
            this.$emit('switch-day', { index, date });
        },
        scrollTimeAndHeader(evt) {
            this.$eventHub.$emit('scroll-schedule', evt.target.scrollTop);
            this.$refs.headers.scrollLeft = evt.target.scrollLeft;
        },
        getStatuses() {
            let status = new AppointmentStatus();
            status.fetchList().then((response) => {
                this.appointmentStatuses = response;
                this.inactiveStatuses.push(this.getStatusIdBySystemStatus(response, CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP));
                this.inactiveStatuses.push(this.getStatusIdBySystemStatus(response, CONSTANTS.APPOINTMENT.STATUSES.DID_NOT_COME));
            });
        },
    }
}
</script>
