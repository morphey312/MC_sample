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
            <el-col :span="8">
                <div class="form-input-group">
                    <form-select
                        :entity="filter"
                        :repository="operators"
                        :clearable="true"
                        property="operator"
                        :label="__('Оператор')" />
                    <form-input-search
                        :entity="filter"
                        :clearable="true"
                        property="sip_number"
                        label="SIP" />
                </div>
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="8">
                <form-row
                    name="dates"
                    :label="__('Период дат')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_from" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_to" />
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    :clearable="true"
                    options="call_log_type"
                    property="type"
                    :label="__('Тип')" />
            </el-col>
            <el-col :span="8">
                <filter-patient
                    :entity="filter"
                    patient-name-prop="patient_name"
                    patient-id-prop="patient"
                    :label="__('Пациент')"
                    @selected="focusOnPatient" />
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="phone_number"
                    :label="__('Номер телефона')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS  from '@/constants';

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
                accessLimit: this.$isAccessLimited('call-logs'),
            }),
            operators: new EmployeeRepository({
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR},
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                operator: null,
                sip: null,
                date_from: null,
                date_to: null,
                type: null,
                clinic: null,
                patient: null,
                patient_name: null,
                phone_number: null,
                ...fromState,
            };
        },
        focusOnPatient(patient) {
            this.$nextTick(() => {
                this.initFilter({
                    patient: patient.id,
                });
            });
        },
    },
};
</script>
