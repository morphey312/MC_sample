<template>
    <div class="separate-form sections-wrapper">
        <content-placeholder v-if="loading" />
        <component
            :is="form"
            v-else
            ref="appointmentForm"
            v-loading="model.saving"
            :model="model"
            :day-sheet-data="daySheetData"
            :card-list="patientCardList"
            :discount-card="discountCard"
            :first-calc-discount-card="firstCalcDiscountCard"
            :refresh-discount-type="refreshDiscountType"
            :old-discount-card="oldDiscountCard"
            :statuses="statuses"
            :insurance-policy="insurancePolicy"
            :surgery-specialization="surgerySpecialization"
            @services-loaded="servicesLoaded">
            <div
                slot="buttons"
                class="dialog-footer text-right">
                <p class="inline-block input-label">
                    {{ __('Итого стоимость услуг и анализов:') }} {{ totalCost }}
                </p>
                <el-button
                    :disabled="emptyLaboratoryItems"
                    @click="biomaterialRegistration"
                    ref="biomaterialRegistrationButton">
                    {{ __('Регистрация биоматериала') }}
                </el-button>
                <el-button @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    :disabled="!$can('appointments.update-clinic') || !$canUpdate('appointments')"
                    type="primary"
                    @click.prevent="update">
                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </component>
    </div>
</template>

<script>
import Appointment from '@/models/appointment';
import AppointmentMixin from '@/components/appointments/mixin/appointment';
import FormActive from './FormActive.vue';
import biomaterialRegistration from './treatments/biomaterial-registration/Form.vue'

export default {
    mixins: [
        AppointmentMixin,
    ],
    props: {
        form: {
            type: Object,
            default: () => FormActive,
        },
    },
    data() {
        return {
            oldDiscountCard: null,
            firstCalcDiscountCard: {
                service: true,
                analysis: true,
            },
            refreshDiscountType: 0,
            discountCard: null,
            appointmentChanged: false,
            actionEdit: true,
            loading: true,
            servicesUpdated: false,
            servicesReady: false,
            laboratoryAnalyses: null,
        };
    },
    computed: {
        emptyLaboratoryItems() {
            return this.laboratoryAnalyses.length === 0;
        }
    },
    created() {
        this.listenAppointmentUpdated = ({data, user}) => {
            if (data.id === this.model.id && user !== this.$store.state.user.id) {
                if (!this.appointmentChanged) {
                    this.appointmentChanged = true;
                    this.$warning(__('Эта запись на прием была изменена другим пользователем! Пожалуйста, закройте и откройте форму заново, чтобы избежать потери данных.'));
                }
            }
        }
    },
    mounted() {
        this.$eventHub.$on('broadcast.appointment_updated', this.listenAppointmentUpdated);
        this.$eventHub.$on('updateAppointment', () => {
            this.loading = true;
            this.initAppointment()
        });
    },
    beforeDestroy() {
        this.$eventHub.$off('broadcast.appointment_updated', this.listenAppointmentUpdated);
        this.$eventHub.$off('updateAppointment');
    },
    beforeMount() {
        this.initAppointment()
    },
    methods: {
        initAppointment() {
            this.model = new Appointment({id: this.daySheetData.appointment.id});
            this.model.fetch([
                'patient_assigned_analyses',
                'patient_assigned_services',
                'patient_assigned_consultations',
                'patient_debts',
                'patient_issued_discount_cards',
                'patient_insurance_policies',
                'patient_legal_entity',
                'appointment_services_prices',
                'doctor',
                'insurance_policy',
                'card_specialization',

            ]).then((response)=> {
                this.loadStatuses();
                this.setTotalCost();
                this.$emit('patientSelected', this.model.patient);
                this.model.set('start', this.daySheetData.appointment.start.substring(0, 5));
                this.insurancePolicy = this.model.insurance_policy;

                if (!this.model.is_deleted) {
                    this.getDoctorAppointmentFirstPatients();
                }
                this.setLaboratoryAnalyses();
                let analysisContainers = this.model.analysisContainers;
                if (analysisContainers) {
                    this.model.set('service_containers', analysisContainers);
                }
                this.laboratoryAnalyses = this.getLaboratoryAnalysisItems();
                this.loading = false;
            });
        },
        setLaboratoryAnalyses() {
            this.laboratoryAnalyses = this.getLaboratoryAnalysisItems();
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
        update() {
            this.$clearErrors();

            // Alert if not all analysis have date_pass
            if (!this.allPassedAnalysis()) {
                return this.warnAnalysisDatePass();
            }

            if (this.appointmentChanged) {
                this.$error(__('Сохранение невозможно. Запись на прием была изменена другим пользователем'));
                return;
            }

            if (this.oldDiscountCard && this.discountCard && this.hasPaidServices()) {
                return this.warnDiscountCardWithPaidServices();
            }

            this.verifyPatientIsFirst().then((result) => {
                if (result == false) {
                    return this.warnPatientHasFirstAppointments();
                }

                // Alert that time is locked
                if (this.isTimeLocked()) {
                    return this.warnTimeLocked();
                }

                if (this.statusReasonIsEmpty()) {
                    return this.setStatusReason();
                }

                if (this.isPatientCardRequired()) {
                    return this.warnPatientCardRequired();
                }

                if (this.cardDontMatchSpecialization()) {
                    return this.warnCardMisMatch();
                }

                this.saveDelayReason();

                return this.save();
            });
        },
        save() {
            this.model.save({}, this.servicesUpdated).then(() => {
                this.$info(__('Запись была успешно обновлена'));
                this.$emit('updated', this.model);
                this.servicesUpdated = false;
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        servicesLoaded() {
            if (!this.servicesReady) {
                this.servicesReady = true;
                this.servicesUpdated = false;
                this.$watch('model.services', {
                    handler() {
                        this.servicesUpdated = true;
                    },
                    deep: true,
                });
            }
        },
        biomaterialRegistration() {
            if (!this.allPassedAnalysis()) {
                return this.warnAnalysisDatePass();
            }
            this.$refs.biomaterialRegistrationButton.$el.blur()
            this.$modalComponent(biomaterialRegistration, {
                model: this.model,
                labaratoryAnalyses: this.laboratoryAnalyses,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                save: (dialog, analyzes) => {
                    dialog.close();
                    this.laboratoryAnalyses = [];
                },
                deleteAnalysis: (dialog, row) => {
                    this.$eventHub.$emit('removeAnalysis', row);
                },
            }, {
                header: __('Регистрация био-материалов:') + ' ' + this.model.patient.full_name,
                width: '1300px',
                customClass: 'padding-0',
            });
        },
    }
}
</script>
