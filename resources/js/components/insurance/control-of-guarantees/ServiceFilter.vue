<template>
    <search-filter
        :model="filter"
        :show-clear-button="true"
        :show-submit-button="true"
        :auto-search="false"
        :button-submit-text="__('Сформировать список')"
        @cleared="cleared"
        @changed="changed">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :multiple="true"
                    :clearable="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :multiple="true"
                    :options="insuranceCompanies"
                    property="with_policy.insurer"
                    :label="__('Страховая компания')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="with_policy.date_start"
                            :editable="false" />
                        <form-date
                            :entity="filter"
                            property="with_policy.date_end"
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
    beforeMount() {
        if (this.$can('execution-act.all-clinics')) {
            this.clinics.setFilters(this.getInsurerFilters());
        }
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('insurance-acts'),
                filters: {
                    id: this.$store.state.user.clinics
                }
            }),
            insuranceCompanies: new InsuranceCompanyRepository({
                accessLimit: this.$isAccessLimited('insurance'),
            }),
        }
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: [],
                with_policy: {
                    insurer: [],
                    date_start: this.$moment().format('YYYY-MM-DD'),
                    date_end: this.$moment().format('YYYY-MM-DD'),
                    skip_medicines: true,
                },
                is_deleted: false,
                by_policy: false,
                debt_service: true,
                ...fromState,
            };
        },
        getInsurerFilters() {
            return _.onlyFilled({
                    clinic: this.filter.clinic,
                });
        },
        getCliniclters() {
            return _.onlyFilled({
                id: this.$store.state.user.clinics,
            });
        }
    },
     watch: {
        ['filter.clinic']() {
            this.insuranceCompanies.setFilters(this.getInsurerFilters());
        },
    },
};
</script>
