<template>
    <div>
        <div class="paragraph">
            <condition-entries
                :records="conditionRecords"
                :readonly="true" />
        </div>
        <div class="paragraph">
            <diary-entries
                :records="diaryRecords"
                :readonly="true" />
        </div>
        <div class="paragraph">
            <service-entries
                :records="serviceRecords"
                :readonly="true" />
        </div>
        <div class="paragraph">
            <protocols-table
                :protocols="protocols"
                :readonly="true" />
            <analyses-table
                :analyses="analyses"
                :readonly="true" />
            <medicine-table
                :medicines="medicines"
                :readonly="true" />
            <procedure-table
                :procedures="procedures"
                :readonly="true" />
            <physiotherapy-table
                :physiotherapies="physiotherapies"
                :readonly="true" />
            <diagnostic-table
                :diagnostics="diagnostics"
                :readonly="true" />
            <surgery-base-service-table
                :services="surgeryBaseServices"
                :readonly="true" />
            <surgery-service-table
                :services="surgeryServices"
                :readonly="true" />
        </div>
        <documents-table
            :records="patientDocuments"
            :readonly="true" />
        <research-table
            :records="patientResearch"
            :readonly="true" />
    </div>
</template>

<script>
import DiaryEntries from '../diary/Entries.vue';
import ServiceEntries from '../service-record/Entries.vue';
import ConditionEntries from '../condition-record/Entries.vue';
import ProtocolsTable from '../protocol/Table.vue';
import AnalysesTable from '../assignment/analysis/Analysis.vue';
import MedicineTable from '../assignment/medicines/Medicine.vue';
import ProcedureTable from '../assignment/procedures/Procedure.vue';
import PhysiotherapyTable from '../assignment/physiotherapies/Physiotherapy.vue';
import DiagnosticTable from '../assignment/diagnostics/Diagnostic.vue';
import SurgeryServiceTable from '../assignment/surgery/Services.vue';
import SurgeryBaseServiceTable from '../assignment/surgery/BaseServices.vue';
import DocumentsTable from '../PatientDocument.vue';
import ResearchTable from '../PatientProtocols.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        DiaryEntries,
        ServiceEntries,
        ConditionEntries,
        ProtocolsTable,
        AnalysesTable,
        MedicineTable,
        ProcedureTable,
        PhysiotherapyTable,
        DiagnosticTable,
        DocumentsTable,
        ResearchTable,
        SurgeryServiceTable,
        SurgeryBaseServiceTable,
    },
    props: {
        records: Array,
    },
    computed: {
        diaryRecords() {
            return this.getRecords(CONSTANTS.CARD_RECORD.TYPE.DIARY_RECORD).reverse();
        },
        serviceRecords() {
            return this.getRecords(CONSTANTS.CARD_RECORD.TYPE.SERVICE_RECORD).reverse();
        },
        conditionRecords() {
            return this.getRecords(CONSTANTS.CARD_RECORD.TYPE.CONDITION_RECORD).reverse();
        },
        protocols() {
            return this.getRecords(CONSTANTS.CARD_RECORD.TYPE.PROTOCOL_RECORD);
        },
        analyses() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS, 'analysis_results');
        },
        medicines() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_MEDICINES, 'assigned_medicines');
        },
        procedures() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PROCEDURES, 'assigned_procedures');
        },
        physiotherapies() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PHYSIOTHERAPIES, 'assigned_physiotherapies');
        },
        diagnostics() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS, 'assigned_diagnostics');
        },
        patientDocuments() {
            return this.getRecords(CONSTANTS.CARD_RECORD.TYPE.PATIENT_DOCUMENT);
        },
        patientResearch() {
            return this.getRecords(CONSTANTS.CARD_RECORD.TYPE.OUTCLINIC_PROTOCOL_RECORD);
        },
        surgeryServices() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_SERVICES, 'surgery_services');
        },
        surgeryBaseServices() {
            return this.getAssignmentRecords(CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES, 'surgery_base_services');
        },
    },
    methods: {
        getRecords(type) {
            return this.records.filter((record) => record.type === type);
        },
        getAssignmentRecords(type, key) {
            return this.records.filter((record) => {
                return record.type === CONSTANTS.CARD_RECORD.TYPE.CARD_ASSIGNMENT
                    && record.recordable.type === type;
            }).reduce((items, value) => {
                return items.concat(_.toArray(value.recordable[key]))
            }, []);
        },
    },
}
</script>
