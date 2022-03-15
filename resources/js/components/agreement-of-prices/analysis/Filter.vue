<template>
    <search-filter
        ref="filter"
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="name"
                    :label="__('Название')"
                    error-prefix="filter"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="laboratories"
                    :clearable="true"
                    :filterable="true"
                    property="laboratory"
                    :label="__('Лаборатория')"
                />
                <form-row
                    name="price_exists_dates"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="price_date_from"
                            :disabled="dateDisable"
                            :clearable="true"
                        />
                        <form-date
                            :entity="filter"
                            property="price_date_to"
                            :options="pickerOptionsTo"
                            :disabled="dateDisable"
                            :clearable="true"
                        />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="laboratory_code"
                    :label="__('Код анализа лаборатории')"
                />
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="clinic_code"
                    :label="__('Код анализа клиники')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="price_exists_for_price_agreement_act"
                    property="price_exists"
                    :label="__('Наличие тарифа')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import BaseFilterMixin from '@/mixins/treatment/filter/base-filter';
import AnalysisFilterMixin from '@/mixins/treatment/filter/analysis-filter';
import ClinicRepository from "@/repositories/clinic";

export default {
    mixins: [
        FilterMixin,
        BaseFilterMixin,
        AnalysisFilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository(),
        };
    },
};
</script>
