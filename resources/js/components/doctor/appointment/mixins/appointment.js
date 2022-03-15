import TreatmentCourse from '@/models/treatment-course';
import TreatmentAssignment from '@/models/patient/card/treatment-assignment';
import Result from '@/models/analysis/result';
import AssignedMedicine from '@/models/patient/assigned-medicine';
import AssignedService from '@/models/patient/assigned-service';
import OutclinicService from '@/models/patient/outclinic-service';
import GenericService from '@/repositories/service';
import AppointmentService from '@/models/appointment/service';
import AnalysisResult from '@/models/analysis/result';
import CONSTANTS from '@/constants';

export default {
    watch: {
        cardAssigments() {
            if (this.blankAssignments) {
                this.analysisList = [];
            }
            this.analysisList.splice(0 );
            this.analysisList.push(...this.getAssignments(CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS));

        }
    },
    data() {
        return {
            analysisList: []
        }
    },
    computed: {
        blankAssignments() {
            return this.cardAssigments.length === 0;
        },
        medicineList() {
            if (this.blankAssignments) {
                return [];
            }
            return this.getAssignments(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_MEDICINES);
        },
        procedureList() {
            if (this.blankAssignments) {
                return [];
            }
            return this.getAssignments(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PROCEDURES);
        },
        physiotherapyList() {
            if (this.blankAssignments) {
                return [];
            }
            return this.getAssignments(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PHYSIOTHERAPIES);
        },
        diagnosticsList() {
            if (this.blankAssignments) {
                return [];
            }
            return this.getAssignments(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS);
        },
        outclinicDiagnostics() {
            if (this.blankAssignments) {
                return [];
            }
            let assignment = this.findAssignment(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS);
            return this.getAssignedList(assignment, CONSTANTS.CARD_ASSIGNMENT.TYPES.OUTCLINIC_SERVICES);
        },
        consultations() {
            return this.consultationRecord ? this.consultationRecord.consultations : [];
        },
        surgeryBaseServices() {
            if (this.blankAssignments) {
                return [];
            }
            return this.getAssignments(CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES);
        },
        surgeryServices() {
            if (this.blankAssignments) {
                return [];
            }
            return this.getAssignments(CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_SERVICES);
        },
        getAnalysisService() {
            return this.appointment.services.find((service) => {
                return (service.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES)
            });
        },
    },
    methods: {
        castAssigntments(record) {
            let recordable = record.recordable;
            if (_.isFilled(recordable)) {
                if (recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS) {
                    recordable.analysis_results = recordable.analysis_results.map(row => new Result(row));
                }

                if (recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_MEDICINES) {
                    recordable.assigned_medicines = recordable.assigned_medicines.map(row => new AssignedMedicine(row));
                }

                if (recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PROCEDURES) {
                    recordable.assigned_procedures = recordable.assigned_procedures.map(row => new AssignedService(row));
                }

                if (recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PHYSIOTHERAPIES) {
                    recordable.assigned_physiotherapies = recordable.assigned_physiotherapies.map(row => new AssignedService(row));
                }

                if (recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS) {
                    recordable.assigned_diagnostics = recordable.assigned_diagnostics.map(row => new AssignedService(row));
                    recordable.outclinic_services = recordable.outclinic_services.map(row => new OutclinicService(row));
                }

                if (recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_BASE_SERVICES) {
                    recordable.surgery_base_services = recordable.surgery_base_services.map(row => new AssignedService(row));
                }

                if (recordable.type === CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_SERVICES) {
                    recordable.surgery_services = recordable.surgery_services.map(row => new AssignedService(row));
                }
            }
        },
        servicesChanged(list) {
            this.setServiceList(list);
            return this.saveAppointment();
        },

        analysesChanged(list) {
            let analysisService = this.getAnalysisService;
            if (analysisService) {
                this.updateAnalysis(list, analysisService).then(() => {
                    return this.saveAppointment();
                });
            } else {
                this.createAnalysisContainer().then(container => {
                    list.forEach(item => {
                        let service = this.createService(container, item);
                        this.appointment.services.push(service);
                    });
                    return Promise.resolve();
                }).then(() => {
                    return this.saveAppointment();
                });
            }

        },
        updateAnalysis(list, analysisService) {
            list.forEach(item => {
                let analysis = this.findAnalysesContainers(item);
                if (analysis) {
                    let analysisIndex = analysis.items.findIndex((row) => {
                        return item.analysis_id === row.analysis_id;
                    });
                    if (analysisIndex != -1) {
                        analysis.items.splice(analysisIndex, 1, new AnalysisResult(item.attributes))
                    }
                } else{
                    let service = this.createService(analysisService, item);
                    this.appointment.services.push(service);

                }
             });
             return Promise.resolve();
        },
        createService(service,item) {
            return new AppointmentService({
                service_id: service.service_id,
                container_type: CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES,
                items: [item],
            });
        },

        findAnalysesContainers(analysis) {
            let containers = this.appointment.services.filter((service) => {
                return (service.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES)
            });
            if (containers.length != 0) {
                return containers.find((container) => {
                    return container.items.filter(item => item.analysis_id === analysis.analysis_id).length != 0;
                });
            }
            return null;
        },
        createAnalysisContainer() {
            let serviceRepo = new GenericService();
            return serviceRepo.fetchList(this.getServiceContainerFilter(), null, 1).then((response) => {
                if (response.length !== 0) {
                    let service = new AppointmentService({
                        service_id: response[0].id,
                        container_type: CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES,
                    });
                    this.appointment.services.push(service);
                    return Promise.resolve(service);
                }
                return Promise.reject();
            });
        },
        getServiceContainerFilter() {
            return {
                payment_destination_mark: CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_ANALYSES,
                clinic: this.appointment.clinic_id,
            }
        },
        createSurgeryCourse() {
            let course = new TreatmentCourse({
                start: this.$moment().format('YYYY-MM-DD'),
                patient_id: this.appointment.patient_id,
                doctor_id: this.appointment.doctor_id,
                card_specialization_id: this.activeCard.specialization_id,
                is_surgery: true,
            });

            return course.save().then(() => {
                this.treatmentCourse = course;
                this.appointment.treatment_course_id = course.id;
                this.$info(__('Лечение успешно назначено'));
                return this.saveAppointment().then(() => {
                    this.treatmentCourse.fetch();
                    return Promise.resolve();
                });
            }).catch((error) => {
                this.$error(__('Не удалось назначить лечение'));
                return Promise.reject();
            });
        },
        courseSigned(list) {
            this.createTreatmentCourse(list).then(() => {
                this.setServiceList(list);
                this.saveAppointment().then(() => {
                    this.treatmentCourse.fetch();
                });
            }).catch((e) => {
                this.$displayErrors(e);
                return Promise.reject();
            });
        },
        saveAppointment() {
            this.loading = true;
            return this.appointment.save().then(() => {
                this.loading = false;
                this.$info(__('Запись успешно обновлена'));
            }).catch((e) => {
                this.$error(__('Не удалось обновить запись'));
                return Promise.reject(e);
            });
        },
        createTreatmentCourse(list) {
            if (this.activeCard) {
                if (_.isVoid(this.appointment.treatment_course_id) && this.hasBaseService(list)) {
                    return this.saveTreatment(this.activeCard);
                } else {
                    return this.createTreatmentAssignment(this.activeCard).then(() => {
                        this.$info(__('Лечение успешно назначено'));
                        return Promise.resolve();
                    }).catch((e) => {
                        this.$error(__('Не удалось назначить лечение'));
                        return Promise.reject(e);
                    });
                }
            }
            return Promise.resolve();
        },
        saveTreatment(card, courseAttributes = {}) {
            let course = new TreatmentCourse({
                start: this.$moment().format('YYYY-MM-DD'),
                patient_id: this.appointment.patient_id,
                doctor_id: this.appointment.doctor_id,
                card_specialization_id: card.specialization_id,
                ...courseAttributes,
            });

            return course.save().then(() => {
                this.treatmentCourse = course;
                return this.createTreatmentAssignment(card).then(() => {
                    this.appointment.treatment_course_id = course.id;
                    this.$info(__('Лечение успешно назначено'));
                    return Promise.resolve();
                }).catch((error) => {
                    this.$error(__('Не удалось назначить лечение'));
                    return Promise.reject();
                });
            }).catch(() => {
                this.$error(__('Не удалось назначить лечение'));
                return Promise.reject();
            });
        },
        createTreatmentAssignment(card) {
            let assignment = new TreatmentAssignment({
                card_specialization_id: card.id,
                treatment_course_id: this.treatmentCourse ? this.treatmentCourse.id : null,
                appointment_id: this.appointment.id,
            });

            if (_.isFilled(this.treatmentAssignment)) {
                assignment.id = this.treatmentAssignment.id;
                assignment.initial = this.treatmentAssignment.recordable.initial;
            } else {
                assignment.initial = _.isVoid(this.appointment.treatment_course_id);
            }
            return assignment.save().then(() => {
                this.treatmentAssignment = assignment;
                return Promise.resolve();
            });
        },
        removeTreatmentService(service) {
            let list = [...this.appointment.services];
            this.appointment.services = _.reject(list, (item) => service.service_id == item.service_id)
                .map(service => {
                    service.treatment_assignment_id = null;
                    return service;
                });
            this.appointment.treatment_course_id = null;
            this.saveAppointment().then(() => this.deleteTreatmentCourse());
        },
        removeDoctorService(service) {
            let appointmentService = new AppointmentService({id: service.id});
            appointmentService.isDeleteable().then(response => {
                let list = [...this.appointment.services];
                this.appointment.services = _.reject(list, (item) => service.service_id == item.service_id);
                this.saveAppointment();
            }).catch((e) => {
                return this.$error(__('Услугу невозможно удалить'));
            });
        },
        removeDoctorAnalysis(analysis) {
            let analysisContainer = this.findAnalysesContainers(analysis);
            if (analysisContainer) {
                analysisContainer.items = _.reject(analysisContainer.items, (item) => item.analysis_id == analysis.analysis_id);
                this.saveAppointment();
            }
        },
        deleteTreatmentCourse() {
            if (_.isVoid(this.treatmentCourse)) {
                return Promise.resolve();
            }

            return this.treatmentCourse.delete().then((response) => {
                this.treatmentAssignment = null;
                this.treatmentCourse = null;
                this.$info(__('Курс лечения успешно удален'));
                return Promise.resolve();
            }).catch((error) => {
                this.$error(__('Не удалось удалить курс лечения'));
                return Promise.reject();
            });
        },
        setServicesTreatmentCourse(list) {
            let treatmentAssignmentId = (this.treatmentAssignment && this.treatmentAssignment.recordable)
                ? this.treatmentAssignment.recordable.id
                : null;
            return list.map(service => {
                service.treatment_assignment_id = treatmentAssignmentId;
                return service;
            });
        },
        setServiceList(list) {
            list = this.setServicesTreatmentCourse(list);
            if (this.appointment.services.length === 0) {
                this.appointment.services = list;
            } else {
                this.appointment.services = [...this.appointment.services, ...this.getUniqueServices(list)];
            }
        },
        getUniqueServices(list) {
            let services = [];
            list.forEach(item => {
                let index = this.appointment.services.findIndex(service => {
                    return service.id == item.id;
                });

                if (index === -1) {
                    services.push(item);
                }
            });
            return services;
        },
        hasBaseService(list) {
            let base = list.find(item => item.is_base != false);
            return _.isFilled(base);
        },
    },
}
