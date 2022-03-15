<template>
    <div class="assignment-wrapper">
        <div class="row" v-if="hasItems('operation_services')">
            <printable-table
                :fields="operationServicesFields"
                :list="treatmentActivities.operation_services"
                :title="__('Процедуры и манипуляции')" />
        </div>
        <div class="row" v-if="hasItems('medicines')">
            <printable-table
                :fields="assignedMedicineFields"
                :list="treatmentActivities.medicines"
                :title="__('Медикаменты')" />
        </div>
        <div class="row" v-if="hasItems('analysis_results')">
            <printable-table
                :fields="analysisResultFields"
                :list="treatmentActivities.analysis_results"
                :title="__('Анализы')" />
        </div>
        <div class="row" v-if="hasItems('consultations')">
            <printable-table
                :fields="consultationFields"
                :list="treatmentActivities.consultations"
                :title="__('Назначенные консультации специалистов')" />
        </div>
    </div>
</template>
<script>
import PrintableTable from '@/components/doctor/appointment/assignment/PrintableTable.vue';

export default {
    components: {
        PrintableTable,
    },
    props: {
        treatmentActivities: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            operationServicesFields: [
                {
                    name: 'appointment_date',
                    title: __('Дата'),
                    width: '15%',
                    formatter: (value) => {
                        return this.formatDate(value);
                    },
                },
                {
                    name: 'service_name',
                    title: __('Название'),
                    width: '55%',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'assigned_quantity',
                    title: __('Количество'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'used_quantity',
                    title: __('Выполнено'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
            ],
            assignedMedicineFields: [
                {
                    name: 'appointment.date',
                    title: __('Дата'),
                    width: '15%',
                    formatter: (value) => {
                        return this.formatDate(value);
                    },
                },
                {
                    name: 'medicine.name',
                    title: __('Название препарата'),
                    width: '40%',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'medicine.quantity',
                    title: __('Назначено'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'medicine.issued_quantity',
                    title: __('Выдано'),
                    width: '15%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'medicine.comment',
                    title: __('Комментарий'),
                    width: '15%',
                },
            ],
            analysisResultFields: [
                {
                    name: 'analysis.name',
                    title: __('Название анализов'),
                    width: '50%',
                },
                {
                    name: 'quantity',
                    title: __('Назначено'),
                    width: "25%",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'used_quantity',
                    title: __('Сданы'),
                    width: "25%",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
            ],
            consultationFields: [
                {
                    name: 'appointment.date',
                    title: __('Дата'),
                    width: '40%',
                    formatter: (value) => {
                        return this.formatDate(value);
                    },
                },
                {
                    name: 'consultation.specialization_name',
                    title: __('Специализация'),
                    width: "60%",
                },
            ],
        };
    },
    methods: {
        formatDate(date, format = 'DD.MM.YYYY') {
            return this.$formatter.dateFormat(date, format);
        },
        hasItems(key) {
            return this.treatmentActivities[key] && this.treatmentActivities[key].length !== 0;
        },
    },
}
</script>