<template>
    <div :class="cssClass">
        <drawer :open="displayFilter">
            <section class="grey">
                <record-filter
                    ref="filter"
                    :filters="filters"
                    :patient-cards="patientCards"
                    @changed="changeFilters"
                    @cleared="clearFilters"
                />
            </section>
        </drawer>
        <section
            :class="bodyClass"
            v-loading="loading">
            <div v-for="(items, key) in recordList" :key="key" class="patient-history-records">
                <el-row :gutter="20">
                    <el-col :span="6" class="text-right record-date">
                        <h3>{{ key }}</h3>
                    </el-col>
                    <el-col :span="16">
                        <informations
                            :items="items"
                            @show-details="showDetails" />
                    </el-col>
                </el-row>
            </div>
        </section>
        <div :class="footerClass">
            <pagination
                ref="pagination"
                :show-table-settings="false"
                @pageSizeChanged="pageSizeChanged"
                @pageChanged="pageChanged" />
        </div>
    </div>
</template>
<script>
import CardRecordRepository from '@/repositories/patient/card/record';
import CardAssignment from '@/models/patient/card/assignment';
import DiaryRecord from '@/models/patient/card/diary-record';
import OutpatientRecord from '@/models/patient/card/outpatient-record';
import ProtocolRecord from '@/models/patient/card/protocol-record';
import TreatmentAssignment from '@/models/patient/card/treatment-assignment';
import ConsultationRecord from '@/models/patient/card/consultation-record';
import OutclinicProtocolRecord from '@/models/patient/card/outclinic-protocol-record';
import PatientDocument from '@/models/patient/card/document';
import Pagination from '@/components/general/Pagination.vue';
import Informations from './Informations.vue';
import RecordFilter from './Filter.vue';
import OutpatientRecordDetails from './OutpatientRecord.vue';
import AssignedAnalysis from '../assignment/analysis/Analysis.vue';
import AssignedProcedure from '../assignment/procedures/Procedure.vue';
import AssignedPhysiotherapy from '../assignment/physiotherapies/Physiotherapy.vue';
import AssignedDiagnostic from '../assignment/diagnostics/Diagnostic.vue';
import AssignedMedicine from '../assignment/medicines/Medicine.vue';
import SurgeryServices from '../assignment/surgery/Services.vue';
import SurgeryBaseServices from '../assignment/surgery/BaseServices.vue';
import DiaryEntries from '../diary/Entries.vue';
import ManipulationEntries from '../manipulation/Entries.vue';
import TreatmentCourseDetails from '../assignment/treatment-course/TreatmentCourse.vue';
import ConsultationDetails from '../consultation/Table.vue';
import NextVisitBlock from "../next-visit/NextVisitBlock";
import FileActionMixin from '@/mixins/file-action';
import CONSTANTS from '@/constants';
import PrintedDocument from "@/models/patient/card/printed-document";
import Echo from "./modals/Echo";
import ServiceRecordBlock from "../service-record/Entries";
import ConditionRecordBlock from "../condition-record/Entries";
import ServiceRecord from "@/models/patient/card/service-record";
import ConditionRecord from "@/models/patient/card/condition-record";
import FileViewerHeader from "@/components/general/FileViewerHeader";
import NextVisit from "@/models/patient/card/next-visit";
import ManipulationRecord from "@/models/patient/card/manipulation-record";

export default {
    mixins: [
        FileActionMixin,
    ],
    components: {
        Pagination,
        Informations,
        RecordFilter,
    },
    props: {
        patient: Object,
        cssClass: {
            type: String,
            default: 'sections-wrapper records-history',
        },
        bodyClass: {
            type: String,
            default: 'block-card-records',
        },
        footerClass: {
            type: String,
            default: 'dialog-footer',
        },
        displayFilter: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            loading: true,
            repository: new CardRecordRepository(),
            page: 1,
            perPage: 20,
            sort: [
                {
                    field: 'created',
                    direction: 'desc',
                },
            ],
            recordList: {},
            filters: {},
            scopes: [
                'specialization',
                'doctor',
                'appointment',
            ],
        }
    },
    computed: {
        patientCards() {
            return this.patient.cards.map(card => card.id);
        },
    },
    mounted() {
        this.filters = this.getRecordFilters();
        this.refresh();
    },
    methods: {
        loadData() {
            this.loading = true;
            this.repository.fetch(this.filters, this.sort, this.scopes, this.page, this.perPage).then((response) => {
                if (response.pagination && this.$refs.pagination) {
                    this.$refs.pagination.setPaginationData(response.pagination);
                }
                this.castRecords(response.rows);
                this.loading = false;
            });
        },
        castRecords(records) {
            records = records.map(record => {
                record.record_date = this.formatDate(record.date, 'YYYY-MM-DD');
                return record;
            });
            this.recordList = _.groupBy(records, 'record_date');
        },
        changeFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
            this.refresh();
        },
        clearFilters() {
            this.filters = this.getRecordFilters();
            this.refresh();
        },
        getRecordFilters() {
            let filters = _.onlyFilled({
                specialization_card: this.patientCards,
                patient: this.patient.id,
            });

            if (!this.$can('patient-cabinet.history-printed-documents')) {
                filters.not_type = ['patient_card_printed_document'];
            }

            return filters;
        },
        pageChanged(page) {
            if (page == 'prev') {
                this.page--;
            } else if (page == 'next') {
                this.page++;
            } else {
                this.page = page;
            }
            this.refresh();
        },
        pageSizeChanged(size) {
            this.perPage = size;
            this.page = 1;
            this.refresh();
        },
        refresh() {
            this.loadData();
        },
        formatDate(date) {
            return this.$formatter.dateFormat(date);
        },
        formatDateTime(date) {
            return this.$formatter.datetimeFormat(date);
        },
        showDetails(record) {
            if (record instanceof OutpatientRecord) {
                return this.showOutpatientRecord(record);
            } else if (record instanceof CardAssignment) {
                return this.showAssignment(record);
            } else if (record instanceof DiaryRecord) {
                return this.showDiaryRecord(record);
            } else if (record instanceof ServiceRecord) {
                return this.showServiceRecord(record);
            } else if (record instanceof ConditionRecord) {
                return this.showConditionRecord(record);
            } else if (record instanceof ProtocolRecord) {
                return this.showProtocolRecord(record);
            } else if (record instanceof TreatmentAssignment) {
                return this.showTreatmentCourse(record);
            } else if (record instanceof ConsultationRecord) {
                return this.showConsultations(record);
            } else if (record instanceof PatientDocument) {
                return this.showPatientDocument(record);
            }else if (record instanceof PrintedDocument) {
                if(record.file !== null){
                    return this.showFile(record);
                }
                return this.showPrintedDocument(record);
            }else if (record instanceof NextVisit) {
                return this.showNextVisit(record);
            } else if (record instanceof OutclinicProtocolRecord) {
                return this.showPatientDocument(record);
            } else if (record instanceof ManipulationRecord) {
                return this.showManipulationRecord(record)
            }
        },
        showManipulationRecord(record) {
            let header = __('Проведенные манипуляции {header}', {header: this.getModalHeader(record)});
            let data = {records: [record]};
            return this.details(data, ManipulationEntries, header);
        },
        showAssignment(record) {
            if (record.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS) {
                return this.showAnalyses(record);
            } else if (record.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PROCEDURES) {
                return this.showProcedures(record);
            } else if (record.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PHYSIOTHERAPIES) {
                return this.showPhysiotherapies(record);
            } else if (record.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS) {
                return this.showDiagnostics(record);
            } else if (record.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_MEDICINES) {
                return this.showMedicines(record);
            } else if (record.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES) {
                return this.showSurgery(record);
            } else if (record.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_SERVICES) {
                return this.showSurgeryServices(record);
            }
        },
        getModalHeader(record) {
            return `(${record.specialization_name}) - ${this.formatDateTime(record.date)}`;
        },
        showFile(record){
            return this.view(record.file.url, __('Просмотр распечатанного документа'));
        },
        showOutpatientRecord(record) {
            let header = __('Изменения в амбулаторной карте {header}', {header: this.getModalHeader(record)});
            return this.details({record}, OutpatientRecordDetails, header);
        },
        showDiaryRecord(record) {
            let header = __('Запись в дневнике {header}', {header: this.getModalHeader(record)});
            let data = {records: [record]};
            return this.details(data, DiaryEntries, header);
        },
        showServiceRecord(record) {
            let header = __('Служебная запись в дневнике {header}', {header: this.getModalHeader(record)});
            let data = {records: [record]};
            return this.details(data, ServiceRecordBlock, header);
        },
        showConditionRecord(record) {
            let header = __('Запись в дневнике {header}', {header: this.getModalHeader(record)});
            let data = {records: [record]};
            return this.details(data, ConditionRecordBlock, header);
        },
        showTreatmentCourse(record) {
            let header = __('Назначено лечение {header}', {header: this.getModalHeader(record)});
            let data = {
                services: record.recordable.services,
                readonly: true,
                title: false,
            };
            return this.details(data, TreatmentCourseDetails, header);
        },
        showAnalyses(record) {
            let header = __('Назначены анализы {header}', {header: this.getModalHeader(record)});
            let data = {
                analyses: record.recordable.analysis_results,
                readonly: true,
                title: false,
            };
            return this.details(data, AssignedAnalysis, header);
        },
        showProcedures(record) {
            let header = __('Назначены процедуры {header}', {header: this.getModalHeader(record)});
            let data = {
                procedures: record.recordable.assigned_procedures,
                readonly: true,
                title: false,
            };
            return this.details(data, AssignedProcedure, header);
        },
        showPhysiotherapies(record) {
            let header = __('Назначены физиопроцедуры {header}', {header: this.getModalHeader(record)});
            let data = {
                physiotherapies: record.recordable.assigned_physiotherapies,
                readonly: true,
                title: false,
            };
            return this.details(data, AssignedPhysiotherapy, header);
        },
        showDiagnostics(record) {
            let header = __('Назначена диагностика {header}', {header: this.getModalHeader(record)});
            let data = {
                diagnostics: [...record.recordable.assigned_diagnostics, ...record.recordable.outclinic_services],
                readonly: true,
                title: false,
            };
            return this.details(data, AssignedDiagnostic, header);
        },
        showMedicines(record) {
            let header = __('Назначены медикаменты {header}', {header: this.getModalHeader(record)});
            let data = {
                medicines: record.recordable.assigned_medicines,
                readonly: true,
                title: false,
            };
            return this.details(data, AssignedMedicine, header);
        },
        showNextVisit(record) {
            let header = __('Назначен след. визит {header}', {header: this.getModalHeader(record)});
            let data = {
                nextVisit: record,
                readonly: true,
                title: false,
            };
            return this.details(data, NextVisitBlock, header);
        },
        showConsultations(record) {
            let header = __('Назначены консультации врачей {header}', {header: this.getModalHeader(record)});
            let data = {
                consultations: record.consultations,
                readonly: true,
                title: false,
            };
            return this.details(data, ConsultationDetails, header);
        },
        showProtocolRecord(record) {
            return this.view(record.file_data.url);
        },
        showPatientDocument(record) {
            this.selectAndView(record.attachments_data);
        },
        showPrintedDocument(record){
            let header = __('Просмотр распечатанного заключения ({date})', {date: this.formatDateTime(record.date)});

            this.$modalComponent(Echo, {
                record: record
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header,
                width: '835px',
                customClass: 'no-footer',
                headerAddon: {
                    component: FileViewerHeader,
                    props:{
                        showDownload: false
                    },
                    eventListeners: {
                        print: (dialog) => {
                            dialog.getTopComponent().print();
                        },
                        downloadFile(dialog){
                            dialog.getTopComponent().download();
                        }
                    },
                }

            });
        },
        showSurgery(record) {
            let header = __('Назначена операция {header}', {header: this.getModalHeader(record)});
            let data = {
                services: record.recordable.surgery_base_services,
                readonly: true,
                title: false,
            };
            return this.details(data, SurgeryBaseServices, header);
        },
        showSurgeryServices(record) {
            let header = __('операционные мероприятия {header}', {header: this.getModalHeader(record)});
            let data = {
                services: record.recordable.surgery_services,
                readonly: true,
                title: false,
            };
            return this.details(data, SurgeryServices, header);
        },
        details(props, detailComponent, header) {
            this.$modalComponent(detailComponent, props, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header,
                width: '1020px',
                customClass: 'no-footer',
            });
        },
    },
}
</script>
