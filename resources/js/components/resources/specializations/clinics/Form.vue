<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :options="clinics"
                    property="clinic_id"
                    :filterable="true"
                    :label="__('Клиника')"
                />
                <form-select
                    :entity="model"
                    options="active_status"
                    property="status"
                    :label="__('Статус')"
                />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="first_patient_appointment_limit"
                    type="number"
                    :step="1"
                    :min="0"
                    css-class="label-solid form-input"
                    :label="__('Минимальное количество записей')">
                    <span slot="label-addon">
                        <el-tooltip
                            placement="bottom"
                            effect="light"
                            popper-class="light-popover-content specialization-popover">
                            <template slot="content">{{ __('Минимальное кол-во записей в отделение, с которого начинает действовать ограничение') }}</template>
                            <svg-icon name="info-alt" class="icon-tiddly icon-grey" />
                        </el-tooltip>
                    </span>
                </form-input>
                <form-row
                    name="days_since"
                    label="&nbsp;">
                    <form-checkbox
                        :entity="model"
                        property="show_days_since_message"
                        :label="__('По данной специализации выводить сообщение о давности посещения клиники пациентом')" />
                </form-row>
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="days_since_last_visit"
                    type="number"
                    :step="1"
                    :min="0"
                    css-class="label-solid form-input"
                    :label="__('Давность посещения, дни')" >
                    <span slot="label-addon">
                        <el-tooltip
                            placement="bottom"
                            effect="light"
                            popper-class="light-popover-content specialization-popover">
                            <template slot="content">{{ __('Количество дней с момента последнего посещения клиники пациентом, после которого посещение считается давним') }}</template>
                            <svg-icon name="info-alt" class="icon-tiddly icon-grey" />
                        </el-tooltip>
                    </span>
                </form-input>
                <form-select
                    :entity="model"
                    :options="moneyRecievers"
                    property="money_reciever_id"
                    :label="__('Получатель денег')" />
                </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import MoneyRecieverRepository from '@/repositories/clinic/money-reciever';

export default {
    props: {
        model: Object,
    },
    data() {
        return {
            clinics: new ClinicRepository({
                filters: this.getClinicFilters(),
                accessLimit: this.$isAccessLimited('specializations')
            }),
            moneyRecievers: new MoneyRecieverRepository(),
        }
    },
    methods: {
        getClinicFilters() {
            return _.onlyFilled({
                not_in_specialization: [this.model.specialization_id]
            })
        }
    }
}
</script>
