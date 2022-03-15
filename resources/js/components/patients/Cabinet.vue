<template>
    <div
        v-loading="patient === null"
        class="patient-cabined">
        <template v-if="patient">
            <patient-menu
                ref="patientMenu"
                :patient="patient" />
            <router-view :patient="patient"></router-view>
        </template>
    </div>
</template>

<script>
import PatientMenu from './cabinet/Menu.vue';
import Patient from '@/models/patient';

export default {
    components: {
        PatientMenu,
    },
    data() {
        return {
            patient: null,
        };
    },
    mounted() {
        this.load();
    },
    methods: {
        load() {
            let patient = new Patient({id: this.$route.params.patientId});
            patient.fetch([
                'contact_details',
                'archive_card_numbers',
                'clinics',
                'relatives',
                'discount_cards',
                'insurance_policies',
                'appointment_documents',
                'payments',
                'client_ids'
            ]).then(() => {
                this.patient = patient;
            });
        },
    },
};
</script>
