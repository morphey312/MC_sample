<template>
</template>

<script>
import moneyFormatter from "@/services/money-formatter.js";

export default {
    props: {
        appointment: Object,
        clinic: Object,
        patient: Object,
        reciever: Object,
        services: Array,
        index: Number,
    },
    computed: {
        shortNameSigner() {
            return this.$formatter.nameInitialsFormat(this.reciever.signer);
        },
        total() {
            return _.sumOf(this.services, 'fullPrice').toFixed(2);
        },
        totalDiscount() {
            return _.sumOf(this.services, 'discount').toFixed(2);
        },
        totalWithDiscount() {
            return _.sumOf(this.services, 'cost').toFixed(2);
        },
        totalInWords() {
            return moneyFormatter.moneyToString(this.totalWithDiscount);
        },
        patientName() {
            return _.capitalize(this.patient.firstname) + ' ' + this.patient.lastname.toUpperCase();
        },
        signerName() {
            let signerFullNameArray = this.reciever.signer.split(' ');
            return _.capitalize(signerFullNameArray[1]) + ' ' + signerFullNameArray[0].toUpperCase();
        },
        getFormatedAdditional() {
            return this.reciever.official_additional.split("\n");
        },
    },

}
</script>
