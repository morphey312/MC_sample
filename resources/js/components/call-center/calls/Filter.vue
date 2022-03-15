<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :repository="operators"
                    property="operator"
                    :clearable="true"
                    :multiple="true"
                    :label="__('Оператор')"
                />
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <filter-patient
                    :entity="filter"
                    patient-name-prop="patient_name"
                    patient-id-prop="patient"
                    :label="__('Пациент')" />
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="patient_phone_number"
                    :label="__('Номер телефона')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Дата создания')">
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
                <form-select
                    :entity="filter"
                    :clearable="true"
                    options="patient_appointment_is_first"
                    property="is_first"
                    :label="__('Тип')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="delete_status"
                    :clearable="true"
                    property="is_deleted"
                    :label="__('Показывать')" />
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
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('calls') && this.$isAccessLimited('appointments'),
            }),
            operators: new EmployeeRepository(),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                operator: null,
                clinic: [],
                date_start: null,
                date_end: null,
                created_start: null,
                created_end: null,
                is_first: null,
                patient_name: null,
                patient_phone_number: null,
                is_deleted: null,
                ...fromState,
            };
        },
    },
};
</script>
