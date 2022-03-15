<template>
    <printable-table
        :list="tableData"
        :fields="fields" />
</template>
<script>
import PrintableTable from '@/components/doctor/appointment/assignment/PrintableTable.vue';
import ChartFormatMixin from '@/components/reports/interactive/mixins/chart-format';

export default {
    mixins: [
        ChartFormatMixin,
    ],
    components: {
        PrintableTable,
    },
    props: {
        tableData: {
            type: Array,
            default: () => [],
        },
        clinicsToDisplay: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            fields: [],
        }
    },
    beforeMount() {
        this.fields = this.getFields();
    },
    methods: {
        getFields() {
            return [
                {
                    name: 'name',
                    title: '',
                },
                {
                    name: 'period-first',
                    title: '',
                },
                ...this.getClinicFields(),
            ]
        },
        getClinicFields() {
            let fields = [];
            this.clinicsToDisplay.forEach(clinic => {
                fields.push({
                    name: `clinic-${clinic.id}`,
                    title: this.clinicLabelMap[clinic.value],
                });
            });
            return fields;
        },
        getClinicLabel(name) {
            return this.clinicLabelMap[name] || name;
        },
    }
}
</script>