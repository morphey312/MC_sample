<template>
	<div v-if="patientStatus" class="mr-20">
        <span>{{ __('Статус:') }}</span>
        <span>{{ getPatientStatus() }}</span>
        <span v-if="haveAction()">
            {{ __('ожидает на') }}
            <a v-if="patientStatus === statusNew"
               href="#"
               @click.prevent="$emit('approvePatient')">
                {{ __('подтверждение') }}
            </a>
            <a v-if="patientStatus === statusApproved"
               href="#"
               @click.prevent="$emit('signPatient')">
                {{ __('подписание') }}
            </a>
        </span>
    </div>
</template>
<script>
import CONSTANTS from '@/constants';

export default {
    data() {
        return {
            patientStatus: null
        }
    },
    computed: {
        statusNew() {
            return CONSTANTS.EHEALTH_PATIENT.STATUS.NEW
        },
        statusApproved() {
            return CONSTANTS.EHEALTH_PATIENT.STATUS.APPROVED
        }
    },
	methods: {
        setPatientStatus(status) {
            this.patientStatus = status
        },
        getPatientStatus() {
            return this.$handbook.getOption('ehealth_patient_status', this.patientStatus);
        },
        haveAction() {
            return this.patientStatus === CONSTANTS.EHEALTH_PATIENT.STATUS.NEW || this.patientStatus === CONSTANTS.EHEALTH_PATIENT.STATUS.APPROVED
        }
	}
}
</script>
