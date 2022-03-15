<template>
    <div
        v-loading="loading"
        class="doctor-appointment">
        <template v-if="appointment">
            <appointment-menu
                ref="appointmentMenu"
                :appointment="appointment"
                :active-card="activeCard"
                :analyses="analysisList"
                :medicines="medicineList"
                :procedures="procedureList"
                :physiotherapies="physiotherapyList"
                :diagnostics="diagnosticsList"
                :surgery-base-services="surgeryBaseServices"
                :surgery-services="surgeryServices"
                :outclinic-diagnostics="outclinicDiagnostics"
                :protocols="protocols"
                :consultations="consultations"
                :next-visit="nextVisit"
                :readonly="readonly"
                :doctor-specializations="doctorSpecializations"
                :insurance-policy="insurancePolicy"
                :clinic-requisites="clinicRequisites"
                :is-surgery="isSurgery"
                :outpatientRecord="outpatientRecord"
                @added-assignment="addedAssignment"
                @assignment-list-changed="assignmentListChanged"
                @course-signed="courseSigned"
                @next-visit-created="nextVisitCreated"
                @service-list-changed="servicesChanged"
                @analysis-list-changed="analysesChanged"
                @remove-treatment-service="removeTreatmentService"
                @remove-doctor-service="removeDoctorService"
                @remove-doctor-analysis="removeDoctorAnalysis"
                @protocol-added="protocolAdded"
                @consultation-signed="consultationRecordAdded"
                @document-added="documentAdded"
                @research-added="researchAdded"
            />
            <page
                :title="__('Пациент: {name}', {name: appointment.patient.full_name})">
                <template slot="header-addon">
                    <div class="buttons">
                        <a
                            v-if="isOnlineConsultation && $canCreate('video-chat')"
                            href="#"
                            @click.prevent="showVideoChat">
                            <svg-icon
                                name="play-alt"
                                class="icon-small icon-blue">
                                {{ __('Видеоконсультация') }}
                            </svg-icon>
                        </a>
                        <a
                            v-if="signalRecord"
                            href="#"
                            @click.prevent="showSignalRecord">
                            <svg-icon
                                name="report-alt"
                                class="icon-small icon-blue">
                                {{ __('Сигнальные обозначения') }}
                            </svg-icon>
                        </a>
                    </div>
                    <section class="grey details">
                        <el-row :gutter="20">
                            <el-col :span="6">
                                <span class="patient-info">
                                    <template v-if="previousVisit">
                                        <span>{{ __('Последний визит:') }}</span>
                                        {{ $formatter.dateFormat(previousVisit.appointment.date) }}
                                    </template>
                                    <template v-else>
                                        <span>{{ (appointment.is_first == 1) ? __('Первичный визит') : __('Повторный визит') }}</span>
                                    </template>
                                </span>
                            </el-col>
                            <el-col :span="6">
                                <span class="patient-info">
                                    <span>{{ __('Номер карты:') }}</span>
                                    {{ appointment.patient_card ? appointment.patient_card.number : __('Нет карты') }}
                                </span>
                            </el-col>
                            <el-col :span="6">
                                <span class="patient-info">
                                    <span>{{ __('Специализация:') }}</span>
                                    {{ appointment.specialization_card ? appointment.specialization_card.specialization.name : __('Нет данных') }}
                                </span>
                            </el-col>
                            <el-col :span="6">
                                <span class="patient-info">
                                    <span>{{ __('Дата рождения:') }}</span>
                                    {{ $formatter.dateFormat(appointment.patient.birthday) || __('Нет данных') }}
                                </span>
                            </el-col>
                        </el-row>
                    </section>
                </template>
                <template v-if="!loading">
                    <div style="width: 100%;">
                        <el-row :gutter="20" v-if="patientServiceDebt > 0">
                            <el-col :span="24">
                                <alert type="skk">
                                    {{ __('У пациента долг:') }}
                                    {{ $formatter.numberFormat(patientServiceDebt) }} {{ __('грн.') }}
                                </alert>
                            </el-col>
                        </el-row>
                        <el-row :gutter="20" v-if="appointment.insurance_policy_id != null">
                            <el-col :span="24">
                                <alert type="conference" icon="info-alt">
                                    {{ __('Пациент застрахован') }}
                                    {{ policyInfo }}
                                </alert>
                            </el-col>
                        </el-row>
                    </div>
                    <section v-if="activeCard !== null">
                        <outpatient-record-block
                            ref="outpatientRecord"
                            v-if="outpatientRecord !== null"
                            :model="outpatientRecord"
                            :template="cardTemplate"
                            :template-addons="templateAddons"
                            :appointment="appointment"
                            :is-complete-dialog="isCompleteDialog"
                            @clear-outpatient-card="clearOutpatientCard"
                            @data-saved="outpatientRecordSaved"
                            @data-changed="outpatientRecordChanged" />
                        <section-wrapper
                            :label="__('Дневник')">
                            <div class="paragraph">
                                <treatment-block
                                    v-if="treatmentCourse"
                                    :treatment-course="treatmentCourse"
                                    :appointment="appointment"
                                    :patient="appointment.patient"/>
                            </div>
                            <div v-if="isSurgery" class="paragraph">
                                <condition-block
                                    :appointment="appointment"
                                    :active-card="activeCard"
                                    :records="conditionRecords"
                                    :readonly="readonly"
                                    @add-diary-record="addConditionRecord"
                                />
                            </div>
                            <div class="paragraph">
                                <diary-block
                                    :appointment="appointment"
                                    :active-card="activeCard"
                                    :records="diaryRecords"
                                    :readonly="readonly"
                                    @add-diary-record="addDiaryRecord"
                                />
                            </div>
                            <div class="paragraph">
                                <manipulation-block
                                    :appointment="appointment"
                                    :active-card="activeCard"
                                    :records="manipulationRecords"
                                    :readonly="readonly"
                                    @add-diary-record="addManipulationRecord"
                                />
                            </div>
                            <div class="paragraph">
                                <service-block
                                    :appointment="appointment"
                                    :active-card="activeCard"
                                    :records="serviceRecords"
                                    :readonly="readonly"
                                    @add-diary-record="addServiceRecord"
                                />
                            </div>
                            <protocols-block
                                :protocols="protocols"/>
                            <div class="paragraph">
                                <assignment-block
                                    ref="assignments"
                                    :appointment="appointment"
                                    :active-card="activeCard"
                                    :card-assigments="cardAssigments"
                                    :analysis-list="analysisList"
                                    :medicine-list="medicineList"
                                    :procedure-list="procedureList"
                                    :physiotherapy-list="physiotherapyList"
                                    :diagnostics-list="diagnosticsList"
                                    :surgery-base-services="surgeryBaseServices"
                                    :surgery-services="surgeryServices"
                                    :outclinic-diagnostics="outclinicDiagnostics"
                                    :consultations="consultations"
                                    :next-visit="nextVisit"
                                    :readonly="readonly"
                                    :is-surgery="isSurgery"
                                    @next-visit-removed="nextVisitDeleted"
                                    @assignments-changed="assignmentsChanged"
                                    @assignment-list-changed="assignmentListChanged"
                                    @remove-treatment-service="removeTreatmentService"
                                    @remove-doctor-service="removeDoctorService"
                                    @remove-doctor-analysis="removeDoctorAnalysis"
                                    @remove-consultation="removeConsultation"
                                    @call-menu-callback="callMenuCallback"
                                    @print-advisory="printAdvisory"
                                    @create-surgery-course="createSurgeryCourse" />
                            </div>
                            <patient-document-block
                                :records="patientDocuments"
                                @document-deleted="documentDeleted"/>
                            <patient-research-block
                                :records="patientResearch"
                                @research-result-deleted="researchDeleted"/>
                        </section-wrapper>
                        <button-end-reception
                            :previousVisit="previousVisit"
                            :treatment-assignment="treatmentAssignment"
                            :outpatient-record="outpatientRecord"
                            :appointment="appointment"
                            :card-assignments="cardAssigments"
                            :treatment-course="treatmentCourse"/>
                        <section-wrapper
                            v-if="previousVisit !== null"
                            :label="__('Предыдущий визит')">
                            <previous-visit-block
                                :data="previousVisit"
                                :appointment="appointment"/>
                        </section-wrapper>
                    </section>
                    <section v-else>
                        <no-data-placeholder :message="__('У пациента нет карты!')"/>
                    </section>
                </template>
            </page>
            <vue-draggable-resizable v-if="displayVideoChat" :h="586" :parent="true" :resizable="false" :w="727"
                                     :x="100" :y="100" :z="999" style="border: none;" @dragging="onDrag" className="video-chat-drag-container vdr">
                <video-chat
                    :appointment="appointment"
                    :camera-preview="cameraPreview"
                    @cameraPreview="cameraPreviewChangedEvent"
                    @hidden="hideVideoChat"/>
            </vue-draggable-resizable>

            <webcam-preview
                v-if="cameraPreview">
            </webcam-preview>
        </template>
    </div>
</template>

<script>
import AppointmentMenu from './appointment/Menu.vue';
import SectionWrapper from './appointment/SectionWrapper.vue';
import Appointment from '@/models/appointment';
import SignalRecordRepository from '@/repositories/patient/signal-record';
import RecordTemplateRepository from '@/repositories/patient/card/record-template';
import CardRecordRepository from '@/repositories/patient/card/record';
import SpecializationRepository from '@/repositories/specialization';
import AppointmentServiceRepository from '@/repositories/appointment/service';
import SignalRecord from '@/models/patient/signal-record';
import SignalRecordModal from './signal-record/Modal.vue';
import OutpatientRecordBlock from './appointment/OutpatientRecord.vue'
import DiaryBlock from './appointment/Diary.vue';
import ServiceBlock from './appointment/ServiceRecord.vue';
import ConditionBlock from './appointment/ConditionRecord.vue';
import ServiceRecord from "@/models/patient/card/service-record";
import ConditionRecord from "@/models/patient/card/condition-record";
import CompleteAppointment from './appointment/Complete.vue';
import OutpatientRecord from '@/models/patient/card/outpatient-record';
import DiaryRecord from '@/models/patient/card/diary-record';
import ProtocolRecord from '@/models/patient/card/protocol-record';
import TreatmentCourse from '@/models/treatment-course';
import TreatmentAssignment from '@/models/patient/card/treatment-assignment';
import ConsultationRecord from '@/models/patient/card/consultation-record';
import AssignmentBlock from './appointment/Assignment.vue';
import CardAssignment from '@/models/patient/card/assignment';
import PatientDocument from '@/models/patient/card/document';
import ProtocolsBlock from './appointment/protocol/Table.vue';
import PreviousVisitBlock from './appointment/PreviousVisit.vue';
import PatientDocumentBlock from './appointment/PatientDocument.vue';
import PatientResearchBlock from './appointment/PatientProtocols.vue';
import CardAssignmentMixin from './appointment/mixins/assignment';
import AppointmentMixin from './appointment/mixins/appointment';
import CONSTANTS from '@/constants';
import ButtonEndReception from './appointment/ButtonEndReception.vue';
import PrintAdvisoryOptions from './appointment/outpatient-record/PrintOptions.vue';
import SelectCardTemplate from './appointment/SelectCardTemplate.vue';
import NextVisit from "@/models/patient/card/next-visit";
import TreatmentBlock from './appointment/TreatmentCourse.vue';
import VideoChat from './appointment/online/VideoChat.vue';
import WebcamPreview from './appointment/online/WebcamPreview.vue';
import OutclinicProtocolRecord from '@/models/patient/card/outclinic-protocol-record';
import ManipulationBlock from './appointment/Manipulation.vue';
import ManipulationRecord from "../../models/patient/card/manipulation-record";
import VueDraggableResizable from 'vue-draggable-resizable'
import 'vue-draggable-resizable/dist/VueDraggableResizable.css'

export default {
    mixins: [
        CardAssignmentMixin,
        AppointmentMixin,
    ],
    components: {
        ServiceBlock,
        ConditionBlock,
        AppointmentMenu,
        OutpatientRecordBlock,
        DiaryBlock,
        AssignmentBlock,
        ProtocolsBlock,
        PreviousVisitBlock,
        SectionWrapper,
        ButtonEndReception,
        PatientDocumentBlock,
        TreatmentBlock,
        PatientResearchBlock,
        VideoChat,
        WebcamPreview,
        ManipulationBlock,
        VueDraggableResizable,
    },
    data() {
        return {
            appointment: null,
            signalRecord: null,
            cardTemplate: null,
            outpatientRecord: null,
            outpatientRecordUnsaved: false,
            isCompleteDialog: false,
            loading: true,
            diaryRecords: [],
            serviceRecords: [],
            conditionRecords: [],
            manipulationRecords: [],
            patientDocuments: [],
            patientResearch: [],
            cardAssigments: [],
            protocols: [],
            treatmentCourse: null,
            treatmentAssignment: null,
            previousVisit: null,
            activeCard: null,
            consultationRecord: null,
            nextVisit: null,
            doctorSpecializations: [],
            appointmentDoctor: null,
            patientServiceDebt: 0,
            insurancePolicy: null,
            clinicRequisites: null,
            isSurgery: false,
            isEmergency: false,
            templateAddons: [],
            displayVideoChat: false,
            cameraPreview: false,
            width: 0,
            height: 0,
            x: 0,
            y: 0
        };
    },
    mounted() {
        this.load();
        this.$safeClose(__('Вы уверены, что хотите закрыть эту страницу?'));
        this.$eventHub.$on('broadcast.appointment_updated', this.listenAppointmentUpdated);
    },
    beforeRouteLeave(to, from, next) {
        let needUpdateStatus = this.isIncompleted(this.appointment);
        if (this.outpatientRecordUnsaved || needUpdateStatus) {
            this.isCompleteDialog = true;
            this.$modalComponent(CompleteAppointment, {
                appointment: this.appointment,
                outpatientRecord: this.outpatientRecord,
                outpatientRecordUnsaved: this.outpatientRecordUnsaved,
                needUpdateStatus,
                next,
            }, {
                close: (dialog, next) => {
                    dialog.close();
                    next();
                },
            }, {
                header: __('Завершение приема'),
                width: '550px',
                onClosed: () => {
                    this.isCompleteDialog = false;
                },
            });
        } else {
            next();
        }
    },
    beforeDestroy() {
        this.$unsafeClose();
        this.$eventHub.$off('broadcast.appointment_updated');
    },
    created() {
        this.listenAppointmentUpdated = ({data}) => {
            if (data.id === this.appointment.id) {
                this.updateServicesAndAssigments();
            }
        }
    },
    computed: {
        readonly() {
            if (_.isFilled(this.appointment.date)) {
                return this.$moment().isSame(this.appointment.date, 'day') === false;
            }
            return true;
        },
        policyInfo() {
            if (!this.insurancePolicy || !this.insurancePolicy.company) {
                return '';
            }
            return __('{company}. Номер полиса: {number}. Контактный телефон: {phone}', {
                company: this.insurancePolicy.company.name,
                number: this.insurancePolicy.number,
                phone: this.insurancePolicy.company.phone_number
            });
        },
        isOnlineConsultation() {
            return this.appointment.services.some((service) => service.is_online);
        },
    },
    watch: {
        nextVisit: function (val) {
            this.$emit('next-visit-changed', val);
        }
    },
    methods: {
        updateServicesAndAssigments() {
            let updatedAppointment = new Appointment({id: this.appointment.id});
            updatedAppointment.fetch(['appointment_services_prices']).then(() => {
                this.appointment.services = updatedAppointment.services
            });
            this.loadAppointmentRecords(this.appointment.specialization_card.id, this.appointment.id, this.cardTemplate).then((appointmentRecords) => {
                this.cardAssigments = []
                appointmentRecords.records.forEach((record) => {
                    if (record instanceof CardAssignment) {
                        this.cardAssigments.push(record);
                        this.castAssigntments(record);
                    }
                });
            });
        },
        onResize: function (x, y, width, height) {
            this.x = x
            this.y = y
            this.width = width
            this.height = height
        },
        onDrag: function (x, y) {
            this.x = x
            this.y = y
        },
        load() {
            let appointment = new Appointment({id: this.$route.params.appointmentId});
            appointment.fetch([
                'status',
                'specialization',
                'card_specialization',
                'doctor',
                'appointment_services_prices',
                'patient_issued_discount_cards',
                'full_treatment_course',
                'insurance_policy',
                'clinic',
                'ambulance_call'
            ]).then(() => {
                this.appointment = appointment;
                this.doctorSpecializations = appointment.doctor_specializations.map(item => item.id);
                this.clinicRequisites = appointment.clinic_requisites;
                this.appointmentDoctor = appointment.doctor;
                this.insurancePolicy = appointment.insurance_policy;

                let surgeryMarks = [CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY, CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.ANESTHESIA];
                let emergencyMarks = [CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.EMERGENCY];
                this.isSurgery = appointment.card_specialization
                    ? (surgeryMarks.indexOf(appointment.card_specialization.service_group) !== -1)
                    : false;
                this.isEmergency = appointment.card_specialization
                    ? (emergencyMarks.indexOf(appointment.card_specialization.service_group) !== -1)
                    : false;
                this.getPatientDebt();

                if (_.isFilled(appointment.treatment_course_id)) {
                    this.treatmentCourse = new TreatmentCourse(appointment.treatment_course);
                }
                if (this.hasInvalidStatus(appointment)) {
                    appointment.changeSystemStatus(CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_DOCTOR);
                }
                if (!this.appointment.specialization_not_show_signal_records) {
                    this.loadSignalRecord(appointment);
                }

                if (_.isFilled(appointment.discount_card_id)) {
                    let card = appointment.patient.issued_discount_cards.find(item => {
                        return appointment.discount_card_id == item.id;
                    });

                    if (!_.isNull(card)) {
                        this.$discountData.discountCard = card;
                        this.$discountData.refreshDiscountType = 1;
                    }
                } else {
                    this.$discountData.refreshDiscountType = 0;
                    this.$discountData.discountCard = null;
                }

                if (this.isSurgery && surgeryMarks.indexOf(appointment.specialization_service_group) !== -1) {
                    this.getSurgeryAssignmentRecords().then((courseAssignment) => {
                        this.loadAppointmentData(appointment, courseAssignment);
                    });
                } else {
                    this.loadAppointmentData(appointment);
                }
            });
        },
        clearOutpatientCard() {
            if (this.outpatientRecord.fields.length) {
                this.outpatientRecord.complaints = '';
                this.outpatientRecord.diagnosis = '';
                this.outpatientRecord.diagnosis_icd = [];
                this.outpatientRecord.diagnosis_icd_names = [];

                this.outpatientRecord.fields.forEach((field) => {
                    field.option_value = null;
                    field.value = null;
                });
            }
        },
        loadSignalRecord(appointment) {
            let repository = new SignalRecordRepository();
            repository.getPatientRecord(appointment.patient_id).then((record) => {
                if (record === null) {
                    record = new SignalRecord({patient_id: appointment.patient_id});
                }
                this.signalRecord = record;
                if (this.$store.state.user.isDoctor) {
                    this.showSignalRecord();
                }
            });
        },
        getSurgeryAssignmentRecords() {
            if (this.treatmentCourse === null) {
                return Promise.resolve(null);
            }

            let services = [];
            this.treatmentCourse.appointments
                .filter((appointment) => {
                    return appointment.assignments && appointment.assignments.length !== 0;
                })
                .forEach((appointment) => {
                    let surgeryAssignments = appointment.assignments.filter(assignment => {
                        return assignment.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES;
                    });

                    surgeryAssignments.forEach((assignment) => {
                        services = [
                            ...services,
                            ...assignment.recordable[CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES]
                        ];
                    });
                });

            let surgeryService = services.find(service => {
                return service.is_base === true &&
                    service.specialization.service_group === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY &&
                    _.isFilled(service.card_assignment_id);
            });

            if (!surgeryService) {
                return Promise.resolve(null);
            }

            let repository = new CardRecordRepository();
            return repository.fetch({card_assignment: surgeryService.card_assignment_id}, null, ['appointment'])
                .then(response => {
                    if (response && response.rows && response.rows.length !== 0) {
                        let assignments = response.rows.filter(row => row instanceof CardAssignment);
                        assignments.forEach(record => {
                            this.castAssigntments(record);
                        });
                        let courseAssignment = assignments.find(item => item.recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES);
                        return Promise.resolve(courseAssignment);
                    }
                    return Promise.resolve(null);
                });
        },
        loadAppointmentData(appointment, courseAssignment = null) {
            let card = appointment.specialization_card;
            if (card) {
                this.getUsableTemplate(card, appointment, courseAssignment).then((template) => {
                    let cardId = courseAssignment ? courseAssignment.card_specialization_id : card.id;
                    this.loadAppointmentRecords(cardId, appointment.id, template).then((appointmentRecords) => {
                        this.initAppointmentData(appointment, card, appointmentRecords, template);
                    });
                });
            } else {
                this.$warning(__('У пациента нет карты'));
                this.loading = false;
            }
        },
        initAppointmentData(appointment, card, appointmentRecords, template) {
            this.activeCard = card;
            this.previousVisit = appointmentRecords.previousVisit;
            this.initRecords(appointmentRecords.records);
            if (template) {
                this.initOutpatientRecord(appointment, card, template, appointmentRecords.outpatientData);
                if (this.isSurgery && this.previousVisit && template.is_fallback === false) {
                    this.previousVisit.records.forEach(record => {
                        if (record instanceof OutpatientRecord && record.template_id != template.id) {
                            this.outpatientRecord.mergeWith(record);
                        }
                    });
                }
                this.cardTemplate = template;
            } else {
                this.$warning(__('Для данной специализации нет шаблона амбулаторной карты'));
            }
            this.loading = false;
        },
        getUsableTemplate(card, appointment, courseAssignment = null) {
            let specializations = appointment.doctor.specializations.map(s => s.id);
            if (specializations.indexOf(card.specialization_id) === -1) {
                specializations.push(card.specialization_id);
            }

            if (courseAssignment && courseAssignment.appointment) {
                specializations = [courseAssignment.appointment.specialization_id, ...specializations];
            }

            let repository = new SpecializationRepository();
            return repository.fetchList({id: specializations, has_card_template: 1}).then((list) => {
                let specializationIds = list.map(s => s.id);
                if (specializationIds.length === 0) {
                    return this.loadCardTemplate();
                }
                if (specializationIds.length === 1) {
                    return this.loadCardTemplate(specializationIds[0]);
                }
                if (specializationIds.indexOf(card.specialization_id) !== -1) {
                    return this.loadCardTemplate(card.specialization_id);
                }
                return new Promise((resolve, reject) => {
                    this.$modalComponent(SelectCardTemplate, {list}, {
                        selected: (dialog, specialization) => {
                            dialog.close();
                            this.loadCardTemplate(specialization)
                                .then((template) => {
                                    resolve(template);
                                }).catch((error) => {
                                reject(error);
                            });
                        }
                    }, {
                        header: __('Выбор шаблона карты'),
                        width: '400px',
                        closeOnEscape: false,
                        showClose: false,
                        customClass: 'no-footer',
                    });
                });
            });
        },
        loadCardTemplate(specialization = null) {
            let repository = new RecordTemplateRepository();
            return repository.getForSpecialization(specialization).then(response => {
                if (this.isSurgery || this.isEmergency) {
                    return this.loadTemplateAddons(repository).then(() => {
                        return Promise.resolve(response);
                    });
                }
                return Promise.resolve(response);
            });
        },
        loadTemplateAddons(repository = null) {
            repository = repository || new RecordTemplateRepository();
            return repository.fetch({specialization_addons: this.appointment.card_specialization_id}).then(templates => {
                if (templates && templates.rows) {
                    this.templateAddons = templates.rows;
                }
                return Promise.resolve();
            });
        },
        loadAppointmentRecords(cardId, appointmentId, template) {
            let repository = new CardRecordRepository();
            return repository.getAppointmentRecords(cardId, appointmentId, null, true, template ? template.id : null, this.isSurgery ? 0 : 1);
        },
        showSignalRecord() {
            this.$modalComponent(SignalRecordModal, {
                model: this.signalRecord,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Сигнальные обозначения пациента'),
                width: '1200px',
                closeOnEscape: false,
                showClose: false,
            });
        },
        initRecords(records) {
            records.forEach((record) => {
                if (record instanceof OutpatientRecord) {
                    this.mergeOutpatientRecord(record);
                } else if (record instanceof DiaryRecord) {
                    this.diaryRecords.unshift(record);
                } else if (record instanceof ServiceRecord) {
                    this.serviceRecords.unshift(record);
                } else if (record instanceof ConditionRecord) {
                    this.conditionRecords.unshift(record);
                } else if (record instanceof ProtocolRecord) {
                    this.protocols.unshift(record);
                } else if (record instanceof CardAssignment) {
                    this.cardAssigments.push(record);
                    this.castAssigntments(record);
                } else if (record instanceof TreatmentAssignment) {
                    this.treatmentAssignment = record;
                } else if (record instanceof ConsultationRecord) {
                    this.consultationRecord = record;
                } else if (record instanceof NextVisit) {
                    this.nextVisit = record;
                } else if (record instanceof PatientDocument) {
                    this.patientDocuments.unshift(record);
                } else if (record instanceof OutclinicProtocolRecord) {
                    this.patientResearch.unshift(record);
                } else if (record instanceof ManipulationRecord) {
                    console.log(record);
                    this.manipulationRecords.unshift(record);
                }
            });
        },
        mergeOutpatientRecord(record) {
            if (this.outpatientRecord === null) {
                this.outpatientRecord = record;
            } else {
                this.outpatientRecord.mergeWith(record);
            }
        },
        initOutpatientRecord(appointment, card, template, previous) {
            if (this.outpatientRecord === null) {
                this.outpatientRecord = new OutpatientRecord({
                    card_specialization_id: card.id,
                    appointment_id: appointment.id,
                    template_id: template.id,
                });
            }
            this.outpatientRecord.setPrevious(previous);
        },
        getPatientDebt() {
            let service = new AppointmentServiceRepository();
            let filters = {
                patient: this.appointment.patient_id,
                appointment_clinic: this.appointment.clinic_id,
                card_specialization: this.appointment.card_specialization_id,
            }
            service.fetchDebts(filters).then(response => {
                if (response && response.length != 0) {
                    this.patientServiceDebt = response.reduce((total, row) => {
                        let debt = Number(row.cost) - Number(row.paid);
                        if (debt > 0) {
                            total += debt;
                        }
                        return total;
                    }, 0);
                }
            });
        },
        outpatientRecordSaved() {
            this.outpatientRecordUnsaved = false;
        },
        outpatientRecordChanged() {
            this.outpatientRecordUnsaved = true;
        },
        hasInvalidStatus(appointment) {
            return appointment.status.system_status === CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_RECEPTION;
        },
        isIncompleted(appointment) {
            return appointment.status.system_status === CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_DOCTOR;
        },
        protocolAdded(protocol) {
            this.protocols = [...this.protocols, protocol];
        },
        assignmentsChanged(assignment) {
            let index = this.cardAssigments.findIndex(item => item.id === assignment.id);

            if (index === -1) {
                this.cardAssigments.push(assignment);
            } else {
                this.cardAssigments.splice(index, 1, assignment);
            }
            this.castAssigntments(assignment);
        },
        getAssignments(type) {
            let assignment = this.findAssignment(type);
            return this.getAssignedList(assignment, type);
        },
        getAssignedList(assignment, type) {
            if (_.isFilled(assignment)) {
                return assignment.recordable[type] || [];
            }
            return [];
        },
        assignmentListChanged({index, type}) {
            let list = this.getAssignments(type);
            list.splice(index, 1);
        },
        callMenuCallback(callback) {
            this.$refs.appointmentMenu[callback]();
        },
        addedAssignment({list, method}) {
            this.$refs.assignments[method](list);
        },
        consultationRecordAdded(list) {
            if (this.activeCard) {
                if (_.isVoid(this.consultationRecord)) {
                    this.consultationRecord = new ConsultationRecord({
                        appointment_id: this.appointment.id,
                        card_specialization_id: this.activeCard.id,
                    });
                }
                this.consultationRecord.consultations = list;
                this.saveConsultation();
            } else {
                this.$warning(__('У пациента нет карты'));
            }
        },
        removeConsultation(index) {
            this.consultationRecord.consultations.splice(index, 1);
            this.saveConsultation();
        },
        saveConsultation() {
            return this.consultationRecord.save().then(() => {
                this.$info(__('Назначение успешно сохранено'));
            }).catch((error) => {
                this.$error(__('Не удалось сохранить назначение'));
            });
        },
        documentAdded(record) {
            this.patientDocuments.unshift(record);
        },
        documentDeleted(record) {
            this.patientDocuments = this.patientDocuments.filter((r) => r !== record);
        },
        researchAdded(record) {
            this.patientResearch.unshift(record);
        },
        researchDeleted(record) {
            this.patientResearch = this.patientResearch.filter((r) => r !== record);
        },
        addDiaryRecord(record) {
            this.diaryRecords.push(record);
        },
        addManipulationRecord(record) {
            this.manipulationRecords.push(record);
        },
        addServiceRecord(record) {
            this.serviceRecords.push(record);
        },
        addConditionRecord(record) {
            this.conditionRecords.push(record);
        },
        nextVisitCreated(model) {
            this.nextVisit = model;
        },
        nextVisitDeleted() {
            this.nextVisit = null;
        },
        printAdvisory() {
            this.$modalComponent(PrintAdvisoryOptions, {
                structure: this.outpatientRecord.structure,
                activeCard: this.activeCard,
                appointment: this.appointment,
                appointmentDoctor: this.appointmentDoctor,
                clinicRequisites: this.clinicRequisites,
                outpatientRecord: this.outpatientRecord,
                diaryRecords: this.diaryRecords,
                manipulationRecords: this.manipulationRecords,
                diagnostics: [...this.diagnosticsList, ...this.outclinicDiagnostics],
                analyses: this.analysisList,
                consultations: this.consultations,
                medicines: this.medicineList,
                physiotherapies: this.physiotherapyList,
                procedures: this.procedureList,
                nextVisit: this.nextVisit,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Печать консультативного заключения'),
                width: '600px',
            });
        },
        showVideoChat() {
            this.displayVideoChat = true;
        },
        hideVideoChat() {
            this.displayVideoChat = false;
        },
        cameraPreviewChangedEvent(val){
            this.cameraPreview = val;
        }
    }
};
</script>
