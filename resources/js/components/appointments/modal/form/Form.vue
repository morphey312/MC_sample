<template>
    <form>
        <alerts :patient="model.patient" :appointment="model" />
        <div class="grey">
            <div class="page-content-grey top-form-part label-block">
                <el-row :gutter="20">
                    <el-col :span="-15">
                        <form-select
                            :entity="model"
                            :options="patients"
                            property="patient_id"
                            :filterable="true"
                            :label="__('Пациент')"
                            :disabled="disabled" >
                            <template v-if="model.patient_id">
                                <span
                                    slot="label-addon">
                                    <a
                                        href="#"
                                        @click.prevent="editPatient">
                                        {{ __('Профиль') }}
                                    </a>
                                    <span> / </span>
                                    <a
                                        href="#"
                                        :disabled="!canChangePatient"
                                        @click.prevent="findPatient">
                                        {{ __('Выбрать пациента') }}
                                    </a>
                                </span>
                            </template>
                            <template v-else>
                                <a
                                    href="#"
                                    slot="label-addon"
                                    @click.prevent="findPatient"
                                    class="float-right">
                                    {{ __('Выбрать пациента') }}
                                </a>
                            </template>
                        </form-select>
                        <form-row
                            name="card_specialization_id"
                            :label="__('Карта')">
                            <el-select
                                v-model="card_specialization_id"
                                :filterable="true"
                                :clearable="clearableCard">
                                <el-option
                                    v-for="card in cardList"
                                    :key="card.id"
                                    :value="card.specialization_id"
                                    :label="card.value"/>
                            </el-select>
                        </form-row>
                        <form-switch
                            :entity="model"
                            options="patient_appointment_is_first"
                            property="is_first"
                            :label="__('Прием пациента')">
                        </form-switch>
                    </el-col>
                    <el-col :span="-15">
                        <form-select
                            :entity="model"
                            :options="clinics"
                            property="clinic_id"
                            :filterable="true"
                            :label="__('Клиника')"
                            :disabled="disabled" />
                        <form-select
                            :entity="model"
                            :options="specializations"
                            property="specialization_id"
                            :label="__('Специализация')"
                            :disabled="editBlocked" />
                        <form-select
                            :entity="model"
                            :options="doctorList"
                            property="doctor_id"
                            :label="__('Врач')"
                            :disabled="disabled">
                            <template slot="label-addon">
                                {{ doctorLabel }}
                            </template>
                        </form-select>
                    </el-col>
                    <el-col :span="-15">
                        <form-select
                            :entity="model"
                            :options="statuses"
                            property="appointment_status_id"
                            :label="__('Статус')"
                            :disabled="editBlocked" >
                            <template v-if="model.is_deleted && !editBlocked">
                                <span
                                    slot="label-addon"
                                    class="float-right">
                                    <a
                                        href="#"
                                        @click.prevent="showDeleteReasonModal"
                                    >
                                        {{ __('Ред. причину удаления') }}
                                    </a>
                                </span>
                            </template>

                        </form-select>
                        <form-select
                            :entity="model"
                            :options="patientCourses"
                            property="treatment_course_id"
                            :label="__('Курс лечения')" />
                        <form-select
                            :entity="model"
                            :repository="sources"
                            :clearable="true"
                            :min-query-len="0"
                            property="source_id"
                            :class="{'hightlight-action': $isMobileNavigator}"
                            :label="__('Источник информации')" />
                    </el-col>
                    <el-col :span="-15">
                        <form-date
                            :entity="model"
                            property="date"
                            :disabled="disabled"
                            :label="__('Дата приема')" />
                        <form-row
                            name="dates"
                            css-class="form-input-group"
                            label="" >
                            <form-time
                                :entity="model"
                                property="start"
                                :label="__('Начало приема')"
                                :disabled="editBlocked"
                                :picker-options="pickerOptions" />
                            <div class="form-input">
                                <div class="label-wrapper">
                                    <label class="input-label">{{ __('Длительность') }}</label>
                                </div>
                                <el-select
                                    v-model="appointmentDuration"
                                    @change="durationChanged"
                                    :disabled="editBlocked">
                                    <el-option
                                      v-for="item in minuteList"
                                      :key="item"
                                      :value="item"
                                    />
                                </el-select>
                            </div>
                        </form-row>
                        <form-select
                            :entity="model"
                            :repository="operators"
                            property="operator_id"
                            :label="__('Оператор')">
                            <suggest-info
                                v-if="operatorSuggestFrom"
                                slot="label-addon"
                                :suggest-from="operatorSuggestFrom" />
                        </form-select>
                    </el-col>
                    <el-col :span="-15">
                        <form-text
                            :entity="model"
                            property="comment"
                            :rows="7"
                            style="margin-bottom: 10px;"
                            :label="__('Комментарий')" />
                        <form-checkbox
                            v-if="$can('appointments.do-not-take-payment')"
                            :entity="model"
                            property="do_not_take_payment"
                            :label="__('Оплату не брать')"
                            />
                    </el-col>
                </el-row>
            </div>
        </div>
        <section class="p-0 appointment-form-switcher">
            <treatment-block
                :appointment-data="appointmentData"
                :model="model"
                :enquiry="enquiry"
                :specialization="specialization"
                :insurance-policy="insurancePolicy"
                @assigned-add="assignedAdd"
                @services-loaded="servicesLoaded" />
        </section>
        <div>
            <slot name="buttons"></slot>
        </div>
    </form>
</template>

<script>
import TreatmentBlock from './treatments/Treatment.vue';
import Alerts from './Alerts.vue';
import SuggestInfo from './SuggestInfo.vue';
import CONSTANTS from '@/constants';
import FormDelete from '@/components/appointments/modal/form/FormDelete.vue';
import specialization from "@/repositories/specialization";

export default {
    components: {
        TreatmentBlock,
        Alerts,
        SuggestInfo,
    },
    props: {
        operatorSuggestFrom: {
            type: Object,
            default: null,
        },
        model: Object,
        sources: Object,
        patients: Array,
        cardList: Array,
        clinics: Array,
        specializations: Array,
        doctorList: Array,
        operators: Object,
        patientCourses: Array,
        statuses: Array,
        pickerOptions: Object,
        clearableCard: Boolean,
        appointmentData: Object,
        doctorLabel: String,
        selectedDuration: [String, Number],
        minuteList: Array,
        editBlocked: {
            type: Boolean,
            default: false,
        },
        enquiry: Object,
        insurancePolicy: Object,
    },
    data() {
        return {
            disabled: true,
            appointmentDuration: this.selectedDuration,
            card_specialization_id: this.model ? this.model.card_specialization_id : null,
        }
    },
    watch: {
        ['model.card_specialization_id'](val) {
            this.card_specialization_id = val;
        },
        card_specialization_id(val) {
            if (this.model.service_containers && val != this.model.card_specialization_id) {
                let existAssignedAnalysis = this.model.service_containers.filter((analysis) => {
                    return _.isFilled(analysis.card_assignment_id);
                });
                if (existAssignedAnalysis.length > 0) {
                    this.$confirm(__('В записи есть назначенные анализы. Вы действительно хотите изменить карту пациента?'), () => {
                        this.model.card_specialization_id = val;
                    },  {
                        cancelled: ()=> {
                            this.card_specialization_id = this.model.card_specialization_id;
                        }
                    })
                } else {
                    this.model.card_specialization_id = this.card_specialization_id;
                }
            } else {
                this.model.card_specialization_id = this.card_specialization_id;
            }
        }
    },
    computed: {
        specialization() {
            if (this.specializations.length > 0) {
                return this.specializations.find((specialization) => {
                    return specialization.id === this.model.specialization_id
                })
            }
        },
        canChangePatient(){
            let disabledStatuses = [
                CONSTANTS.APPOINTMENT.STATUSES.COMPLETED,
                CONSTANTS.APPOINTMENT.STATUSES.ANALYSES_READY,
                CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_DOCTOR
            ]

            let status = this.statuses.filter(x => x.id === this.model.appointment_status_id)[0];

            return this.$can('appointments.change-patient-override') ||
                  disabledStatuses.indexOf(_.get(status, 'system_status')) === -1;
        }
    },
    mounted() {
        this.$watch('selectedDuration', () => {
            this.appointmentDuration = this.selectedDuration;
        });
    },
    methods: {
        editPatient() {
            this.$emit('edit-patient');
        },
        showDeleteReasonModal(){
            this.$modalComponent(FormDelete, { daySheetData: { appointment: this.model }, edit:true },
                {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    deleted: (dialog, id) => {
                        dialog.close();
                    },
                },
                {
                    header: __('Причина удаления записи'),
                    width: '400px',
                })
        },
        findPatient() {
            if(this.canChangePatient){
                this.$emit('find-patient');
            }else {
                return this.$error(__('У вас нет прав для выполнения данной операции'));
            }
        },
        durationChanged(val) {
            this.$emit('duration-changed', val);
        },
        assignedAdd(assigned){
            this.$emit('assigned-add', assigned);
        },
        servicesLoaded() {
            this.$emit('services-loaded');
        },
    },
};
</script>
