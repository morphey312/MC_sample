<template>
    <search-filter
        :model="filter"
        :show-clear-button="true"
        :show-submit-button="true"
        :auto-search="false"
        @cleared="cleared"
        @changed="changed">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    property="clinic"
                    :filterable="true"
                    :clearable="true"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="insuranceCompanies"
                    property="insurer"
                    :clearable="true"
                    :label="__('Страховая компания')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="date_start"
                            :editable="false" />
                        <form-date
                            :entity="filter"
                            property="date_end"
                            :editable="false" />
                    </div>
                </form-row>
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from "@/repositories/clinic";
import InsuranceCompanyRepository from "@/repositories/insurance-company";

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('insurance-acts'),
            }),
            insuranceCompanies: new InsuranceCompanyRepository({
                accessLimit: this.$isAccessLimited('insurance'),
            }),
        }
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: null,
                insurer: null,
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                ...fromState,
            };
        },
        getInsurerFilters() {
            return _.onlyFilled({
                    clinic: this.filter.clinic,
                });
        },
    },
    watch: {
        ['filter.clinic']() {
            this.insuranceCompanies.setFilters(this.getInsurerFilters());
        },
    },
};
</script>
