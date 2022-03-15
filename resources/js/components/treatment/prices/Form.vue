<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :disabled="!isAmendable"
                    :entity="model"
                    :options="setTypes"
                    property="set_id"
                    :label="__('Набор цен')"
                />
                <form-select
                    :disabled="!isAmendable"
                    :entity="model"
                    :multiple="true"
                    :options="clinics"
                    :filterable="true"
                    property="clinics"
                    :label="__('Клиники, в которых действует тариф')"
                />

            </el-col>
            <el-col :span="8">
                <form-date
                    :disabled="!isAmendable"
                    :entity="model"
                    property="date_from"
                    :label="__('Дата начала действия тарифа')"
                />
                <form-select
                    :entity="model"
                    :options="currencies"
                    property="currency"
                    :label="__('Валюта')"
                />
            </el-col>
            <el-col :span="8">
                <form-date
                    :disabled="!isAmendable"
                    :entity="model"
                    property="date_to"
                    :label="__('Дата окончания действия тарифа')"
                />
                <div class="form-input-group">
                    <form-input
                        :entity="model"
                        property="cost"
                        :label="__('Стоимость')"
                    />
                    <form-input
                        :entity="model"
                        :disabled="isExternal"
                        property="self_cost"
                        :label="__('Себестоимость')"
                    />
                </div>
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import PriceSetRepository from '@/repositories/price/set';
import CONSTANTS from '@/constants';

export default {
    props: {
        model: Object,
        filter: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
        priceSets: Array,
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
                filters: this.filter,
            }),
            currencies: this.$handbook.getOptions('currency'),
            setTypes: [...this.priceSets],
        };
    },
    computed: {
        isAmendable() {
            return this.model.isNew()
                || this.$can('analysis-prices.amend') && this.model.service_type === CONSTANTS.PRICE.SERVICE_TYPE.ANALYSIS
                || this.$can('service-prices.amend') && this.model.service_type === CONSTANTS.PRICE.SERVICE_TYPE.SERVICES;
        },
        isExternal() {
            return _.isFilled(this.model.external_price_id)
        }
    },
}
</script>
