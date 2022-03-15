<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :button-submit-text="__('Показать')"
        :auto-search="false"
        @changed="changed">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    property="clinic"
                    :filterable="true"
                    :label="__('Клиника')" />
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
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :multiple="true"
                    property="specialization"
                    :label="__('Специализация карты')" />
            </el-col>
            <el-col :span="6">
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
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            }),
            specializations: new SpecializationRepository(),
        }
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: null,
                specialization: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
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
     watch: {
        ['filter.currency']() {
            this.clinics.setFilters(this.getClinicFilters());
        },
        ['filter.clinic']() {
            this.specializations.setFilters(this.getSpecializationFilters());
        },
    },
};
</script>
