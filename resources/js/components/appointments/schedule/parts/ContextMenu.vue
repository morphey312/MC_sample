<template>
    <div class="context-menu-wrapper">
        <div class="popover-close-btn" @click="close">
            <div>
                <i class="el-icon-close"></i>
            </div>
        </div>
        <template>
            <div v-if="$can('appointments.external-change')">
                <div>
                    <el-popover
                        placement="right-start"
                        width="200"
                        v-model="showAppointmentStatus"
                        popper-class="schedule-popover"
                        trigger="click" >
                        <div class="context-menu-wrapper">
                            <div
                                v-for="item in appointmentStatuses"
                                @click="setAppointmentStatus(item.id)">
                                {{ item.value }}
                            </div>
                        </div>
                        <template  slot="reference">
                            <div> {{ __('Изменить статус') }}</div>
                        </template>
                    </el-popover>
                </div>
            </div>
        </template>
        <template v-if="appointmentActions.length != 0">
            <div
                v-for="(item, index) in appointmentActions"
                :key="`a-${index}`"
                @click="emitEvent(item.action)">
                    {{ item.title }}
            </div>
            <hr v-if="patientActions.length != 0 || callRequestActions.length != 0">
        </template>
        <template v-if="patientActions.length != 0">
            <div
                v-for="(item, index) in patientActions"
                :key="`p-${index}`"
                @click="emitEvent(item.action)">
                {{ item.title }}
            </div>
            <hr v-if="surgeryActions.length != 0">
        </template>
        <template v-if="surgeryActions.length != 0">
            <div
                v-for="(item, index) in surgeryActions"
                :key="`sa-${index}`"
                @click="emitEvent(item.action)">
                {{ item.title }}
            </div>
            <hr v-if="callRequestActions.length != 0">
        </template>
        <template v-if="callRequestActions.length != 0">
            <div
                v-for="(item, index) in callRequestActions"
                :key="`cr-${index}`"
                @click="emitEvent(item.action)">
                {{ item.title }}
            </div>
            <hr v-if="footerActions.length != 0 || isFillQuestionnaireVisible">
        </template>
        <template v-if="footerActions.length != 0">
            <div
                v-for="(item, index) in footerActions"
                :key="`fa-${index}`"
                @click="emitEvent(item.action)">
                {{ item.title }}
            </div>
        </template>
    </div>
</template>

<script>
import AppointmentStatusRepository from '@/repositories/appointment/status';

export default {
    props: {
        appointment: Object,
        visible: {
            type: Boolean,
            default: false,
        },
        readonly: {
            type: Boolean,
            default: false,
        },
        isQuestionnaireFilled: {
            type: Boolean,
            default: false
        },
        isOperational: {
            type: Boolean,
            default: false
        },
        appointmentStatuses: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            showAppointmentStatus: false,
            appointmentActions: this.getAppointmentActions(),
            patientActions: this.getPatientActions(),
            callRequestActions: this.getCallRequestActions(),
            footerActions: this.getFooterActions(),
            surgeryActions: this.getSurgeryActions(),
            isChangingStatus: false,
        }
    },
    computed: {
        isFillQuestionnaireVisible(){
            if(!this.$can('appointments.questionnaire') || this.isQuestionnaireFilled){
                return false;
            }

            return this.appointment.patient.filled_questionnaires === undefined ||
                this.appointment.patient.filled_questionnaires.length === 0;
        }
    },
    methods: {
        setAppointmentStatus(id) {
            this.isChangingStatus = true;
            this.appointment.changeStatus({appointment_status_id: id}).then(() => {
                this.$info(__('Статус записи успешно обновлен'));
                this.isChangingStatus = false;
            });
        },
        close() {
            this.showAppointmentStatus = false
            this.$emit('close');
        },
        emitEvent(name) {
            this.$emit(name, {visible: false});
        },
        getAppointmentActions() {
            return [
                {
                    action: 'edit',
                    title: __('Изменить запись'),
                    visible: !this.readonly && this.$canManage('appointments.update', [this.appointment.clinic_id]),
                },
                {
                    action: 'delete',
                    title: __('Удалить запись'),
                    visible: !this.readonly && this.$canManage('appointments.delete', [this.appointment.clinic_id]),
                },
                {
                    action: 'copy',
                    title: __('Копировать запись'),
                    visible: !this.readonly && this.$canCreate('appointments'),
                },
                {
                    action: 'cut',
                    title: __('Перенести запись'),
                    visible: !this.readonly && this.$canManage('appointments.update', [this.appointment.clinic_id]),
                },
            ].filter((item) => item.visible !== false);
        },
        getPatientActions() {
            return [
                {
                    action: 'set-schedule-patient',
                    title: __('Задать пациента'),
                    visible: !this.readonly,
                },
                {
                    action: 'edit-appointment-patient',
                    title: __('Изменить пациента'),
                    visible: !this.readonly && this.$canUpdate('patients'),
                },
                {
                    action: 'show-patient-payments',
                    title: __('Добавить платёж'),
                    visible: !this.readonly && this.isAddPaymentVisible()
                    ,
                },
                {
                    action: 'show-appointment-log',
                    title: __('Операции по записи пациента'),
                    visible: !this.readonly && this.$canManage('appointments.update', [this.appointment.clinic_id]),
                },
                {
                    action: 'go-patient-cabinet',
                    title: __('Перейти в личный кабинет пациента'),
                    visible: this.$can('patient-cabinet.access'),
                },
                {
                    action: 'go-doctor-cabinet',
                    title: __('Перейти на прием в кабинете врача'),
                    visible: !this.readonly && this.$can('doctor-cabinet.access'),
                },
                {
                    action: 'call-patient',
                    title: __('Позвонить пациенту'),
                    visible: !this.readonly && this.$can('process-logs.access'),
                },
            ].filter((item) => item.visible !== false);
        },
        isAddPaymentVisible(){
            return this.$can('payments.create-clinic') && this.$store.state.user.isCashier
        },
        getCallRequestActions() {
            return [
                {
                    action: 'set-sms-reminder',
                    title: __('Отправить СМС'),
                    visible: !this.readonly && this.$can('sms-reminders.create'),
                },
                {
                    action: 'add-call-request',
                    title: __('Изменить/создать заявку на прозвон'),
                    visible: !this.readonly && (this.appointment.call_request === null
                        ? this.$canCreate('call-requests')
                        : this.$canManage('call-requests.update', [this.appointment.call_request.clinic_id])),
                },
                {
                    action: 'delete-call-request',
                    title: __('Отменить заявку на прозвон'),
                    visible: !this.readonly && this.appointment.call_request !== null && this.$canManage('call-requests.update', [this.appointment.call_request.clinic_id]),
                }
            ].filter((item) => item.visible !== false);
        },
        getFooterActions() {
            return [
                {
                    action: 'fill-patient-questionnaire',
                    title: __('Заполнить Анкету'),
                    visible: this.isFillQuestionnaireVisible,
                },
            ].filter((item) => item.visible !== false);
        },
        getSurgeryActions() {
            if (this.isOperational && this.$can('appointments.add-surgery-doctors')) {
                return [
                    {
                        action: 'set-surgery-employees',
                        title: __('Добавить операционных сотрудников'),
                        visible: !this.readonly,
                    }
                ];
            }
            return [];
        },
    },
    watch: {
        visible(v) {
            if (v) {
                this.appointmentActions = this.getAppointmentActions();
                this.patientActions = this.getPatientActions();
                this.callRequestActions = this.getCallRequestActions();
                this.footerActions = this.getFooterActions();
            }
        },
    },
}
</script>
