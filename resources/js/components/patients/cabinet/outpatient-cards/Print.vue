<template>
    <div class="card-record-section">
        <h2>{{ pageHeader }}</h2>
        <h3 v-if="appointment">{{ __('Дата визита') }}: {{ $formatter.dateFormat(appointment.date) }}</h3>
        <h3 v-if="appointment">{{ __('Врач') }}: {{ appointment.doctor_name }}</h3>
        <div class="card-record-line">
            <div class="card-record-field">
                <div class="prefix">{{ __('Жалобы') }}</div>
                <div class="border-bottom field-input">
                    {{ record.complaints }}
                </div>
            </div>
        </div>
        <fields-section 
            v-for="(section, index) in structure.getSections()"
            :key="index"
            :label="section.label"
            :fields="section.children" />
        <div class="card-record-line">
            <div class="card-record-field">
                <div class="prefix">{{ __('Диагноз по МКБ') }}</div>
                <div class="border-bottom field-input">
                    {{ $formatter.listFormat(record.diagnosis_icd_names) }}
                </div>
            </div>
        </div>
        <div class="card-record-line">
            <div class="card-record-field">
                <div class="prefix">{{ __('Диагноз') }}</div>
                <div class="border-bottom field-input">
                    {{ record.diagnosis }}
                </div>
            </div>
        </div>
        <div class="mt-10" v-if="diagnostics.length != 0">
            <div>
                <b>{{ __('Диагностическое обследование:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="diagnostics"
                    :fields="diagnosticFields" />
            </div>
        </div>
        <div class="mt-10" v-if="analysesResults.length != 0">
            <div>
                <b>{{ __('Забор анализов во время приема:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="analysesResults"
                    :fields="analysisResultFields" />
            </div>
        </div>
        <div class="mt-10" v-if="analyses.length != 0">
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
        <div class="mt-10" v-if="consultations.length != 0">
            <div>
                <b>{{ __('Направление на консультацию:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="consultations"
                    :fields="consultationFields" />
            </div>
        </div>
        <div class="mt-10" v-if="physiotherapies.length != 0">
            <div>
                <b>{{ __('Назначенная физиотерапия:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="physiotherapies"
                    :fields="physiotherapyFields" />
            </div>
        </div>
        <div class="mt-10" v-if="procedures.length != 0">
            <div>
                <b>{{ __('Назначенные процедуры:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="procedures"
                    :fields="procedureFields" />
            </div>
        </div>
        <div class="mt-10" v-if="medicines.length != 0">
            <div>
                <b>{{ __('Назначенные медикаменты:') }}</b>
            </div>
            <div class="mt-10">
                <printable-table
                    :list="medicines"
                    :fields="medicineFields" />
            </div>
        </div>
        <div class="mt-10" v-if="diaryRecords.length != 0">
            <div>
                <b>{{ __('Комментарий врача:') }}</b>
            </div>
            <div class="mt-10">
                {{ $formatter.listFormat(diaryRecords, 'comment') }}
            </div>
        </div>
        <div class="mt-10" v-if="nextVisits.length != 0">
            <div>
                <b>{{ __('Дата следующего визита:') }}</b>
                {{ $formatter.listFormat(nextVisits.map((v) => $formatter.dateFormat(v.date))) }}
            </div>
        </div>
    </div>
</template>
<script>
import FieldsSection from './print/Section.vue';
import PrintableTable from '@/components/doctor/appointment/assignment/PrintableTable.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        FieldsSection,
        PrintableTable,
    },
    props: {
        structure: Object,
        record: {
            type: Object,
            default: () => ({}),
        },
        appointment: {
            type: Object,
        },
        records: {
            type: Array,
        },
        pageHeader: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            diagnostics: this.getDiagnostics(),
            analysesResults: this.getAnalysesResults(),
            analyses: this.getAnalyses(),
            consultations: this.getConsultations(),
            physiotherapies: this.getPhysiotherapies(),
            procedures: this.getProcedures(),
            medicines: this.getMedicines(),
            diaryRecords: this.getDiaryRecords(),
            nextVisits: this.getNextVisits(),
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
        };
    },
    methods: {
        getDiagnostics() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS, 'assigned_diagnostics');
        },
        getAnalysesResults() {
            return [];
        },
        getAnalyses() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS, 'analysis_results');
        },
        getConsultations() {
            return this.getRecords(CONSTANTS.CARD_RECORD.TYPE.CONSULTATION_RECORD).reduce((acc, current) => {
                return acc.concat(current.consultations);
            }, []);
        },
        getPhysiotherapies() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PHYSIOTHERAPIES, 'assigned_physiotherapies');
        },
        getProcedures() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PROCEDURES, 'assigned_procedures');
        },
        getMedicines() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_MEDICINES, 'assigned_medicines');
        },
        getDiaryRecords() {
            return this.getRecords(CONSTANTS.CARD_RECORD.TYPE.DIARY_RECORD).reverse();
        },
        getNextVisits() {
            return this.getRecords(CONSTANTS.CARD_RECORD.TYPE.NEXT_VISIT);
        },
        getRecords(type) {
            if (!this.records) {
                return [];
            }
            return this.records.filter((record) => record.type === type);
        },
        getAssignmentRecords(type, key) {
            if (!this.records) {
                return [];
            }
            return this.records.filter((record) => {
                return record.type === CONSTANTS.CARD_RECORD.TYPE.CARD_ASSIGNMENT
                    && record.recordable.type === type;
            }).reduce((items, value) => {
                return items.concat(_.toArray(value.recordable[key]))
            }, []);
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
    },
}
</script>