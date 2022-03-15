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
            <el-col :span="6">
                <form-row
                    name="appoitment_dates"
                    :label="__('Период желаемой записи')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="period_from" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="period_to" />
                    </div>
                </form-row>
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
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :repository="operators"
                    :clearable="true"
                    :multiple="true"
                    property="operator"
                    :label="__('Оператор')" />

                <form-select
                    :entity="filter"
                    :repository="operators"
                    :clearable="true"
                    :multiple="true"
                    property="creator"
                    :label="__('Кто создал')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')" />
                <form-select
                    :entity="filter"
                    options="wait_list_record_status"
                    :clearable="true"
                    property="status"
                    :label="__('Статус заявки')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="patient_appointment_is_first"
                    :clearable="true"
                    property="isFirstVisitCall"
                    :label="__('Тип пациента (звонок)')" />
                <form-select
                    :entity="filter"
                    options="patient_appointment_is_first"
                    :clearable="true"
                    property="isFirstVisitProcess"
                    :label="__('Тип пациента (запись)')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
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
                accessLimit: this.$isAccessLimited('wait-list-record'),
            }),
            operators: new EmployeeRepository({
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR},
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                period_from: null,
                period_to: null,
                created_start: null,
                created_end: null,
                operator: [],
                is_first: null,
                creator: [],
                clinic: [],
                status: null,
                ...fromState,
            };
        },
    },
};
</script>
