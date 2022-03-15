<template>
    <div class="treatment-course-details sections-wrapper" v-loading="loading">
        <section
            class="grey course-header">
            <el-row>
                <el-col :span="13">
                    <el-row :gutter="20">
                        <el-col :span="14">
                            <p>
                                {{ __('Начало лечения:') }} {{ $formatter.dateFormat(course.start) }}
                            </p>
                            <p>
                                {{ __('Диагноз по МКБ:') }} {{ $formatter.listFormat(diagnosisIcd) }}
                            </p>
                            <p>
                                {{ __('Диагноз:') }} {{ diagnosis }}
                            </p>
                        </el-col>
                        <el-col :span="10">
                            <p>
                                {{ __('Окончание лечения:') }}
                                <template v-if="course.end">
                                    {{ $formatter.dateFormat(course.end) }}
                                </template>
                            </p>
                            <p>
                                <a
                                    href="#"
                                    class="underline"
                                    :disabled="!course.end"
                                    @click.prevent="continueCourse">
                                    {{ __('Продлить курс лечения') }}
                                </a>
                            </p>
                            <p v-if="course.is_surgery && surgeryDate">
                                {{ __('Дата операции:') }} {{ $formatter.dateFormat(surgeryDate) }}
                            </p>
                        </el-col>
                    </el-row>
                </el-col>
                <el-col :span="11">
                    <div class="cost-wrapper" v-if="isDoctorSpecialization">
                        <el-row :gutter="20">
                            <el-col :span="12">
                                <p>
                                    {{ __('Стоимость курса:') }} {{ $formatter.numberFormat(courseBaseCost) }} {{ __('грн.') }}
                                </p>
                                <p>
                                    {{ __('Себестоимость курса:') }} {{ $formatter.numberFormat(courseSelfCost) }} {{ __('грн.') }}
                                </p>
                                <p v-if="discount.length === 1">
                                    {{ __('Скидка:') }} {{ $formatter.numberFormat(discount[0], 0) }}%
                                </p>
                            </el-col>
                            <el-col :span="12">
                                <p>
                                    {{ __('Итого со скидкой:') }} {{ $formatter.numberFormat(courseCost) }} {{ __('грн.') }}
                                </p>
                                <p>
                                    {{ __('Оплачено:') }} {{ $formatter.numberFormat(coursePayed) }} {{ __('грн.') }}
                                </p>
                                <p>
                                    {{ __('Долг:') }} {{ $formatter.numberFormat(courseCost - coursePayed) }} {{ __('грн.') }}
                                </p>
                            </el-col>
                        </el-row>
                    </div>
                </el-col>
            </el-row>
        </section>
        <el-tabs v-model="activeTab" class="tab-group-grey" v-if="!loading">
            <el-tab-pane
                :label="__('Услуги')"
                name="services" >
                <section class="darkgrey-cap pt-0">
                    <services-list
                        :rows="services"
                        :is-doctor-sepcialization="isDoctorSpecialization"
                        :show-discounts="discount.length !== 1" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Процедуры')"
                name="procedures" >
                <section class="pt-0">
                    <procedures-list
                        :assigned="assigned_procedures"
                        :rows="procedures"
                    />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Физиотерапия')"
                name="physiotherapies" >
                <section class="pt-0">
                    <physiotherapies-list
                        :assigned="assigned_physiotherapies"
                        :rows="physiotherapies" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Медикаменты')"
                name="medicines" >
                <section class="pt-0">
                    <medicines-list
                        :issued="issued_medicines"
                        :rows="medicines"
                    />
                </section>
            </el-tab-pane>
            <el-tab-pane 
                v-if="course.is_surgery"
                :label="__('МОЗ формы')"
                name="moz_blanks">
                <section class="pt-0">  
                    <moz-blank-list
                        :course="course"
                        :patient="patient"
                        :diagnosisIcd="diagnosisIcd"
                        :diagnosis="diagnosis"
                        :treatment-activities="treatmentActivities" />
                </section>
            </el-tab-pane>
        </el-tabs>
        <div
            v-if="$can('patient-cabinet.treatment-course-edit')"
            style="margin: initial"
            class="form-footer text-left">
            <el-button
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import CONSTANTS  from '@/constants';
import ServicesList from './Services.vue';
import ProceduresList from './Procedures.vue';
import PhysiotherapiesList from './Physiotherapies.vue';
import MedicinesList from './Medicines.vue';
import MozBlankList from './MozBlanks.vue';
import CardRecordRepository from '@/repositories/patient/card/record';
import TreatmentCourseEditModal from './modal/Edit';
import PaymentDestinations from '@/repositories/service/payment-destination';

export default {
    components: {
        ServicesList,
        ProceduresList,
        PhysiotherapiesList,
        MedicinesList,
        MozBlankList,
    },
    props: {
        course: Object,
        patient: Object,
    },
    data() {
        return {
            loading: true,
            activeTab: 'services',
            services: [],
            procedures: [],
            assigned_procedures: [],
            physiotherapies: [],
            assigned_physiotherapies: [],
            medicines: [],
            issued_medicines: [],
            courseCost: [],
            courseBaseCost: 0,
            courseSelfCost: 0,
            discount: [],
            coursePayed: [],
            diagnosis: null,
            diagnosisIcd: [],
            isDoctor: this.$store.state.user.isDoctor,
            surgeryDate: null,
            treatmentActivities: {},
            operationDestination: null,
        };
    },
    computed: {
        isDoctorSpecialization() {
            if (this.isDoctor) {
                return this.$store.state.user.specializations.indexOf(this.course.card_specialization_id) !== -1;
            }
            return true;
        },
    },
    mounted() {
        if (this.course.is_surgery) {
            this.getPaymentDestinations().then(() => {
                this.loadDiagnosis();
            });
        } else {
            this.loadDiagnosis();
        }
    },
    methods: {
        loadDiagnosis() {
            this.course.fetch(['detailed', 'documents']).then(() => {
                this.getLists();
                let appointment = this.getInitialCourseAppointment();
                let card = this.patient.getCard(appointment.clinic_id, 
                    this.course.is_surgery ? appointment.card_specialization_id : appointment.specialization_id);

                //If there's no card by appointment spec look for treatment course spec card
                if (!card) {
                    card = this.patient.getCard(appointment.clinic_id, this.course.card_specialization_id);
                }

                let repository = new CardRecordRepository();
                repository.getAppointmentRecords(card.id, appointment.id, {
                    type: CONSTANTS.CARD_RECORD.TYPE.OUTPATIENT_RECORD,
                }, false).then((result) => {
                    this.castDiagnosis(result);
                    this.loading = false;
                });
            });
        },
        getLists() {
            this.services = this.getServices();
            this.procedures = this.getProcedures();
            this.assigned_procedures = this.getAssignedProcedures();
            this.physiotherapies = this.getPhysiotherapies();
            this.assigned_physiotherapies = this.getAssignedPhysiotherapies();
            this.medicines = this.getMedicines();
            this.issued_medicines = this.getIssuedMedicines();
            this.courseCost = this.calcCourseCost();
            this.courseBaseCost = this.calcCourseBaseCost();
            this.courseSelfCost = this.calcCourseSelfCost();
            this.discount = this.calcCourseDiscount();
            this.coursePayed = this.calcCoursePayed();
            this.surgeryDate = this.getSurgeryDate();
            if (this.course.is_surgery) {
                this.seedTreatmentActivities();
            }
        },
        getPaymentDestinations() {
            let destinations = new PaymentDestinations();
            return destinations.fetchList().then(response => {
                this.operationDestination = response.find(row => {
                    return row.additional_service_mark === CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_OPERATION;
                });
                return Promise.resolve();
            });
        },
        seedTreatmentActivities() {
            //Seed assigned and used services
            this.treatmentActivities.operation_services = _.orderBy([
                ...this.prepareOperationServices(this.assigned_procedures, this.procedures),
                ...this.prepareOperationServices(this.assigned_physiotherapies, this.physiotherapies),
                ...this.getOperationServices(),
                ...this.getDiagnostics(),
            ], 'appointment_date', 'asc');

            //Seed assigned and used analysis
            this.treatmentActivities.analysis_results = this.getTreatmentAnalyses();

            //Seed assigned and issued medicines
            this.treatmentActivities.medicines = [...this.medicines].map(row => {
                if (row.medicine.medicine_type === CONSTANTS.ASSIGNED_MEDICINE.TYPES.OUTCLINIC_MEDICINE) {
                    row.medicine.issued_quantity = __('Из аптек');
                }
                return row;
            });
            
            //Seed assigned consultations
            this.treatmentActivities.consultations = this.getConsultations();
        },
        getDiagnostics() {
            let diagnostics = this.getAssignedGroups(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_DIAGNOSTICS);
            let appointmentServices = this.course.appointments.reduce((services, appointment) => {
                return [...services, ...appointment.services];
            }, []);
            return this.prepareOperationServices(diagnostics, appointmentServices, false);
        },
        getOperationServices() {
            if (!this.operationDestination) {
                return [];
            }
            let operationAssignments = this.getAssignedGroups(CONSTANTS.CARD_ASSIGNMENT.TYPES.SURGERY_SERVICES)
                .filter(item => {
                    return item.service.payment_destination_id === this.operationDestination.id
                        && !item.service.is_base;
                });
            let appointmentServices = this.getServicesOfGroup(CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY);
            return this.prepareOperationServices(operationAssignments, appointmentServices);
        },
        prepareOperationServices(assignedList, services, nestedService = true) {
            let results = [];
            let serviceGroups = _.groupBy(assignedList, 'service.service_id');
            Object.keys(serviceGroups).forEach(serviceId => {
                let group = serviceGroups[serviceId];
                let assignedQuantity = group.reduce((quantity, item) => {
                    return quantity + Number(item.service.assigned_quantity);
                }, 0);
                let usedQuantity = services.filter(item => {
                        let service_id = nestedService ? item.service.service_id : item.service_id;
                        return service_id == serviceId;
                    }).reduce((quantity, item) => {
                        let itemQuantity = nestedService ? item.service.quantity : item.quantity;
                        return quantity + Number(itemQuantity);
                    }, 0);

                results.push({
                    id: serviceId,
                    appointment_date: group[0].appointment.date,
                    service_name: group[0].service.name,
                    assigned_quantity: assignedQuantity,
                    used_quantity: usedQuantity,
                });
            });
            return results;
        },
        getTreatmentAnalyses() {
            let assignedAnalyses = [];
            let analysisGroup = CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS;
            this.course.appointments.forEach((appointment) => {
                appointment.assignments
                    .filter((assignment) => assignment.recordable.type === analysisGroup)
                    .forEach((assignment) => {
                        assignment.recordable[analysisGroup].forEach(analysis => {
                            let index = assignedAnalyses.findIndex(item => item.analysis_id === analysis.analysis_id);
                            if (index !== -1) {
                                assignedAnalyses[index].quantity += Number(analysis.quantity);
                            } else {
                                assignedAnalyses.push(analysis);
                            }
                        });
                    });
            });

            let analysisResults = [];
            this.course.appointments.forEach(appointment => {
                analysisResults = [...analysisResults, ...appointment.analysis_results];
            });
            
            return [
                // Count number of uses assigned analysis results
                ...assignedAnalyses.map(analysis => {
                    analysis.used_quantity = analysisResults.reduce((total, item) => {
                        if ((analysis.card_assignment_id === item.card_assignment_id) && 
                            (analysis.analysis_id === item.analysis_id)) {
                            total += Number(item.quantity);
                        }
                        return total;
                    }, 0);
                    return analysis;
                }), 
                // Prepend not assigned analysis results
                ...analysisResults.filter(analysis => _.isVoid(analysis.card_assignment_id))
                    .map(analysis => {
                        analysis.used_quantity = analysis.quantity;
                        analysis.quantity = 0;
                        analysis.analysis = {name: analysis.analysis_name};
                        return analysis;
                    }
                )
            ];
        },
        getSurgeryDate() {
            let surgery = this.course.appointments.find(appointment => {
                return appointment.services.findIndex(service => {
                    return service.specialization.service_group === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY 
                        && service.is_base;
                }) !== -1;
            });
            return surgery ? surgery.date : null;
        },
        castDiagnosis(result) {
            this.diagnosis = result.outpatientData.diagnosis
                ? result.outpatientData.diagnosis
                : (result.records[0] ? result.records[0].diagnosis : '');
            this.diagnosisIcd = _.uniq(result.outpatientData.diagnosis_icd_names.length != 0
                ? result.outpatientData.diagnosis_icd_names
                : this.getIcdDiagnoses(result));
        },
        getIcdDiagnoses(result) {
            let list = [];
            result.records.forEach(record => {
                list = [...list, ...record.diagnosis_icd_names];
            })
            return list;
        },
        getInitialCourseAppointment() {
            let initialAppointment = this.course.appointments.find((appointment) => {
                return appointment.treatment_card_record != null &&
                       appointment.treatment_card_record.recordable &&
                       appointment.treatment_card_record.recordable.initial == true;
            });
            return (initialAppointment != undefined) ? initialAppointment : this.course.appointments[0];
        },
        calcCourseCost() {
            return this.getBaseServices().reduce((sum, service) => {
                return sum + Number((service.cost ? service.cost : 0));
            }, 0);
        },
        calcCourseBaseCost() {
            return this.getBaseServices().reduce((sum, service) => {
                return sum + Number((service.base_cost ? (service.quantity * service.base_cost) : 0));
            }, 0);
        },
        calcCourseDiscount() {
            return _.uniq(this.getBaseServices().reduce((sum, service) => {
                return sum.concat([service.discount]);
            }, []));
        },
        calcCourseSelfCost() {
            let medicineCostField = this.$store.state.user.isDoctor ? 'base_cost' : 'self_cost';

            return this.course.appointments.reduce((sum, appointment) => {
                return sum + appointment.services.reduce((sum, service) => {
                    return sum + Number(
                        ((service.self_cost && !service.is_base && _.isFilled(service.specialization.service_group))
                        ? (service.quantity * service.self_cost)
                        : 0)
                    );
                }, 0) + appointment.medicines.reduce((sum, medicine) => {
                     return sum + Number(
                            (medicine[medicineCostField] ? (medicine.quantity * medicine[medicineCostField]) : 0)
                        );
                }, 0);
             }, 0);
        },
        calcCoursePayed() {
            return this.getBaseServices().reduce((sum, service) => {
                return sum + Number(service.payed);
            }, 0);
        },
        getServices() {
            let result = [];
            this.course.appointments.forEach((appointment) => {
                appointment.services
                    .filter((service) => service.is_base == true)
                    .forEach((service) => {
                        result.push({
                            appointment,
                            service,
                        });
                    });
            });
            return result;
        },
        getBaseServices() {
            let result = [];
            this.course.appointments.forEach((appointment) => {
                result = result.concat(appointment.services.filter((service) => service.is_base));
            });
            return result;
        },
        getProcedures() {
            return this.getServicesOfGroup(CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.PROCEDURE);
        },
        getAssignedProcedures() {
            return this.getAssignedGroups(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PROCEDURES);
        },
        getPhysiotherapies() {
            return this.getServicesOfGroup(CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.PHYSIOTHERAPY);
        },
        getAssignedPhysiotherapies() {
            return this.getAssignedGroups(CONSTANTS.CARD_ASSIGNMENT.TYPES.ASSIGNED_PHYSIOTHERAPIES);
        },
        getServicesOfGroup(group) {
            let result = [];
            this.course.appointments.forEach((appointment) => {
                appointment.services
                    .filter((service) => service.specialization.service_group == group)
                    .forEach((service) => {
                        result.push({
                            appointment,
                            service,
                        });
                    });
            });
            return result;
        },
        getAssignedGroups(group) {
            let result = [];
            this.course.appointments.forEach((appointment) => {
                appointment.assignments
                    .filter((assignment) => assignment.recordable.type == group)
                    .forEach((assignment) => {
                        assignment.recordable[group].forEach(service => {
                            result.push({
                                appointment,
                                service,
                            });
                        });
                    });
            });
            return result;
        },
        getMedicines() {
            let result = [];
            this.course.appointments.forEach((appointment) => {
                appointment.medicines.forEach((medicine) => {
                    result.push({
                        appointment,
                        medicine,
                    });
                });
            });
            return result;
        },
        getIssuedMedicines() {
            let result = [];
            this.course.appointments.forEach((appointment) => {
                appointment.medicines.forEach((medicine) => {
                    if (medicine.issued_quantity > 0) {
                        result.push({
                            appointment,
                            medicine,
                        });
                    }
                });
            });
            return result;
        },
        getConsultations() {
             let result = [];
            this.course.appointments.forEach((appointment) => {
                appointment.consultation_records
                    .forEach((assignment) => {
                        assignment.recordable.consultations.forEach(consultation => {
                            result.push({
                                appointment,
                                consultation,
                            });
                        });
                    });
            });
            return result;
        },
        continueCourse() {
            if (!this.course.end) {
                return false;
            }

            this.$confirm(__('Вы уверены что хотите продлить курс?'), () => {
                this.course.end = null;
                this.course.save().then(() => {
                    this.$emit('courseChanged', this.course);
                    this.$info(__('Курс лечения успешно продлен'));
                }).catch((e) => {
                    this.$displayErrors(e);
                });
            });
        },
        edit() {
            this.$modalComponent(TreatmentCourseEditModal, {
                course: this.course
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, course) => {
                    this.course.start = course.start;
                    this.course.end = course.end;
                    this.$info(__('Курс лечения был успешно обновлён'));
                    dialog.close();
                },
            }, {
                header: __('Изменить курс лечения'),
                width: '540px',
            })
        }
    },
}
</script>
