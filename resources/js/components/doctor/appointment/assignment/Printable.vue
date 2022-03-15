<template>
    <div>
        <h2>{{ header }}</h2>
        <printable-table
            v-if="isPicked('consultations')"
            :list="consultations"
            :fields="consultationFields"
            :title="__('Назначенные консультации врачей')" />
        <printable-table
            v-if="isPicked('analysisList')"
            :list="analysisList"
            :fields="analysisFields"
            :title="__('Назначенные анализы')" />
        <printable-table
            v-if="isPicked('diagnosticsList')"
            :list="diagnosticsList"
            :fields="diagnosticFields"
            :title="__('Назначенная аппаратная диагностика')" />
        <printable-table
            v-if="isPicked('medicineList')"
            :list="medicineList"
            :fields="medicineFields"
            :title="__('Назначенные препараты')" />
        <printable-table
            v-if="isPicked('physiotherapyList')"
            :list="physiotherapyList"
            :fields="physiotherapyFields"
            :title="__('Назначенная физиотерапия')" />
        <printable-table
            v-if="isPicked('procedureList')"
            :list="procedureList"
            :fields="procedureFields"
            :title="__('Назначенные процедуры')" />
    </div>
</template>
<script>
import PrintableTable from './PrintableTable.vue';

export default {
    components: {
        PrintableTable,
    },
    props: {
        list: {
            type: Array,
            default: () => [],
        },
        appointment: Object,
        analysisList: {
            type: Array,
            default: () => [],
        },
        medicineList: {
            type: Array,
            default: () => [],
        },
        procedureList: {
            type: Array,
            default: () => [],
        },
        physiotherapyList: {
            type: Array,
            default: () => [],
        },
        diagnosticsList: {
            type: Array,
            default: () => [],
        },
        consultations: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            header: this.getPatientInfo(),
            consultationFields: this.getConsultationFields(),
            medicineFields: this.getMedicineFields(),
            diagnosticFields: this.getDiagnosticFields(),
            physiotherapyFields: this.getPhysiotherapyFields(),
            procedureFields: this.getProcedureFields(),
            analysisFields: this.getAnalysisFields(),
        }
    },
    methods: {
        isPicked(listName) {
            return this.list.indexOf(listName) != -1;
        },
        getPatientInfo() {
            return __('Пациент:') + ' ' + this.appointment.patient.full_name + ' ' +
                    __('/ № карты:') + ' ' + (this.appointment.patient_card ? this.appointment.patient_card.number : '') + ' ' +
                    __('/ Дата визита:') + ' ' + this.$formatter.dateFormat(this.appointment.date) + ' ' +
                    __('/ Врач:') + ' ' + this.appointment.doctor.name;
        },
        getConsultationFields() {
            return [
                {
                    name: 'specialization_name',
                    title: __('Название специализации врача'),
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                },
            ];
        },
        getDiagnosticFields() {
            return [
                {
                    name: 'name',
                    title: __('Диагностика'),
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                },
            ];
        },
        getPhysiotherapyFields() {
            return [
                {
                    name: 'name',
                    title: __('Название физиотерапии'),
                },
                {
                    name: 'assigned_quantity',
                    title: __('Количество физиотерапий'),
                },
            ];
        },
        getProcedureFields() {
            return [
                {
                    name: 'name',
                    title: __('Название процедуры'),
                },
                {
                    name: 'assigned_quantity',
                    title: __('Количество процедур'),
                    dataClass: 'text-right',
                },
            ];
        },
        getAnalysisFields() {
            return [
                {
                    name: 'created',
                    title: __('Дата'),
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'analysis.name',
                    title: __('Название анализов'),
                },
                {
                    name: 'quantity',
                    title: __('Кол-во анализов'),
                    dataClass: 'text-right',
                },
                {
                    name: 'date_expected_pass',
                    title: __('Реком. Дата сдачи'),
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value, 'DD.MM.YY');
                    },
                    dataClass: 'text-right',
                },
            ];
        },
        getMedicineFields() {
            return [
                {
                    name: 'name',
                    title: __('Название медикамента'),
                },
                {
                    name: 'cost',
                    title: __('Из аптек'),
                    formatter: (value) => {
                        return isNaN(value) ? this.$formatter.boolToString(true) : '';
                    }
                },
                {
                    name: 'medication_duration',
                    title: __('Принимать, дней'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'quantity',
                    title: __('Кол-во, шт'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                },
            ];
        }
    },
}
</script>
