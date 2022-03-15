<template>
    <content-placeholder v-if="loading" />
    <div v-else>
        <div 
            v-if="hasIncomingCall"
            class="paragraph">
            <h4>{{ __('Входящие') }}</h4>
            <incoming-call 
                :call="incomingCall"
                @select-patient="selectPatientContact"
                @select-unknown="selectUnknownContact"
                @show-cabinet="showCabinet" />
        </div>
        <div 
            v-if="hasEnquiry"
            class="paragraph">
            <h4>{{ __('Заявка с сайта') }}</h4>
            <enquiry 
                :enquiry="enquiry"
                @select-patient="selectPatientContact"
                @select-unknown="selectUnknownContact"
                @show-cabinet="showCabinet" />
        </div>
        <div 
            v-if="hasOutgoingCalls"
            class="paragraph">
            <h4>{{ __('Исходящие') }}</h4>
            <outgoing-calls 
                :calls="outgoingCalls"
                @select-patient="selectPatientContact"
                @select-unknown="selectUnknownContact"
                @show-cabinet="showCabinet" />
        </div>
        <div 
            v-if="!hasIncomingCall && !hasOutgoingCalls && !hasEnquiry"
            class="paragraph">
            <h4>{{ __('Неопределенный контакт') }}</h4>
            <undefined-contact 
                :process-log="item" 
                @select-unknown="selectUnknownContact" />
        </div>
        <div 
            v-if="hasAppointmentActions"
            class="paragraph">
            <h4>{{ __('Записи') }}</h4>
            <appointment-actions 
                :actions="appointmentActions"
                @select-patient="selectPatientContact"
                @show-cabinet="showCabinet" />
        </div>
        <div 
            v-if="hasCallActions"
            class="paragraph">
            <h4>{{ __('Звонки') }}</h4>
            <call-actions 
                :actions="callActions"
                @select-patient="selectPatientContact"
                @select-employee="selectEmployeeContact"
                @show-cabinet="showCabinet" />
        </div>
        <div 
            v-if="hasPatientActions"
            class="paragraph">
            <h4>{{ __('Пациенты') }}</h4>
            <patient-actions 
                :actions="patientActions"
                @select-patient="selectPatientContact"
                @show-cabinet="showCabinet" />
        </div>
        <div 
            v-if="hasWaitListRecord"
            class="paragraph">
            <h4>{{ __('Запись в листе ожидания') }}</h4>
            <wait-list-record 
                :record="waitListRecord"
                @select-patient="selectPatientContact"
                @select-unknown="selectUnknownContact"
                @show-cabinet="showCabinet" />
        </div>
    </div>
</template>

<script>
import IncomingCall from './details/IncomingCall.vue';
import OutgoingCalls from './details/OutgoingCalls.vue';
import Enquiry from './details/Enquiry.vue';
import UndefinedContact from './details/UndefinedContact.vue';
import PatientActions from './details/PatientActions.vue';
import CallActions from './details/CallActions.vue';
import AppointmentActions from './details/AppointmentActions.vue';
import WaitListRecord from './details/WaitListRecord.vue';
import CONSTANT from '@/constants';
import SelectContactMixin from '../mixins/select-contact';

export default {
    mixins: [
        SelectContactMixin,
    ],
    components: {
        IncomingCall,
        OutgoingCalls,
        Enquiry,
        UndefinedContact,
        PatientActions,
        CallActions,
        AppointmentActions,
        WaitListRecord,
    },
    props: {
        item: Object,
        modalComponent: Object,
    },
    data() {
        return {
            loading: true,
        };
    },
    computed: {
        hasIncomingCall() {
            return this.isCallIncoming();
        },
        incomingCall() {
            return this.isCallIncoming() ? this.item.call_details : null;
        },
        hasEnquiry() {
            return this.item.enquiry_details !== null;
        },
        enquiry() {
            return this.item.enquiry_details;
        },
        hasOutgoingCalls() {
            return this.isCallOutgoing()
                || this.hasActions(CONSTANT.CALL_ACTION.SUBJECT.CALL_LOG);
        },
        outgoingCalls() {
            return (this.isCallOutgoing() ? [this.item.call_details] : [])
                .concat(
                    this.getActions(CONSTANT.CALL_ACTION.SUBJECT.CALL_LOG)
                        .map((action) => action.related)
                );
        },
        hasPatientActions() {
            return this.hasActions(CONSTANT.CALL_ACTION.SUBJECT.PATIENT);
        },
        patientActions() {
            return this.getActions(CONSTANT.CALL_ACTION.SUBJECT.PATIENT);
        },
        hasCallActions() {
            return this.hasActions(CONSTANT.CALL_ACTION.SUBJECT.CALL);
        },
        callActions() {
            return this.getActions(CONSTANT.CALL_ACTION.SUBJECT.CALL);
        },
        hasAppointmentActions() {
            return this.hasActions(CONSTANT.CALL_ACTION.SUBJECT.APPOINTMENT);
        },
        appointmentActions() {
            return this.getActions(CONSTANT.CALL_ACTION.SUBJECT.APPOINTMENT);
        },
        hasWaitListRecord() {
            return this.item.wait_list_details !== null;
        },
        waitListRecord() {
            return this.item.wait_list_details;
        },
    },
    mounted() {
        this.item.fetch([
            'call', 
            'enquiry', 
            'actions', 
            'default', 
            'auto_process',
            'wait_list_record',
        ]).then(() => {
            this.loading = false;
        });
    },
    methods: {
        isCallIncoming() {
            return this.item.call_details !== null 
                && this.item.call_details.type == CONSTANT.CALL_LOG.TYPES.INCOMING;
        },
        isCallOutgoing() {
            return this.item.call_details !== null 
                && this.item.call_details.type == CONSTANT.CALL_LOG.TYPES.OUTGOING;
        },
        hasActions(type) {
            return this.item.related_actions.some((item) => item.related_type === type);
        },
        getActions(type) {
            return this.item.related_actions.filter((item) => item.related_type === type);
        },
        showCabinet(patient) {
            this.$router.push({name: 'patient-cabinet-info', params: {patientId: patient.id}});
        },
    }
};
</script>