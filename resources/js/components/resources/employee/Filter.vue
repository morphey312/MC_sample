<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared" >
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
                <form-select
                    :entity="filter"
                    options="employee_status"
                    :clearable="true"
                    property="status"
                    :label="__('Статус')"
                />
                <div class="form-input-group">
                    <form-date
                        :entity="filter"
                        :clearable="true"
                        property="date_employment_start"
                        :label="__('Дата начала работы')" />
                    <form-date
                        :entity="filter"
                        :clearable="true"
                        property="date_employment_end"
                        :label="__('Дата окончания работы')" />
                </div>
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :options="positions"
                    :clearable="true"
                    :filterable="true"
                    property="position"
                    :label="__('Должность')"
                />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    property="specialization"
                    :label="__('Специализация')"
                />
                <form-select
                    :entity="filter"
                    :options="roles"
                    :multiple="true"
                    :clearable="true"
                    property="role"
                    :label="__('Группы доступа')"
                />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="full_name"
                    :label="__('ФИО')"
                />
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="phone"
                    :label="__('Телефон')"
                />
                <form-select
                    :entity="filter"
                    options="yes_no"
                    :clearable="true"
                    property="has_day_sheet"
                    :label="__('Наличие табеля')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import PositionRepository from '@/repositories/employee/position';
import SpecializationRepository from '@/repositories/specialization';
import RoleRepository from "@/repositories/role";
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('employees'),
            }),
            positions: new PositionRepository(),
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('employees'),
            }),
            roles: new RoleRepository()
        };
    },
    watch: {
        ['filter.clinic']: {
            immediate: true,
            handler() {
                this.specializations.setFilters(this.getSpecializationFilters());
            }
        },
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: [],
                status: null,
                position: null,
                specialization: null,
                full_name: null,
                phone: null,
                date_start_working: null,
                date_end_working: null,
                role: [],
                ...fromState,
            };
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filter.clinic,
            });
        },
    },
};
</script>
