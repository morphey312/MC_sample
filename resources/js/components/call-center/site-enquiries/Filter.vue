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
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="enquiry_status"
                    :clearable="true"
                    property="status"
                    :label="__('Статус заявки')" />
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="yes_no"
                    :clearable="true"
                    property="is_online"
                    :label="__('Онлайн оплата')" />
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
                accessLimit: this.$isAccessLimited('site-enquiries'),
            }),
            operators: new EmployeeRepository({
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR},
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                created_start: null,
                created_end: null,
                operator: [],
                clinic: [],
                status: null,
                ...fromState,
            };
        },
    },
};
</script>
