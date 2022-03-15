<template>
    <search-filter
        :model="filter"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared"
        :show-submit-button="true"
        :show-clear-button="true">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-input
                    :entity="filter"
                    property="fullName"
                    :clearable="true"
                    :label="__('ФИО')"
                />
                <form-select
                    :entity="filter"
                    :options="positions"
                    :clearable="true"
                    property="position"
                    :label="__('Должность')"
                />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    property="specialization"
                    :label="__('Специализация')"
                />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :options="statuses"
                    :clearable="true"
                    property="status"
                    :label="__('Статус')"
                />
                <form-checkbox
                    :entity="filter"
                    property="hasSpecializations"
                    :label="__('Только сотрудники, ведущие прием по специализации')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import PositionRepository from '@/repositories/employee/position';
import SpecializationRepository from '@/repositories/specialization';
import CONSTANTS from '@/constants';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('day-sheets'),
            }),
            statuses: this.$handbook.getOptions('employee_status'),
            positions: new PositionRepository(),
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('day-sheets'),
                filters: this.getSpecializationsFilters(),
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                    clinic: null,
                    status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                    position: null,
                    specialization: null,
                    fullName: '',
                    hasSpecializations: true,
                    hasDaySheet: 1,
                    ...fromState,
                };
        },
        changed(filters) {
            this.$emit('changed', filters);
        },
        cleared() {
            if(_.isFunction(this.initFilter)){
                this.initFilter(true);
            }
            this.$emit('changed', {
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                hasSpecializations: true,
            });
        },
        getSpecializationsFilters() {
            return _.onlyFilled({
                clinic: this.filter ? this.filter.clinic : null,
            });
        },
    },
    watch: {
        ['filter.clinic']() {
            this.specializations.setFilters(this.getSpecializationsFilters());
        },
    },
}
</script>
