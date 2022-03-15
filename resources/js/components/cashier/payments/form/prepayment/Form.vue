<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                :entity="model"
                :options="clinics"
                property="clinic_id"
                :disabled="readonly"
                :filterable="true"
                :label="__('Клиника')" />
                <form-select
                    :entity="model"
                    :options="specializations"
                    :clearable="true"
                    :disabled="model.used == true"
                    property="specialization_id"
                    :label="__('Специализация')"/>
            </el-col>
            <el-col :span="12">
                <form-select
                    :entity="model"
                    :options="cashboxList"
                    property="cashbox_id"
                    :disabled="readonly"
                    :label="__('Форма оплаты')"
                />
            </el-col>
        </el-row>
        <el-row>
            <form-input
                :entity="model"
                property="amount"
                :disabled="readonly"
                :label="__('Сумма, грн')"
            />
            <form-select
                :entity="model"
                property="service_id"
                :repository="services"
                :clearable="true"
                :disabled="model.used == true"
                :min-query-len="0"
                :label="__('Услуга')"
            />
            <form-text
                :entity="model"
                property="comment"
                :disabled="readonly"
                :rows="4"
                :label="__('Примечание')"
            />
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>
<script>
import ClinicRepository from '@/repositories/clinic';
import ServiceRepository from '@/repositories/service';
import ProxyRepository from '@/repositories/proxy-repository';
import CONSTANTS from '@/constants';
import SpecializationRepository from "@/repositories/specialization";

export default {
    props: {
        model: Object,
        cashboxList: {
            type: Array,
            default: () => [],
        },
        readonly: {
            type: Boolean,
            default: false,
        },
        date: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('payments'),
            }),
            specializations: new SpecializationRepository({
                filters:  _.onlyFilled({
                    status: 1,
                    clinic: this.model.clinic_id
                }),
            }),
            services: new ProxyRepository(({filters}) => {
                let combined = this.services.getFilters(filters);
                let repository = new ServiceRepository();
                if ((combined.filters.query === undefined || combined.filters.query.length === 0) &&
                    _.isVoid(this.model.clinic_id)) {
                    return Promise.resolve([]);
                }
                return repository.fetchList(combined.filters);
            }, {
                filters: this.getServiceFilters(),
            }),
        }
    },
    watch: {
        ['model.clinic_id'](clinic_id) {
            if (this.model.isNew()) {
                this.services.setFilters(this.getServiceFilters());
            }

            this.specializations.setFilters({
                status: 1,
                clinic: clinic_id
            })
        },
        ['model.specialization_id'](val) {
            this.services.setFilters(this.getServiceFilters())
        },
        ['model.cashbox_id'](val) {
            this.$emit('cashbox-changed', val);
        },
    },
    methods: {
        getServiceFilters() {
            let priceDate = this.date || this.$moment().format('YYYY-MM-DD');
            return _.onlyFilled({
                clinic: this.model.clinic_id,
                specialization: this.model.specialization_id,
                hasPrice: {
                    clinic: this.model.clinic_id,
                    from: priceDate,
                    to: priceDate,
                    set: [CONSTANTS.PRICE.SET_TYPE.BASE],
                },
                disabled: false,
            });
        },
    },
}
</script>
