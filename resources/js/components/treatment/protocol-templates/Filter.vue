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
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="name"
                    :label="__('Название')"
                />
            </el-col>
            <el-col :span="8">
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
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import PositionRepository from '@/repositories/employee/position';
import SpecializationRepository from '@/repositories/specialization';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('protocol-templates')
            }),
            specializations: new SpecializationRepository({
                status: 1,
                limitClinics: this.$isAccessLimited('protocol-templates')
            }),
        };
    },
    watch: {
        ['filter.clinic']: {
            handler() {
                this.specializations.setFilters(this.getSpecializationFilters());
            }
        },
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: null,
                name: null,
                specialization: null,
                ...fromState,
            };
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                status: 1,
                clinic: this.filter.clinic,
            });
        },
    },
};
</script>
