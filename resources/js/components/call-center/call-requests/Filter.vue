<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :show-collapse-button="true"
        :start-collapsed="startCollapsed"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="5">
                <form-row
                    name="create_dates"
                    :label="__('Период создания заявки')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_start" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_end" />
                    </div>
                </form-row>
                <form-row
                    name="appointment_dates"
                    :label="__('Период записи пациентов')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="appointment_date_start" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="appointment_date_end" />
                    </div>
                </form-row>
                <form-row
                    name="recall_dates"
                    :label="__('Желаемый период прозвона')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="recall_period_start" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="recall_period_end" />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="5">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    property="clinic"
                    :filterable="true"
                    :label="__('Клиника')" />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    property="specialization"
                    :label="__('Специализация')" />
                <form-select
                    :entity="filter"
                    :options="doctors"
                    :clearable="true"
                    property="doctor"
                    :label="__('Врач')" />
            </el-col>
            <el-col :span="5">
                <form-select
                    :entity="filter"
                    :options="call_request_purposes"
                    :clearable="true"
                    property="purpose"
                    :label="__('Цель прозвона')" />
                <form-select
                    :entity="filter"
                    :options="call_results"
                    :clearable="true"
                    property="call_result"
                    :label="__('Результат звонка')" />
                <form-select
                    :entity="filter"
                    options="call_request_added"
                    :clearable="true"
                    property="added"
                    :label="__('Способ создания заявки')" />
            </el-col>
            <el-col :span="5">
                <form-select
                    :entity="filter"
                    options="call_request_status"
                    :clearable="true"
                    property="status"
                    :label="__('Статус заявки')" />
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="patient_phone_number"
                    :label="__('Номер телефона')"
                />
                <form-input-search
                    :entity="filter"
                    property="patient_email"
                    :clearable="true"
                    label="E-mail"
                />
            </el-col>
            <el-col :span="4">
                <form-date
                    :entity="filter"
                    :clearable="true"
                    :label="__('Рекомм. Дата записи')"
                    property="recommended_appointment_date" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import SpecializationRepository from '@/repositories/specialization';
import CallRequestPurposeRepository from '@/repositories/call-request/purpose';
import CallResultRepository from '@/repositories/calls/result';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        startCollapsed: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('call-requests'),
            }),
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('call-requests'),
            }),
            doctors: new EmployeeRepository({
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
            }),
            call_request_purposes: new CallRequestPurposeRepository(),
            call_results: new CallResultRepository(),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                created_start: null,
                created_end: null,
                appointment_date_start: null,
                appointment_date_end: null,
                recall_period_start: null,
                recall_period_end: null,
                clinic: null,
                specialization: null,
                doctor: null,
                purpose: null,
                call_result: null,
                added: null,
                status: null,
                patient_phone_number: null,
                patient_email: null,
                recommended_appointment_date: null,
                ...fromState,
            };
        },
    },
};
</script>
