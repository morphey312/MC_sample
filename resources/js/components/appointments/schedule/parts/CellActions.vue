<template>
    <span>
        <span @click="addAppointment" class="cell-content">
            <template v-if="collapsed">
                <svg-icon name="plus-alt" class="icon-tiny icon-blue" />
            </template>
            <template v-else>
                {{ __('Доб. запись (Маx:') }} {{appointmentDuration}} {{ __('м.)') }}
            </template>
        </span>
        <el-popover
            ref="popover"
            placement="bottom"
            width="190"
            popper-class="schedule-popover"
            trigger="manual" >
            <slot>
                <div><a href="#" @click.prevent="newAppointment">{{ __('Добавить новую запись') }}</a></div>
                <div v-if="isActionCopy"><a  href="#" @click.prevent="copyAppointment">{{ __('Вставить запись') }}</a></div>
                <div v-else><a href="#" @click.prevent="pasteAppointment">{{ __('Перенести запись') }}</a></div>
            </slot>
        </el-popover>
        <span
            v-if="isLocked"
            class="float-right lock-cell"
            @click="toggleLockTime">
            <el-popover
                ref="notification"
                placement="bottom"
                width="200"
                trigger="hover">
                {{ __('Разблокировать время') }}
                <template  slot="reference">
                    <svg-icon name="unlock" class="icon-tiny icon-green" />
                </template>
            </el-popover>
        </span>
        <span
            v-else
            class="float-right lock-cell"
            @click="toggleLockTime">
            <svg-icon name="lock" class="icon-tiny icon-blue" />
        </span>
        <span
            v-if="!isLocked"
            v-show="$can('appointments.doctor-block')"
            class="float-right lock-cell"
            style="margin-right: 7px;"
            @click="lockDoctorTime">
            <svg-icon style="margin-left: auto;" name="user-alt" class="icon-blue icon-tiny" />
        </span>
    </span>
</template>

<script>
import { on, off } from 'element-ui/src/utils/dom';

export default {
    props: {
        appointmentDuration: Number,
        isActionCopy: Boolean,
        isLocked: Boolean,
        collapsed: {
            type: Boolean,
            default: false,
        },
    },
    created() {
        this.documentClickHandler = (e) => {
            if (this.$refs.popover) {
                this.$refs.popover.handleDocumentClick(e);
            }
        };
    },
    mounted() {
        on(document, 'click', this.documentClickHandler);
        on(document, 'contextmenu', this.documentClickHandler);
    },
    beforeDestroy() {
        off(document, 'click', this.documentClickHandler);
        off(document, 'contextmenu', this.documentClickHandler);
    },
    methods: {
        addAppointment(event) {
            if (this.$canCreate('appointments')) {
                this.$emit('add-appointment', event);
            }
            return false;
        },
        newAppointment() {
            this.$emit('new-appointment');
        },
        copyAppointment() {
            if (this.$canCreate('appointments')) {
                return this.$emit('copy-appointment');
            }
            return false;
        },
        pasteAppointment() {
            this.$emit('paste-appointment');
        },
        toggleLockTime() {
            if (this.$canCreate('appointments')) {
                return this.$emit('toggle-lock-time');
            }
            return false;
        },
        lockDoctorTime(){
            this.$emit('toggle-lock-doctor-time')
        }
    },
}
</script>
