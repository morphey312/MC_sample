<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="priceSets"
                    :clearable="false"
                    property="set_id"
                    :label="__('Страховая')"
                />
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="name"
                    :label="__('Название услуги')"
                    error-prefix="filter"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="availableClinics"
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
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="destinations"
                    :clearable="true"
                    property="payment_destination"
                    :label="__('Назначение платежа')"
                />
                <form-select
                    :entity="filter"
                    options="service_status"
                    property="disabled"
                    :clearable="true"
                    :label="__('Показывать услуги')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="price_exists"
                    property="price_exists"
                    :label="__('Наличие тарифа')"
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
                            :disabled="dateDisable"
                            :clearable="true"
                        />
                    </div>
                </form-row>
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import BaseFilterMixin from '@/mixins/treatment/filter/base-filter';
import ServiceFilterMixin from '@/mixins/treatment/filter/service-filter';
import InsuranceFilterMixin from './mixins/filter';

export default {
    mixins: [
        FilterMixin,
        BaseFilterMixin,
        ServiceFilterMixin,
        InsuranceFilterMixin,
    ],
};
</script>
