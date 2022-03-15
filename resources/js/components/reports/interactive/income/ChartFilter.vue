<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :button-submit-text="__('Показать')"
        :auto-search="false"
        @changed="changed">
        <el-row :gutter="20">
            <el-col :span="5">
                <form-switch
                    :entity="filter"
                    :options="filterTypes"
                    property="filterType"
                    :label="__('Показать по:')"
                />
            </el-col>
            <el-col :span="4">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :multiple="true"
                    :clearable="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="5">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :multiple="true"
                    :clearable="true"
                    property="specialization"
                    :label="__('Специализация карты')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Дата')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="date_start"
                            :clearable="true" />
                        <form-date
                            :entity="filter"
                            property="date_end"
                            :clearable="true" />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="3">
                <form-select
                    :entity="filter"
                    options="currency"
                    property="currency"
                    :label="__('Валюта')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import ProxyRepository from '@/repositories/proxy-repository';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        filterTypes: Array,
    },
    data() {
        let specializations = new ProxyRepository(() => {
            let options = specializations.getOptions();
            if (options.filters != undefined && options.filters.clinic.length !== 0) {
                return (new SpecializationRepository).fetchList(options.filters);
            }
            return (new SpecializationRepository).fetchList();
        });
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            }),
            specializations,
        }
    },
    watch: {
        ['filter.currency']() {
            this.clinics.setFilters(this.getClinicFilters());
        },
        ['filter.clinic']() {
            this.specializations.setFilters(this.getSpecializationFilters());
        },
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: [],
                specialization: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                is_deleted: false,
                currency: CONSTANTS.CURRENCY.UAH,
                ...fromState,
            };
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filter.clinic,
            });
        },
        getClinicFilters() {
            let currency = this.filter.currency === CONSTANTS.CURRENCY.UAH ? null : this.filter.currency;
            return _.onlyFilled({
                currency: currency,
            });
        },
    },
};
</script>
