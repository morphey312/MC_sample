<template>
    <div>
        <div class="card-record-line">
            <div class="card-record-field">
                <b>{{ __('Дата:') }}</b>&nbsp;&nbsp;{{ $formatter.dateFormat(appointment.date, 'DD.MM.YYYY') }}
            </div>
            <div class="card-record-field field-right">
                <b>{{ __('№ карты:') }}</b>&nbsp;&nbsp;{{ appointment.patient_card ? appointment.patient_card.number : __('Нет карты') }}
            </div>
        </div>
        <div class="card-record-line mt-10">
            <div class="card-record-field">
                <b>{{ __('Ф.И.О.') }}</b>&nbsp;&nbsp;{{ appointment.patient.full_name }}
            </div>
        </div>
        <div class="card-record-line mt-10">
            <div class="card-record-field ">
                <b>{{ __('Дата рождения') }}</b>&nbsp;&nbsp;{{ $formatter.dateFormat(appointment.patient.birthday, 'DD.MM.YYYY') }}
            </div>
            <div class="card-record-field field-right">
                <b>{{ __('Отделение') }}</b>&nbsp;&nbsp;{{ appointment.specialization_card ? appointment.specialization_card.specialization.name : '' }}
            </div>
        </div>
        <div class="text-center mt-30">
            <h2 class="uppercase"><b>{{ __('Консультация специалиста') }}</b></h2>
        </div>
        <div class="mt-30" v-if="isPicked('complains')">
            <div>
                <b>{{ __('Жалобы:') }}</b>
            </div>
            <div class="mt-10">
                {{ record.complaints }}
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('diagnosis_mkb') && record.diagnosis_icd_names && record.diagnosis_icd_names.length != 0">
            <div>
                <b>{{ __('Диагноз МКБ (предварительный диагноз):') }}</b>
            </div>
            <div class="mt-10">
                {{ $formatter.listFormat(record.diagnosis_icd_names) }}
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('diagnosis') && record.diagnosis">
            <div>
                <b>{{ __('Диагноз:') }}</b>
            </div>
            <div class="mt-10">
                {{ record.diagnosis }}
            </div>
        </div>
        <template v-if="structure !== null">
            <fields-section
                v-for="(section, index) in structure.getSections()"
                v-if="isPicked(`section.${index}`)"
                :key="index"
                :label="section.label"
                :fields="section.children" />
        </template>
        <div class="mt-10" v-if="isPicked('diagnostics') && diagnostics.length != 0">
            <div>
                <b>{{ __('Диагностическое обследование:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="diagnostics"
                    :fields="diagnosticFields" />
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('analyses_results') && analysesResults.length != 0">
            <div>
                <b>{{ __('Забор анализов во время приема:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="analysesResults"
                    :fields="analysisResultFields" />
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('analyses') && analyses.length != 0">
            <div>
                <b>{{ __('Назначенные анализы:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="analyses"
                    :fields="analysisFields"
                />
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('consultations') && consultations.length != 0">
            <div>
                <b>{{ __('Направление на консультацию:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="consultations"
                    :fields="consultationFields" />
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('physiotherapies') && physiotherapies.length != 0">
            <div>
                <b>{{ __('Назначенная физиотерапия:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="physiotherapies"
                    :fields="physiotherapyFields" />
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('procedures') && procedures.length != 0">
            <div>
                <b>{{ __('Назначенные процедуры:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="procedures"
                    :fields="procedureFields" />
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('medicines') && medicines.length != 0">
            <div>
                <b>{{ __('Назначенные медикаменты:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="medicines"
                    :fields="medicineFields" />
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('comment') && diaryRecords.length != 0">
            <div>
                <b>{{ __('Комментарий врача:') }}</b>
            </div>
            <div class="mt-10">
                {{ $formatter.listFormat(diaryRecords, 'comment') }}
            </div>
            <div class="mt-10" v-if="conditionRecords.length != 0">
                <div 
                    v-for="record in conditionRecords"
                    :key="record.id">
                    <div>
                        <span>{{ __('t: ') }}: {{  record.temperature }} ℃,</span>
                        <span>{{ __('АД: ') }}: {{ record.at }}/{{ record.at2 }} {{ __('мм рт. ст.') }},</span>
                        <span>{{ __('ЧП: ') }}: {{ record.frequency }} {{ __('уд./мин') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-10" v-if="isPicked('next-visit') && nextVisit">
            <div>
                <b>{{ __('Дата следующего визита:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :header="false"
                    :list="nextVisits"
                    :fields="nextVisitFields" />
            </div>
        </div>
        <div class="mt-30">
            <b>{{ __('Врач:') }}</b> {{ appointmentDoctor.name }}
        </div>
        <div class="card-record-line mt-30">
            <div class="card-record-field">
                {{ __('С рекомендациями и планом обследования ознакомлен') }}&nbsp;&nbsp;&nbsp;
            </div>
            <div class="card-record-field sign-wrapper">
                <div class="sign">{{ __('(подпись пациента)') }}</div>
            </div>
        </div>
    </div>
</template>
<script>
import FieldsSection from '@/components/patients/cabinet/outpatient-cards/print/Section.vue';
import PrintableTable from '@/components/doctor/appointment/assignment/PrintableTable.vue';

export default {
    components: {
        PrintableTable,
        FieldsSection
    },
    props: {
        appointment: Object,
        appointmentDoctor: Object,
        structure: Object,
        pick: Array,
        record: Object,
        nextVisit: Object,
        diaryRecords: {
            type: Array,
            default: () => [],
        },
        diagnostics: {
            type: Array,
            default: () => [],
        },
        analyses: {
            type: Array,
            default: () => [],
        },
        consultations: {
            type: Array,
            default: () => [],
        },
        medicines: {
            type: Array,
            default: () => [],
        },
        physiotherapies: {
            type: Array,
            default: () => [],
        },
        procedures: {
            type: Array,
            default: () => [],
        },
        conditionRecords: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            analysisFields: this.getAnalysisFields(),
            diagnosticFields: this.getDiagnosticFields(),
            consultationFields: this.getConsultationFields(),
            medicineFields: this.getMedicineFields(),
            physiotherapyFields: this.getPhysiotherapyFields(),
            procedureFields: this.getProcedureFields(),
            analysisResultFields: this.getAnalysisResultFields(),
            analysesResults: this.getAnalysesResults(),
            nextVisits: this.getNextVisits(),
            nextVisitFields: this.getNextVisitFields(),
        }
    },
    methods: {
        isPicked(section) {
            return this.pick.indexOf(section) !== -1;
        },
        getAnalysesResults() {
            return this.appointment.analyses_results;
        },
        getNextVisits() {
            if (this.nextVisit !== null) {
                return [{
                    title: __('Рекомендована дата следующего визита'),
                    date: this.nextVisit.next_visit_date,
                }];
            }
            return [];
        },
        getNextVisitFields() {
            return [
                {
                    name: 'title',
                    title: __('Рекомендация'),
                },
                {
                    name: 'date',
                    title: __('Дата'),
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
            ];
        },
        getAnalysisFields() {
            return [
                {
                    name: 'created',
                    title: __('Дата'),
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value, 'DD.MM.YY');
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
                    title: __('Реком. дата сдачи'),
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value, 'DD.MM.YY');
                    },
                },
            ];
        },
        getAnalysisResultFields() {
            return [
                {
                    name: 'date_pass',
                    title: __('Дата сдачи'),
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value, 'DD.MM.YY');
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
                    formatter: (value) => {
                        return Number(value);
                    },
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
    }
}
</script>
