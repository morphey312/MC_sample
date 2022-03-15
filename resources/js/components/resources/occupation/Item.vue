<template>
    <div>
        <p>{{ cardNumber }}</p>
        <p>{{ patientName }}</p>
    </div>
</template>
<script>
export default {
    props: {
        item: {
            type: Object,
        },
    },
    computed: {
        cardNumber() {
            return (this.item.patient && this.item.patient.card_number) ? this.item.patient.card_number + '.' : '';
        },
        patientName() {
            return this.item.patient ? this.item.patient.full_name : '';
        },
    }
}
</script>