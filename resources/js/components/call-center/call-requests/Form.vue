<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :options="purposes"
                    property="call_request_purpose_id"
                    :label="__('Цель прозвона')"
                />
                <form-row
                    name="range_dates"
                    :label="__('Желаемый период для прозвона')"
                    :required="true">
                    <div class="form-input-group">
                        <form-date
                            :entity="model"
                            property="recall_from" />
                        <form-date
                            :entity="model"
                            property="recall_to" />
                    </div>
                </form-row>
                <form-select
                    :entity="model"
                    :repository="patients"
                    :clearable="true"
                    property="patient_id"
                    :label="__('Пациент')"
                />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :options="clinics"
                    property="clinic_id"
                    :filterable="true"
                    :label="__('Клиника')"
                />
                <form-select
                    :entity="model"
                    :options="specializations"
                    property="specialization_id"
                    :label="__('Специализация')"
                />
                <form-select
                    :entity="model"
                    :options="doctors"
                    property="doctor_id"
                    :filterable="true"
                    :label="__('Врач')"
                />
            </el-col>
            <el-col :span="8">
                <form-text
                    :entity="model"
                    :rows="3"
                    property="comment"
                    :label="__('Примечание')"
                />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>


<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import PatientRepository from '@/repositories/patient';
import CallRequestPurposeRepository from '@/repositories/call-request/purpose';
import CONSTANTS from '@/constants';

export default {
    props: {
        model: {
            type: Object
        },
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
            }),
            purposes: new CallRequestPurposeRepository(),
            patients: new PatientRepository(),
            specializations: new SpecializationRepository({
                limitClinics: this.limitClinics,
                filters: this.getSpecializationsFilters(),
            }),
            doctors: new EmployeeRepository({
                limitClinics: this.limitClinics,
                filters: this.getDoctorsFilters(),
            }),
            period: {},
        }
    },
    methods: {
        getSpecializationsFilters() {
            return _.onlyFilled({
                clinic: this.model.clinic_id,
            });
        },
        getDoctorsFilters() {
            return _.onlyFilled({
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                specialization: this.model.specialization_id,
                clinic: this.model.clinic_id,
            });
        },
    },
    watch: {
        ['model.clinic_id'](val) {
            this.specializations.setFilters(this.getSpecializationsFilters());
        },
        ['model.specialization_id'](val) {
            this.doctors.setFilters(this.getDoctorsFilters());
        },
    },
};
</script>
