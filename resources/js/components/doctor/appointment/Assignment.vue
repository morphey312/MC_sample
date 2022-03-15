<template>
    <div class="assignments-block">
        <treatment-block
            :services="appointment.services"
            :readonly="readonly"
            @edit="emitCallMenuCallback('assignCourse')"
            @remove-treatment="removeTreatmentService" />
        <doctor-service-block
            :services="appointment.services"
            :readonly="readonly"
            @edit="emitCallMenuCallback('makeServices')"
            @remove-doctor-service="removeDoctorService" />
        <doctor-analysis-block
            :appointment="appointment"
            :readonly="readonly"
            @edit="emitCallMenuCallback('makeAnalyses')"
            @remove-doctor-analysis="removeDoctorAnalysis" />
        <analysis-block
            :analyses="analysisList"
            :readonly="readonly"
            @edit="emitCallMenuCallback('assignAnalysis')"
            @remove-analysis="removeAnalysis" />
        <medicine-block
            :medicines="medicineList"
            :readonly="readonly"
            @edit="emitCallMenuCallback('assignMedicine')"
            @remove-medicine="removeMedicine" />
        <procedure-block
            :procedures="procedureList"
            :readonly="readonly"
            @edit="emitCallMenuCallback('assignProcedure')"
            @remove-procedure="removeProcedure" />
        <surgery-base-service-block
            :services="surgeryBaseServices"
            :readonly="readonly"
            @edit="emitCallMenuCallback('assignSurgery')"
            @remove-service="removeSurgeryBaseService" />
        <surgery-service-block
            :services="surgeryServices"
            :readonly="readonly"
            @edit="emitCallMenuCallback('assignSurgeryServices')"
            @remove-service="removeSurgeryService" />
        <physiotherapy-block
            :physiotherapies="physiotherapyList"
            :readonly="readonly"
            @edit="emitCallMenuCallback('assignPhysiotherapy')"
            @remove-physiotherapy="removePhysiotherapy" />
        <diagnostic-block
            :diagnostics="diagnosticsList"
            :outclinic-diagnostics="outclinicDiagnostics"
            :readonly="readonly"
            @edit="emitCallMenuCallback('assignDiagnostic')"
            @remove-diagnostic="removeDiagnostic"
            @remove-outclinic-diagnostic="removeOutclinicDiagnostic" />
        <consultation-block
            :consultations="consultations"
            :readonly="readonly"
            @remove-selection="removeConsultation" />
        <next-visit-block
            :next-visit="nextVisit"
            :readonly="readonly"
            @edit="emitCallMenuCallback('setNextVisit')"
            @next-visit-removed="nextVisitRemoved" />
        <div class="mt-20 text-right">
            <el-button
                @click="printAdvisory">
                {{ __('Печать консультативного заключения') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import AnalysisBlock from './assignment/analysis/Analysis.vue';
import MedicineBlock from './assignment/medicines/Medicine.vue';
import ProcedureBlock from './assignment/procedures/Procedure.vue';
import PhysiotherapyBlock from './assignment/physiotherapies/Physiotherapy.vue';
import TreatmentBlock from './assignment/treatment-course/TreatmentCourse.vue';
import DiagnosticBlock from './assignment/diagnostics/Diagnostic.vue';
import DoctorServiceBlock from './doctor-service/service/DoctorService.vue';
import DoctorAnalysisBlock from './doctor-service/analysis/DoctorAnalysis.vue';
import SurgeryBaseServiceBlock from './assignment/surgery/BaseServices.vue';
import SurgeryServiceBlock from './assignment/surgery/Services.vue';
import ConsultationBlock from './consultation/Table.vue';
import NextVisitBlock from '@/components/doctor/appointment/next-visit/NextVisitBlock';
import CardAssignment from '@/models/patient/card/assignment';
import CONSTANTS from '@/constants';
import CardAssignmentMixin from './mixins/assignment';

export default {
    mixins: [
        CardAssignmentMixin,
    ],
    components: {
        AnalysisBlock,
        MedicineBlock,
        ProcedureBlock,
        PhysiotherapyBlock,
        TreatmentBlock,
        DiagnosticBlock,
        DoctorServiceBlock,
        DoctorAnalysisBlock,
        ConsultationBlock,
        NextVisitBlock,
        SurgeryBaseServiceBlock,
        SurgeryServiceBlock,
    },
    props: {
        appointment: Object,
        activeCard: Object,
        cardAssigments: {
            type: Array,
            default: () => [],
        },
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
        surgeryBaseServices: {
            type: Array,
            default: () => [],
        },
        surgeryServices: {
            type: Array,
            default: () => [],
        },
        outclinicDiagnostics: {
            type: Array,
            default: () => [],
        },
        treatmentCourse: {
            type: Object,
            default: () => ({}),
        },
        nextVisit: {
            type: Object,
            default: () => ({}),
        },
        consultations: {
            type: Array,
            default: () => [],
        },
        readonly: Boolean,
        isSurgery: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        getAssignerId() {
            return this.isSurgery === true ? this.$store.state.user.employee_id : this.appointment.doctor_id;
        },
        emitCallMenuCallback(callback) {
            this.$emit('call-menu-callback', callback);
        },
        getAssignment(type, card) {
            let data = {
                appointment_id: this.appointment.id,
                card_specialization_id: card.id,
                type,
            };
            let assignment = this.findAssignment(type);

            if (_.isFilled(assignment)) {
                data.id = assignment.id;
            }
            return new CardAssignment(data);
        },
        persistAssignment(type, assignments, card) {
            let assignment = this.getAssignment(type, card);
            assignment.set(type, assignments);
            return this.saveAssignment(assignment);
        },
        saveAssignment(assignment) {
            return assignment.save().then((response) => {
                let cardAssignment = new CardAssignment(response.response.data);
                this.$emit('assignments-changed', cardAssignment);
                this.$info(__('Назначение успешно сохранено'));
            });
        },
        analysesSelected(list) {
            if (!this.activeCard) {
                return this.$warning(__('У пациента нет карты'));
            }

            if (list.length === 0) {
                return;
            }

            let assignments = list.map((row) => {
                return {
                    id: row.id,
                    patient_id: this.appointment.patient_id,
                    card_specialization_id: this.activeCard.specialization_id,
                    clinic_id: this.appointment.clinic_id,
                    assigner_id: row.assigner_id || this.getAssignerId(),
                    date_expected_pass: row.date_expected_pass,
                    analysis_id: row.analysis_id,
                    cost: row.cost,
                    price_id: row.price_id,
                    quantity: row.quantity,
                    discount: row.discount,
                    by_policy: row.by_policy,
                    franchise: row.franchise,
                    warranter: row.warranter,
                };
            });
            this.persistAssignment(CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS, assignments, this.activeCard);
        },
        medicinesSelected(list) {
            if (!this.activeCard) {
                return this.$warning(__('У пациента нет карты'));
            }

            if (list.length === 0) {
                return;
            }

            let assignments = list.map((row) => {
                return {
                    id: row.id,
                    patient_id: this.appointment.patient_id,
                    card_specialization_id: this.activeCard.specialization_id,
                    clinic_id: this.appointment.clinic_id,
                    assigner_id: row.assigner_id || this.getAssignerId(),
                    appointment_id: this.appointment.id,
                    medicine_id: row.medicine_id,
                    medicine_type: row.medicine_type,
                    cost: isNaN(row.cost) ? null : row.cost,
                    base_cost: isNaN(row.base_cost) ? 0 : row.base_cost,
                    self_cost: isNaN(row.self_cost) ? 0 : row.self_cost,
                    quantity: row.quantity,
                    medication_duration: row.medication_duration,
                    comment: row.comment,
                    is_apteka24: row.is_apteka24,
                    apteka24_id: row.apteka24_id,
                    is_free: row.is_free,
                    by_policy: row.by_policy,
                    franchise: row.franchise,
                    warranter: row.warranter,
                };
            });



            this.persistAssignment(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_MEDICINES, assignments, this.activeCard);
        },
        proceduresSelected(list) {
            return this.saveService(list, CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PROCEDURES);
        },
        physiotherapiesSelected(list) {
            return this.saveService(list, CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PHYSIOTHERAPIES);
        },
        surgeryBaseServicesSelected(list) {
            return this.saveService(list, CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES)
                .then(() => {
                    if (_.isVoid(this.appointment.treatment_course_id) && this.activeCard) {
                        this.$emit('create-surgery-course');
                    }
                });
        },
        surgeryServicesSelected(list) {
            return this.saveService(list, CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_SERVICES);
        },
        diagnosticsSelected({diagnostics, outclinicDiagnostics}) {
            if (!this.activeCard) {
                this.$warning(__('У пациента нет карты'));
                return Promise.reject(__('У пациента нет карты'));
            }

            let assignment = this.getAssignment(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS, this.activeCard);
            let diagnosticList = this.getServicesData(diagnostics);
            assignment.set(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS, diagnosticList);
            assignment.set('additional_type', CONSTANTS.CARD_ASSIGNMENT.TYPES.OUTCLINIC_SERVICES);
            assignment.set(CONSTANTS.CARD_ASSIGNMENT.TYPES.OUTCLINIC_SERVICES, outclinicDiagnostics);
            return this.saveAssignment(assignment);
        },
        saveService(list, type, onSave = null) {
            if (!this.activeCard) {
                return this.$warning(__('У пациента нет карты'));
            }

            if (list.length === 0) {
                return;
            }

            let assignments = this.getServicesData(list);
            return this.persistAssignment(type, assignments, this.activeCard);
        },
        getServicesData(list) {
            return list.map((row) => {
                return {
                    id: row.id,
                    service_id: row.service_id,
                    price_id: row.price_id,
                    quantity: row.quantity,
                    patient_id: this.appointment.patient_id,
                    assigner_id: row.assigner_id || this.getAssignerId(),
                    clinic_id: this.appointment.clinic_id,
                    assigned_quantity: row.quantity,
                    cost: row.cost,
                    is_free: row.is_free,
                    comment: row.comment,
                    discount: row.discount,
                    by_policy: row.by_policy,
                    franchise: row.franchise,
                    warranter: row.warranter,
                };
            });
        },
        removeAnalysis(index) {
            let model = this.analysisList[index];
            return this.removeAssignmentItem(model, index, CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS)
                .then(() => {
                    this.$info(__('Анализ успешно удален'));
                }).catch((error) => {
                    this.$error(__('Не удалось удалить назначеный анализ'));
                });
        },
        removeMedicine(index) {
            let model = this.medicineList[index];
            return this.removeAssignmentItem(model, index, CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_MEDICINES)
                .then(() => {
                    this.$info(__('Медикамент успешно удален'));
                }).catch((error) => {
                    this.$error(__('Не удалось удалить назначеный медикамент'));
                });
        },
        removeProcedure(index) {
            let model = this.procedureList[index];
            return this.removeAssignmentItem(model, index, CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PROCEDURES)
                .then(() => {
                    this.$info(__('Процедура успешно удалена'));
                }).catch((error) => {
                    this.$error(__('Не удалось удалить назначенную процедуру'));
                });
        },
        removePhysiotherapy(index) {
            let model = this.physiotherapyList[index];
            return this.removeAssignmentItem(model, index, CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PHYSIOTHERAPIES)
                .then(() => {
                    this.$info(__('Физиотерапия успешно удалена'));
                }).catch((error) => {
                    this.$error(__('Не удалось удалить назначенную физиотерапию'));
                });
        },
        removeDiagnostic(index) {
            let model = this.diagnosticsList[index];
            return this.removeAssignmentItem(model, index, CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS)
                .then(() => {
                    this.$info(__('Аппаратная диагностика успешно удалена'));
                }).catch((error) => {
                    this.$error(__('Не удалось удалить назначенную диагностику'));
                });
        },
        removeSurgeryBaseService(index) {
            let model = this.surgeryBaseServices[index];
            return this.removeAssignmentItem(model, index, CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES)
                .then(() => {
                    this.$info(__('Операция успешно удалена'));
                }).catch((error) => {
                    this.$error(__('Не удалось удалить назначенную операцию'));
                });
        },
        removeSurgeryService(index) {
            let model = this.surgeryServices[index];
            return this.removeAssignmentItem(model, index, CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_SERVICES)
                .then(() => {
                    this.$info(__('Услуга успешно удалена'));
                }).catch((error) => {
                    this.$error(__('Не удалось удалить назначенную услугу'));
                });
        },
        removeOutclinicDiagnostic(index) {
            let assignment = this.findAssignment(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS);
            let listName = CONSTANTS.CARD_ASSIGNMENT.TYPES.OUTCLINIC_SERVICES;
            if (assignment != undefined && assignment.recordable && assignment.recordable[listName]) {
                assignment.recordable[listName].splice(index, 1);
                this.diagnosticsSelected({
                    diagnostics: assignment.recordable[CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS],
                    outclinicDiagnostics: assignment.recordable[listName],
                })
            }
        },
        removeAssignmentItem(model, index, type) {
            if (model !== undefined) {
                return model.delete().then((response) => {
                    this.$emit('assignment-list-changed', {index, type});
                    return Promise.resolve();
                }).catch((error) => {
                    return Promise.reject();
                });
            }
            return Promise.reject();
        },
        removeTreatmentService(service) {
            this.$emit('remove-treatment-service', service);
        },
        removeDoctorService(service) {
            this.$emit('remove-doctor-service', service);
        },
        removeDoctorAnalysis(analysis) {
            this.$emit('remove-doctor-analysis', analysis);
        },
        removeConsultation(index) {
            this.$emit('remove-consultation', index);
        },
        nextVisitRemoved() {
            this.$emit('next-visit-removed');
        },
        printAdvisory() {
            this.$emit('print-advisory');
        },
    }
}
</script>
