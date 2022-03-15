<template>
    <div class="separate-form sections-wrapper">
        <appointment-form
            v-if="!loading"
            ref="appointmentForm"
            v-loading="model.loading || model.saving"
            :model="model"
            :day-sheet-data="daySheetData"
            :card-list="patientCardList"
            :statuses="statuses"
            :operator-suggest-from="operatorSuggestFrom"
            :discount-card="discountCard"
            :refresh-discount-type="refreshDiscountType"
            :first-calc-discount-card="firstCalcDiscountCard"
            :old-discount-card="oldDiscountCard"
            :insurance-policy="insurancePolicy"
            :enquiry="enquiry"
            :surgery-specialization="surgerySpecialization">
            <div
                slot="buttons"
                class="dialog-footer text-right">
                <p class="inline-block input-label">
                    {{ __('Итого стоимость услуг и анализов:') }} {{ totalCost }}
                </p>
                <el-button @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    @click.prevent="create">
                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </appointment-form>
        <content-placeholder
            v-else
        />
    </div>
</template>
<script>
import AppointmentForm from './FormActive.vue';
import Appointment from '@/models/appointment';
import AppointmentMixin from '@/components/appointments/mixin/appointment';
import CONSTANTS from '@/constants';
import CallRepository from '@/repositories/call';
import AppointmentRepository from '@/repositories/appointment';
import SiteEnquiryRepository from "@/repositories/site-enquiry";

export default {
    components: {
        AppointmentForm,
    },
    mixins: [
        AppointmentMixin,
    ],
    data() {
        return {
            operatorSuggestFrom: null,
            firstCalcDiscountCard: {
                service: true,
                analysis: true,
            },
            oldDiscountCard: null,
            refreshDiscountType: 0,
            discountCard: null,
            enquiry: null,
            enquiries: [],
            loading: true,
        };
    },
    watch: {
        ['model.patient_id'](val) {
            this.suggestOperator();
        },
        ['model.is_first'](val) {
            this.suggestOperator();
        },
        ['model.specialization_id'](val) {
            this.suggestOperator();
        },
    },
    beforeMount() {
        this.preloadPatient().then(patient => {
            let appointment = new Appointment();
            this.model = appointment.castToInstance(Appointment, this.daySheetData.appointment);
            this.loadStatuses();
            this.model.set('doctor', this.daySheetData.doctor);
            let processState = (this.$store.state && this.$store.state.processState) ? this.$store.state.processState : null;

            if (processState && processState.processLog && processState.processLog.enquiry) {
                this.getEnquiries(processState.processLog.enquiry).then(() => {
                    this.loading = false;
                });
            } else {
                this.loading = false;
            }
            this.getDoctorAppointmentFirstPatients();
            this.$emit('patientSelected', patient);
        });
    },
    methods: {
        preloadPatient() {
            if (this.daySheetData.appointment.patient.id) {
                return this.fetchPatient(this.daySheetData.appointment.patient);
            }
            return Promise.resolve(null);
        },
        getEnquiries(enquiryId = null) {
            let activeEnquiries = new SiteEnquiryRepository();
            return activeEnquiries.fetch(this.getEnquiryFilters(enquiryId), null, ['service_prices']).then(response => {
                if (response && response.rows.length !== 0) {
                    if (enquiryId) {
                        this.enquiry = response.rows.find(row => row.id == enquiryId);
                        if (this.enquiry && this.enquiry.clinic_id != this.model.clinic_id && this.enquiry.has_available_services) {
                            this.$info(__('Заявка с сайта отправлена в другую клинику. В случае записи в эту клинику, необходимо оформить возврат'));
                        }
                        this.enquiries = response.rows.filter(row => row.id != enquiryId);

                        if (this.enquiry.category === CONSTANTS.SITE_ENQUIRY.TYPE.COVID_TEST) {
                            this.model.comment = this.enquiry.notes;
                        }
                    }

                    if (this.enquiries.length !== 0) {
                        let services = this.enquiries.reduce((list, item) => {
                            if (item.has_available_services) {
                                list = [...list, ...item.service_list, ...item.analysis_list];
                            }
                            return list;
                        }, []);

                        if (services.length !== 0) {
                            this.enquiry.set('services', [
                                ...(this.enquiry.payment_status === CONSTANTS.SITE_ENQUIRY.PAYMENT_STATUS.PAID && this.enquiry.services ? this.enquiry.services : []),
                                ...services,
                            ]);
                        }
                    }
                }
                return Promise.resolve();
            });
        },
        getEnquiryFilters(enquiryId = null) {
            let patientPhone = (this.model && this.model.patient)
                ? (this.model.patient.primary_phone_number || this.model.patient.secondary_phone_number)
                : null;

            return {
                or: [
                    {
                        id: enquiryId,
                    },
                    ...(_.isFilled(patientPhone) ? [
                        {
                            phone_number: patientPhone,
                            clinic: this.model.clinic_id,
                            status: CONSTANTS.SITE_ENQUIRY.STATUS.NOT_PROCESSED,
                            payment_status: CONSTANTS.SITE_ENQUIRY.PAYMENT_STATUS.PAID,
                            category: [
                                CONSTANTS.SITE_ENQUIRY.TYPE.APPOINTMENT,
                                CONSTANTS.SITE_ENQUIRY.TYPE.TOMOGRAPHY
                            ],
                        }
                    ] : [])
                ],
            }
        },
        create() {
            this.$clearErrors();

            // Alert if not all analysis have date_pass
            if (!this.allPassedAnalysis()) {
                return this.warnAnalysisDatePass();
            }

            // Alert model card_specialization_id required
            if (this.isPatientCardRequired()) {
                return this.warnPatientCardRequired();
            }

            // Alert that time is locked
            if (this.isTimeLocked()) {
                return this.warnTimeLocked();
            }

            // Alert current appointment status requires set reason
            if (this.statusReasonIsEmpty()) {
                return this.setStatusReason();
            }

            // Alert missmatch card specialization, appointment specialization and save
            if (this.cardDontMatchSpecialization()) {
                return this.warnCardMisMatch();
            }

            // Save if patient is first for specialization
            if (!this.appointmentNewAndIsFirst()) {
                return this.save();
            }

            let specialization = this.getSpecializationIsFirst();

            if (!specialization) {
                return this.save();
            }

            // Verify appointment is first count for specialization
            let warning = this.verifySpecializationFirstTotal(specialization);

            if (warning == null) {
                return this.save();
            }

            if (warning.is_hard == 1) {
                this.$error(warning.message);
            } else {
                this.$confirm(warning.message, () => {
                    this.save();
                });
            }
        },
        save() {
            this.model.save().then((response) => {
                this.updateEnquiry().then(() => {
                    this.$info(__('Запись была успешно создана'));
                    this.$emit('created', this.model);
                });
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        updateEnquiry() {
            if (this.enquiry && this.enquiry.has_available_services && this.enquiry.clinic_id == this.model.clinic_id) {
                let modelServices = this.getModelServices();
                let modelAnalyses = this.getModelAnalyses();

                let enquiryServices = [...this.enquiry.service_list, ...this.enquiry.analysis_list].filter(service => {
                    if (service.service_type === CONSTANTS.SITE_ENQUIRY.SERVICE_TYPE.SERVICE) {
                        return modelServices.indexOf(service.service_id) !== -1;
                    } else if (service.service_type === CONSTANTS.SITE_ENQUIRY.SERVICE_TYPE.ANALYSIS) {
                        return modelAnalyses.indexOf(service.service_id) !== -1;
                    }
                    return false;
                }).map(service => {
                    service.appointment_id = this.model.id;
                    return service;
                });

                if (enquiryServices.length != 0) {
                    return this.enquiry.updateServices(enquiryServices);
                }
                return Promise.resolve();
            }
            return Promise.resolve();
        },
        getModelServices() {
            return this.model.regular_services.map((item) => item.service_id);
        },
        getModelAnalyses() {
            return this.model.analyses_results.map((item) => item.analysis_id);
        },
        suggestOperator() {
            this.resetOperator();
            return;
            //Temporary not in use
            let isFirst = this.model.is_first === CONSTANTS.APPOINTMENT.TYPES.FIRST;
            let specialization = this.model.specialization_id;
            let patient = this.model.patient_id;
            let formChanged = () => (this.model.is_first !== CONSTANTS.APPOINTMENT.TYPES.FIRST ||
                specialization !== this.model.specialization_id || patient !== this.model.patient_id);
            if (isFirst && _.isFilled(specialization) && _.isFilled(patient)) {
                this.searchFailedAppointment(patient, specialization).then((result) => {
                    if (!formChanged()) {
                        if (result.length !== 0) {
                            this.model.operator_id = result[0].operator_id;
                            this.operatorSuggestFrom = result[0];
                        } else {
                            this.searchRelatedCall(patient, specialization).then((result) => {
                                if (!formChanged()) {
                                    if (result.length !== 0) {
                                        this.model.operator_id = result[0].employee_id;
                                        this.operatorSuggestFrom = result[0];
                                    } else {
                                        this.resetOperator();
                                    }
                                }
                            });
                        }
                    }
                });
            } else {
                this.resetOperator();
            }
        },
        resetOperator() {
            this.model.operator_id = this.$store.state.user.employee_id;
            this.operatorSuggestFrom = null;
        },
        searchFailedAppointment(patient, specialization) {
            let appointments = new AppointmentRepository();
            return appointments.fetch({
                is_first: CONSTANTS.APPOINTMENT.TYPES.FIRST,
                patient,
                specialization,
                or: {
                    is_deleted: 1,
                    system_status: CONSTANTS.APPOINTMENT.STATUSES.DID_NOT_COME,
                },
                created_start: this.$moment().subtract(1, 'years').format('YYYY-MM-DD'),
                operator_status: [
                    CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                    CONSTANTS.EMPLOYEE.STATUSES.PROBATION,
                ],
            }, [{
                field: 'date_time',
                direction: 'desc',
            }], ['default', 'operator'], 1, 1).then((result) => {
                return result.rows;
            });
        },
        searchRelatedCall(patient, specialization) {
            let calls = new CallRepository();
            return calls.fetch({
                is_first: CONSTANTS.CALL.REQUEST_TYPES.FIRST,
                patient,
                specialization,
                created_start: this.$moment().subtract(1, 'years').format('YYYY-MM-DD'),
                operator_status: [
                    CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                    CONSTANTS.EMPLOYEE.STATUSES.PROBATION,
                ],
            }, [{
                field: 'date_time',
                direction: 'desc',
            }], null, 1, 1).then((result) => {
                return result.rows;
            });
        },
        setDiscountCard(card, refreshDiscountType = 0) {
            this.oldDiscountCard = this.discountCard;
            this.refreshDiscountType = refreshDiscountType && !this.oldDiscountCard ? refreshDiscountType : 0;

            this.$discountData.oldDiscountCard = this.discountCard;
            this.$discountData.discountCard = card;
            this.$discountData.refreshDiscountType = refreshDiscountType;

            this.discountCard = card;
            this.model.discount_card_id = card ? card.id : null;
            this.$eventHub.$emit('discountSelected');
        },
    }
}
</script>
