import SearchAnalysis from '../assignment/analysis/Search.vue';
import SearchMedicine from '../assignment/medicines/Search.vue';
import SearchCourse from '../assignment/treatment-course/Search.vue';
import SearchProcedure from '../assignment/procedures/Search.vue';
import SearchPhysiotherapy from '../assignment/physiotherapies/Search.vue';
import SearchDiagnostic from '../assignment/diagnostics/Search.vue';
import ModalHeaderButton from '../ModalHeaderButton.vue';
import CostSwitcher from '../CostSwitcher.vue';
import ProtocolModal from '../protocol/Modal.vue';
import PatientHistory from '../patient-history/History.vue';
import ConsultationModal from '../consultation/Search.vue';
import NextVisitModal from '../next-visit/VisitForm.vue';
import DoctorServices from '../doctor-service/service/Search.vue';
import PatientDocument from '../patient-document/Search.vue';
import DoctorAnalyses from '../doctor-service/analysis/Search.vue';
import MedicineHeaderAddon from '../assignment/medicines/HeaderAddon.vue';
import SetPayment from '../set-payment/Payment.vue';
import DocumentForm from '../documents/DocumentForm.vue';
import OutclinicProtocolForm from "@/components/patients/cabinet/protocols/OutclinicProtocolForm";
import PatientRouteSearch from '../patient-clinic-route/Search.vue';
import SearchSurgeryService from '../assignment/surgery/Search.vue';
import CareEpisode from '../care-episode/Episode';
import CONSTANTS from '@/constants';

export default {
    methods: {
        filloutProtocol(specialization) {
            if (this.activeCard) {
                this.$modalComponent(ProtocolModal, {
                    appointment: this.appointment,
                    card: this.activeCard,
                    specialization,
                    protocols: this.protocols,
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    saved: (dialog, model) => {
                        this.$emit('protocol-added', model);
                    },
                }, {
                    header: __('Заполнить протокол исследования'),
                    width: '1020px',
                    customClass: 'padding-0',
                });
            } else {
                this.$error(__('У пациента нет карты'));
            }
        },
        setNextVisit() {
            this.$modalComponent(NextVisitModal, {
                appointment: this.appointment,
                nextVisit: this.nextVisit
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, model) => {
                    dialog.close();
                    this.$emit('next-visit-created', model);
                },
            }, {
                header: __('Назначить дату следующего визита'),
                width: '400px',
            });
        },
        assignConsultation() {
            this.$modalComponent(ConsultationModal, {
                appointment: this.appointment,
                consultations: this.consultations,
                readonly: this.readonly,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.$emit('consultation-signed', list);
                },
            }, {
                header: __('Назначить консультацию врачей'),
                width: '1020px',
                customClass: 'padding-0',
                headerAddon: {
                    component: ModalHeaderButton,
                    props: {
                        buttonText: __('Назначить врача вне клиники'),
                    },
                    eventListeners: {
                        clicked: (dialog) => {
                            dialog.getTopComponent().assignOutClinic();
                        },
                    },
                },
            });
        },
        assignAnalysis() {
            this.$modalComponent(SearchAnalysis, {
                appointment: this.appointment,
                analyses: this.analyses,
                readonly: this.readonly,
                insurancePolicy: this.insurancePolicy,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.$emit('added-assignment', {list, method: 'analysesSelected' });
                },
                deleted: (dialog, index) => {
                    this.$emit('assignment-list-changed', {
                        index,
                        type: CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS,
                    });
                },
            }, {
                header: __('Выбрать анализы'),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
        assignMedicine(props = {}, headerProps = {}) {
            this.$modalComponent(SearchMedicine, {
                doctor: {
                    id: this.appointment.doctor_id,
                    ...this.appointment.doctor,
                },
                clinicWorksWithApteka24: this.appointment.clinic_works_with_apteka24,
                medicines: this.medicines,
                clinic: this.appointment.clinic_id,
                patient: this.appointment.patient,
                readonly: this.readonly,
                insurancePolicy: this.insurancePolicy,
                ...props,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.$emit('added-assignment', {list, method: 'medicinesSelected' });
                },
                deleted: (dialog, index) => {
                    this.$emit('assignment-list-changed', {
                        index,
                        type: CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_MEDICINES,
                    });
                },
            }, {
                header: __('Назначить медикаменты'),
                width: '1080px',
                customClass: 'padding-0',
                headerAddon: {
                    component: MedicineHeaderAddon,
                    props: {
                        switcherText: __('Платные медикаменты'),
                        ...headerProps,
                    },
                    eventListeners: {
                        toggleCost: (dialog, costType) => {
                            dialog.getTopComponent().toggleCost(costType);
                        },
                        clicked: (dialog) => {
                            dialog.getTopComponent().addOutClinicMedicine();
                        }
                    },
                }
            });
        },
        assignCourse() {
            this.$modalComponent(SearchCourse, {
                appointment: this.appointment,
                insurancePolicy: this.insurancePolicy,
                readonly: this.readonly,
                doctorSpecializations: this.doctorSpecializations,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.$emit('course-signed', list);
                },
                deleted: (dialog, service) => {
                    this.$emit('remove-treatment-service', service);
                },
            }, {
                header: __('Назначить курс лечения'),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
        assignProcedure(props = {}, headerProps = {}) {
            this.$modalComponent(SearchProcedure, {
                appointment: this.appointment,
                insurancePolicy: this.insurancePolicy,
                procedures: this.procedures,
                readonly: this.readonly,
                ...props,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.$emit('added-assignment', {list, method: 'proceduresSelected' });
                },
                deleted: (dialog, index) => {
                    this.$emit('assignment-list-changed', {
                        index,
                        type: CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PROCEDURES,
                    });
                },
            }, {
                header: __('Назначить процедуры'),
                width: '1020px',
                customClass: 'padding-0',
                headerAddon: {
                    component: CostSwitcher,
                    props: {
                        switcherText: __('Платные манипуляции'),
                        ...headerProps,
                    },
                    eventListeners: {
                        toggleCost: (dialog, costType) => {
                            dialog.getTopComponent().toggleCost(costType);
                        },
                    },
                }
            });
        },
        assignPhysiotherapy(props = {}, headerProps = {}) {
            this.$modalComponent(SearchPhysiotherapy, {
                appointment: this.appointment,
                insurancePolicy: this.insurancePolicy,
                physiotherapies: this.physiotherapies,
                readonly: this.readonly,
                ...props,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.$emit('added-assignment', {list, method: 'physiotherapiesSelected' });
                },
                deleted: (dialog, index) => {
                    this.$emit('assignment-list-changed', {
                        index,
                        type: CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PHYSIOTHERAPIES,
                    });
                },
            }, {
                header: __('Назначить физиотерапию'),
                width: '1020px',
                customClass: 'padding-0',
                headerAddon: {
                    component: CostSwitcher,
                    props: {
                        switcherText: __('Платные физиопроцедуры'),
                        ...headerProps,
                    },
                    eventListeners: {
                        toggleCost: (dialog, costType) => {
                            dialog.getTopComponent().toggleCost(costType);
                        },
                    },
                }
            });
        },
        assignDiagnostic() {
            this.$modalComponent(SearchDiagnostic, {
                appointment: this.appointment,
                diagnostics: this.diagnostics,
                insurancePolicy: this.insurancePolicy,
                outclinicDiagnostics: this.outclinicDiagnostics,
                readonly: this.readonly,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.$emit('added-assignment', {list, method: 'diagnosticsSelected' });
                },
                deleted: (dialog, index) => {
                    this.$emit('assignment-list-changed', {
                        index,
                        type: CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS,
                    });
                },
            }, {
                header: __('Назначить аппаратную диагностику'),
                width: '1020px',
                customClass: 'padding-0',
                headerAddon: {
                    component: ModalHeaderButton,
                    props: {
                        buttonText: __('Назначить диагностику вне клиники'),
                    },
                    eventListeners: {
                        clicked: (dialog) => {
                            dialog.getTopComponent().assignOutClinic();
                        },
                    },
                },
            });
        },
        patientHistory() {
            this.$modalComponent(PatientHistory, {
                patient: this.appointment.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog) => {
                    dialog.close();
                },
            },
            {
                header: __('История пациента: {name}', {name: this.appointment.patient.full_name}),
                width: '1020px',
            });
        },
        makeServices(props = {}) {
            this.$modalComponent(DoctorServices, {
                appointment: this.appointment,
                insurancePolicy: this.insurancePolicy,
                readonly: this.readonly,
                ...props,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, services) => {
                    dialog.close();
                    this.$emit('service-list-changed', services);
                },
                deleted: (dialog, service) => {
                    this.$emit('remove-doctor-service', service);
                },
            },
            {
                header: __('Добавить услуги в рамках приема: {name}', {name: this.appointment.patient.full_name}),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
        makeAnalyses() {
            this.$modalComponent(DoctorAnalyses, {
                appointment: this.appointment,
                insurancePolicy: this.insurancePolicy,
                readonly: this.readonly,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, analyses) => {
                    dialog.close();
                    this.$emit('analysis-list-changed', analyses);
                },
                deleted: (dialog, analysis) => {
                    this.$emit('remove-doctor-analysis', analysis);
                },
            },
            {
                header: __('Забор анализов: {name}', {name: this.appointment.patient.full_name}),
                width: '1120px',
                customClass: 'padding-0',
            });
        },
        makeIssuePatientDocument() {
            this.$modalComponent(PatientDocument, {
                appointment: this.appointment,
                clinicRequisites: this.clinicRequisites,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            },
            {
                header: __('Выдать документ для подписи: {name}', {name: this.appointment.patient.full_name}),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
        setExpectedPayment() {
            this.$modalComponent(SetPayment, {
                appointment: this.appointment,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                completed: (dialog) => {
                    dialog.close();
                },
            },
            {
                header: __('Назначить оплату: {name}', {name: this.appointment.patient.full_name}),
                width: '930px',
                customClass: 'padding-0',
            });
        },
        addDocument() {
            this.$modalComponent(DocumentForm, {
                appointment: this.appointment,
                card: this.activeCard,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, record) => {
                    dialog.close();
                    this.$emit('document-added', record);
                },
            },
            {
                header: __('Прикрепить документ к пациенту: {name}', {name: this.appointment.patient.full_name}),
                width: '450px',
                customClass: 'padding-0',
            });
        },
        addProtocol() {
            this.$modalComponent(OutclinicProtocolForm, {
                patient: this.appointment.patient,
                appointment: this.appointment
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, record) => {
                    dialog.close();
                    this.$emit('research-added', record);
                },
            },
            {
                header: __('Прикрепить результат исследования к пациенту: {name}', {name: this.appointment.patient.full_name}),
                width: '450px',
                customClass: 'padding-0',
            });
        },
        goPatientCabinet() {
            let routeData = this.$router.resolve({name: 'patient-cabinet-outpatient', params: {patientId: this.appointment.patient_id}});
            window.open(routeData.href, '_blank');
        },
        showPatientRoutes() {
            this.$modalComponent(PatientRouteSearch, {
                appointment: this.appointment,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, record) => {
                    dialog.close();
                },
            },
            {
                header: __('Маршруты пациента'),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
        surgeyDoctorService() {
            return this.makeServices({isSurgery: this.isSurgery});
        },
        surgeyDoctorAnalyses() {
            return this.makeAnalyses();
        },
        assignSurgeryConsultations() {
            return this.assignConsultation();
        },
        assignSurgeryDiagnostic() {
            return this.assignDiagnostic();
        },
        assignSurgeryAnalyses() {
            return this.assignAnalysis();
        },
        assignSurgeryMedicines() {
            return this.assignMedicine({costInitial: true}, {initialState: true});
        },
        assignSurgeryProcedure() {
            return this.assignProcedure({costInitial: true}, {initialState: true});
        },
        assignSurgeryPhysiotherapy() {
            return this.assignPhysiotherapy({costInitial: true}, {initialState: true});
        },
        assignSurgery() {
            this.$modalComponent(SearchSurgeryService, {
                appointment: this.appointment,
                insurancePolicy: this.insurancePolicy,
                surgeryServices: this.surgeryBaseServices,
                readonly: this.readonly,
                isBase: true,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.$emit('added-assignment', {list, method: 'surgeryBaseServicesSelected' });
                },
                deleted: (dialog, index) => {
                    this.$emit('assignment-list-changed', {
                        index,
                        type: CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES,
                    });
                },
            }, {
                header: __('Назначить операцию'),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
        assignSurgeryServices() {
            this.$modalComponent(SearchSurgeryService, {
                appointment: this.appointment,
                insurancePolicy: this.insurancePolicy,
                surgeryServices: this.surgeryServices,
                readonly: this.readonly,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.$emit('added-assignment', {list, method: 'surgeryServicesSelected' });
                },
                deleted: (dialog, index) => {
                    this.$emit('assignment-list-changed', {
                        index,
                        type: CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_SERVICES,
                    });
                },
            }, {
                header: __('Назначить услуги'),
                width: '1020px',
                customClass: 'padding-0',
                headerAddon: {
                    component: CostSwitcher,
                    props: {
                        switcherText: __('Платные услуги'),
                        initialState: true,
                    },
                    eventListeners: {
                        toggleCost: (dialog, costType) => {
                            dialog.getTopComponent().toggleCost(costType);
                        },
                    },
                }
            });
        },
        episodeOfCare() {
            this.$modalComponent(CareEpisode, {
                appointment: this.appointment,
                outpatientRecord: this.outpatientRecord,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Эпизод оказания медицинской помощи'),
                width: '800px',
            });
        },
    },
}
