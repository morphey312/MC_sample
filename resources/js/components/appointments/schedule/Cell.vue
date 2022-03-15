<template>
    <div
        class="schedule-cell"
        :class="[
            lockedClass,
            outOfDaysheet ? 'out-of-daysheet' : '',
            hasAppointment() ? 'has-appointment' : '',
        ]"
        @mouseover="hoveredCell = true"
        @mouseleave="hoveredCell = false">
        <drop
            @drop="handleDrop"
            @dragover="handleDragover(column, ...arguments)"
            @dragleave="dragOverClass = false"
            :class="{'drag-over-cell': dragOverClass}">
            <drag
                v-if="appointmentList[time]"
                ref="dragElement"
                :transfer-data="{column, appointment: appointmentList[time]}"
                class="draggable-item "
                :class="dragClass"
                :style="cellStyle"
                :draggable="!readonly"
                @dragstart="hoverOut(true)">
                <item
                    ref="item"
                    :item="appointmentList[time]"
                    :owner="columnModel.doctor"
                    :styles="itemStyle"
                    :height="height"
                    :readonly="readonly"
                    :inactive-statuses="inactiveStatuses"
                    :cashier="cashier"
                    :cashboxes="cashboxes"
                    :appointment-statuses="appointmentStatuses"
                    :checkbox-cashboxes="checkboxCashboxes"
                    @hover-in="hoverIn"
                    @hover-out="hoverOut"
                    @edit="editAppointment"
                    @delete="deleteAppointment"
                    @added-to-clipboard="inClipboard = true"
                    @set-attention="setAttention"
                />
            </drag>
            <drag
                v-else-if="isLocked && locked.type === 'fixed' && locked.start === time"
                ref="dragElement"
                :transfer-data="{column}"
                class="draggable-item "
                :class="dragClass"
                :style="cellStyle"
                :draggable="false"
                @dragstart="hoverOut(true)">
                <locked-item
                    ref="item"
                    :styles="itemStyle"
                    :lock="locked"
                    @hover-in="hoverIn"
                    @unlock="unlockTime"
                    @watchblock="emitWatchBlock"
                    @hover-out="hoverOut"
                />
            </drag>
            <div v-else-if="(readonly || !canCreate) && !outOfDaysheet" class="ellipsis">
                {{ time }} &mdash; {{ specializationShort}}
            </div>
            <div v-else class="ellipsis">
                {{ time }}
                <template v-if="hoveredCell && lockedClass !== lockedForeign">
                    &mdash;
                    <cell-actions
                        ref="actions"
                        :appointment-duration="appointmentDuration"
                        :is-action-copy="isActionCopy"
                        :is-locked="isLocked"
                        :collapsed="collapsed"
                        @add-appointment="addAppointment"
                        @new-appointment="newAppointment"
                        @copy-appointment="copyAppointment"
                        @paste-appointment="pasteAppointment"
                        @toggle-lock-time="toggleLockTime"
                        @toggle-lock-doctor-time="toggleLockDoctorTime"
                    />
                </template>
                <template v-else-if="lockedClass == lockedForeign">
                    &mdash;
                    {{ __('Время заблокировано') }}
                    <span class="float-right">
                        <svg-icon :name="$can('appointments.unblock-hard-locks') ? 'unlock' : 'lock'"
                                  class="icon-tiny"
                                  @click="unlockTime"
                                  :class="{
                                      'icon-blue' : $can('appointments.unblock-hard-locks'),
                                      'icon-grey' : !$can('appointments.unblock-hard-locks')
                                  }" />
                    </span>
                </template>
                <template v-else-if="!outOfDaysheet && !hoveredCell">
                    &mdash;
                    {{ specializationShort }}
                </template>
            </div>
        </drop>
    </div>
</template>

<script>
import Item from './Item.vue';
import LockedItem from './LockedItem.vue';
import CellActions from './parts/CellActions.vue';
import DoctorBlockModal from './modals/DoctorBlock.vue';
import CONSTANTS from '@/constants';
import StatusMixin from '@/components/appointments/mixin/status';
import PatientLogButton from "./parts/PatientLogButton";

export default {
    mixins: [
        StatusMixin,
    ],
    components: {
        Item,
        LockedItem,
        CellActions,
    },
    props: {
        time: String,
        appointmentList: Object,
        appointmentDuration: Number,
        column: Number,
        locked: {
            type: Object,
            default: null
        },
        columnModel: {
            type: Object,
        },
        outOfDaysheet: {
            type: Boolean,
            default: false
        },
        specializations: {
            type: Array,
            default: () => [],
        },
        timeRange: {
            type: Array,
            default: () => [],
        },
        collapsed: {
            type: Boolean,
            default: false
        },
        readonly: {
            type: Boolean,
            default: false,
        },
        inactiveStatuses: {
            type: Array,
            default: () => [],
        },
        canCreate: {
            type: Boolean,
            default: true,
        },
        cashier: Object,
        cashboxes: {
            type: Array,
            default: () => [],
        },
        appointmentStatuses: {
            type: Array,
            default: () => [],
        },
        checkboxCashboxes: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            cellHeight: CONSTANTS.SCHEDULE_CELL_HEIGHT,
            hoveredItem: false,
            hoveredCell: false,
            height: '',
            dragOverClass: '',
            inClipboard: false,
            lockedForeign: 'locked-foreign',
            attention: false,
        }
    },
    computed: {
        blockHeight() {
            return this.height + 'px';
        },
        specializationShort(){
            let output = [];
            this.specializations.map((specialization) => {
                output.push(specialization.short_name);
            });

            return output.join(', ');
        },
        cellStyle() {
            return {
                'border-color': this.isLocked ? '#F2F2F2' :this.borderColor(this.appointmentList[this.time]),
                'height': this.blockHeight,
            }
        },
        itemStyle() {
            return {
                'background-color': this.backgroundColor(this.appointmentList[this.time]),
                'min-height': this.blockHeight,
            }
        },
        isActionCopy() {
            let appointment = this.getCopied();
            return appointment && appointment.action == 'copy';
        },
        isLocked() {
            return !_.isEmpty(this.locked);
        },
        lockedClass() {
            if(!this.isLocked) {
                return '';
            }

            return  (this.$parent.loggedEmployeeId === this.locked.employee_id)
                    ? 'locked'
                    : this.lockedForeign;
        },
        dragClass() {
            return {
                'full-cell-info': this.hoveredItem,
                'copy-highlight': this.inClipboard,
                'attention-border': this.attention,
            }
        },
    },
    created() {
        this.listenHighlightAppointments = (status) => {
            if (this.appointmentList[this.time]) {
                this.highLightAppointment(status);
            }
        }

        this.listenClipboardCleared = () => {
            this.inClipboard = false;
        }
    },
    mounted() {
        this.height = this.setHeight();
        this.$eventHub.$on('highlight-appointments', this.listenHighlightAppointments);
        this.$eventHub.$on('clipboard-cleared', this.listenClipboardCleared);

        this.$watch('appointmentList', () => {
            if(this.appointmentList[this.time]) {
                this.height = this.setHeight();
            } else {
                this.setAttention(false);
            }
        });
    },
    beforeDestroy() {
        this.$eventHub.$off('highlight-appointments', this.listenHighlightAppointments);
        this.$eventHub.$off('clipboard-cleared', this.listenClipboardCleared);
    },
    methods: {
        setAttention(val) {
            this.attention = val;
        },
        showContextMenu(e) {
            e.preventDefault();
            if(this.$refs.actions && this.$refs.actions.$refs.popover) {
                this.$refs.actions.$refs.popover.doShow();
            }
        },
        highLightAppointment(status) {
            if(!status) {
                this.hoveredItem = false;
                return;
            }

            if(this.appointmentList[this.time].appointment_status_id == status){
                this.hoveredItem = true;
                return;
            }

            this.hoveredItem = false;
            return;
        },
        addAppointment(e) {
            if(this.getCopied()) {
                return this.showContextMenu(e);
            }

            return this.newAppointment();
        },
        newAppointment() {
            this.$emit('add-appointment', {time: this.time, locked: this.isLocked});
        },
        copyAppointment() {
            this.$emit('copy-appointment', {time: this.time, locked: this.isLocked});
        },
        pasteAppointment() {
            this.$emit('paste-appointment', {time: this.time, locked: this.isLocked});
        },
        getCopied() {
            return this.$parent.copiedItem;
        },
        editAppointment() {
            this.$emit('edit-appointment', {appointment: this.appointmentList[this.time]});
        },
        deleteAppointment() {
            this.$emit('delete-appointment', {appointment: this.appointmentList[this.time]});
        },
        hoverIn({height}) {
            this.hoveredItem = true;

            if(height > this.height){
                this.height = height;
            }
        },
        hoverOut(dragStart) {
            this.hoveredItem = false;
            this.height = dragStart ? this.cellHeight : this.setHeight();
        },
        handleDragover(column, data, event) {
            if (this.$refs.dragElement || (data.column != column) || this.isLocked) {
                event.dataTransfer.dropEffect = 'none';
            } else {
                this.dragOverClass = true;
                event.dataTransfer.dropEffect = 'move';
            }
        },
        handleDrop({appointment}, event) {
            this.dragOverClass = true;
            this.height = this.setHeight();
            if (this.isNotSameTime(appointment)) {
                this.$emit('dropped', {dropTime: this.time, item: appointment});
            }
            this.dragOverClass = false;
        },
        isNotSameTime(appointment) {
            return this.time != appointment.momented.scheduleStart;
        },
        setHeight() {
            if(this.appointmentList[this.time]){
                return  (this.cellHeight * this.appointmentList[this.time].totalCells);
            }else if(this.isLocked && this.locked.type === 'fixed'){
                return  (this.cellHeight * this.getLockCells(this.locked));
            }

            return this.cellHeight;
        },
        getLockCells(lock) {
            let diff = lock.momented.end.diff(lock.momented.start, "minutes");
            if(diff > CONSTANTS.SCHEDULE_TIME_STEP) {
                return diff / CONSTANTS.SCHEDULE_TIME_STEP;
            }

            return 1;
        },
        borderColor(item) {
            if (!item || !item.status) {
                return '';
            }
            return item.status.color ? item.status.color : '#ffffff';
        },
        backgroundColor(item) {
            if(!item || !item.status) {
                return '';
            }

            if (item.appointment_status_id == this.getStatusIdBySystemStatus([item.status], CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP)){
                return '#fff';
            }
            return item.status.color ? item.status.color + '99' : '#ffffff';
        },
        toggleLockTime() {
            if(this.isLocked) {
                this.$emit('unlock-period', {time: this.time});
            } else {
                this.$emit('lock-period', {time: this.time});
            }
        },
        unlockTime(){
            this.$emit('unlock-period', {time: this.time});
        },
        emitWatchBlock(){
            this.$emit('watch-block', {time: this.time});
        },
        toggleLockDoctorTime() {
            this.$modalComponent(DoctorBlockModal, {
                time: this.time,
                timeRange: this.timeRange,
                column: this.columnModel,
                sheetSpecializations: this.specializations,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
                block: (dialog, lock) => {
                    this.$emit('lock-doctor-period', lock);
                    this.setLockHeight(lock);
                    dialog.close();
                }
            }, {
                header: __('Блокировать время приема врача'),
                width: '800px',
                customClass: 'no-footer',
            });
        },
        setLockHeight(lock){
            let start = this.$moment(`${lock.date} ${lock.start}:00`);
            let end = this.$moment(start).add(lock.duration, 'minutes');
            let tempLock = {
                momented: {
                    start: start,
                    end: end
                }
            }
            this.height = this.cellHeight * this.getLockCells(tempLock);
        },
        hasAppointment() {
            return !_.isNil(this.appointmentList[this.time]);
        }
    }
}
</script>
