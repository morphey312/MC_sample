<template>
    <div class="sections-wrapper">
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="name"
                        :label="__('Название клиники')" />
                    <form-select
                        :entity="model"
                        options="voip_queue"
                        property="voip_queue"
                        :label="__('Очередь звонков')" />
                    <form-select
                        :entity="model"
                        :options="groups"
                        property="group_id"
                        :label="__('Группа клиник')" />
                    <form-select
                        :entity="model"
                        :options="moneyRecievers"
                        @changed="loadMoneyRevieverData"
                        property="money_reciever_id"
                        :label="__('Получатель денег')" />
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="official_name"
                        :label="__('Официальное название клиники')">
                        <svg-icon
                            slot="label-addon"
                            name="question-alt"
                            class="icon-tiny"
                            :title="__('Используется в ЛК')" />
                    </form-input>
                    <form-select
                        :entity="model"
                        options="active_status"
                        property="status"
                        :label="__('Статус')" />
                    <form-select
                        :entity="model"
                        options="city"
                        property="city"
                        :label="__('Город')" />
                    <form-input
                        :entity="model"
                        property="analysis_check_url"
                        :label="__('URL для проверки анализов')" />
                </el-col>
                <el-col :span="8">
                    <form-select
                        :entity="model"
                        :options="mspList"
                        property="msp_id"
                        :label="__('Предоставитель мед. услуг')" />
                    <form-select
                        :entity="model"
                        options="currency"
                        property="currency"
                        :label="__('Валюта')" />
                    <form-checkbox
                        :entity="model"
                        property="not_round_cost"
                        :label="__('Не округлять стоимость')"
                    />
                    <form-checkbox
                        :entity="model"
                        property="show_on_site"
                        :label="__('Выводить в ЛК')"
                    />
                    <form-input
                        :entity="model"
                        property="map_link"
                        :label="__('Расположение на карте')" />
                    <form-input
                        :entity="model"
                        property="short_name"
                        :label="__('Краткое обозначение')" />
                </el-col>
            </el-row>
        </section>
        <hr />
        <section>
            <ehealth-address
                v-model="model.address"
                error-prefix="address."
                :show-apartment="false" />
        </section>
    </div>
</template>

<script>
import CountryRepository from '@/repositories/country';
import ClinicGroupRepository from '@/repositories/clinic/group';
import MoneyRecieverRepository from '@/repositories/clinic/money-reciever';
import EhealthAddress from '@/components/general/form/AddressEhealth.vue';

export default {
    components: {
        EhealthAddress,
    },
    props: {
        model: Object,
        mspList: Array,
    },
    data(){
        return {
            countries: new CountryRepository(),
            groups: new ClinicGroupRepository(),
            moneyRecievers: new MoneyRecieverRepository(),
        }
    },
    methods: {
        loadMoneyRevieverData() {
            console.log(this.model.money_reciever_id);
            this.model.money_reciever_signer = "Hello world";
            this.model.contact_phone = "103";
            this.model.money_reciever_official_additional = "Hello world";
            console.log('start me please baybe');
        }
    }
}
</script>
