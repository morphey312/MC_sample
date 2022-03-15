<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared" >
        <el-row :gutter="20">
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="name"
                    :label="__('Название')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="clinic"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    property="specialization"
                    :label="__('Специализация')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="yes_no"
                    :clearable="true"
                    property="is_active"
                    :label="__('Активный')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
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
            specializations: new SpecializationRepository(),
        };
    },
    watch: {
        ['filter.clinic'](value) {
            this.specializations.setFilters({
                clinic: value,
            }, true);
        },
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: null,
                specialization: null,
                name: null,
                sipNumber: null,
                is_active: null,
                ...fromState,
            };
        },
    },
}
</script>
