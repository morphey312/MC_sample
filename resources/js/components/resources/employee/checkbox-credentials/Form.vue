<template>
    <model-form :model="model">
        <form-select
            :entity="model"
            :options="moneyRecievers"
            property="money_reciever_id"
            :label="__('Получатель денег')" />
        <form-select
            :entity="model"
            :options="moneyRecieverCashboxes"
            property="money_reciever_cashbox_id"
            :label="__('Касса Checkbox')" />
        <form-input
            :entity="model"
            property="login"
            :label="__('Логин')" />
        <form-input
            :entity="model"
            property="password"
            :label="__('Пароль')" />
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import MoneyRecieverCashboxRepository from "@/repositories/money-reciever-cashbox";
import MoneyRecieverRepository from '@/repositories/clinic/money-reciever';
import CheckboxCredentialsRepository from "@/repositories/employee/checkbox-credentials";

export default {
    props: {
        model: {
            type: Object,
        },
    },
    data() {
        return {
            moneyRecievers: [],
            moneyRecieverCashboxes: [],
            checkboxCredentials: [],
        }
    },
    watch: {
        ['model.money_reciever_id'](val, oldVal) {
            this.getMoneyRecieverCashboxes();

            if (oldVal !== null && oldVal !== val) {
                this.model.money_reciever_cashbox_id = null;
            }
        },
    },
    mounted() {
        this.getMoneyRecievers();
        this.getCheckboxCredentials();
    },
    methods: {
        getMoneyRecieverCashboxes() {
            this.moneyRecieverCashboxes = [];
            let filters = {};
            if (this.model.money_reciever_id) {
                filters.moneyReciever = this.model.money_reciever_id
            }

            let moneyRecieverCashbox = new MoneyRecieverCashboxRepository({
                filters: filters,
            });

            moneyRecieverCashbox.fetch().then((response) => {
                response.rows.forEach((item) => {
                    if (!this.checkboxCredentials.find((credentials) => credentials.money_reciever_cashbox_id === item.id) ||
                        this.model.money_reciever_cashbox_id === item.id) {
                        this.moneyRecieverCashboxes.push(item);
                    }
                });
            });
        },
        getMoneyRecievers() {
            let moneyReciever = new MoneyRecieverRepository();

            moneyReciever.fetchList().then((response) => {
                this.moneyRecievers = response;
            });
        },
        getCheckboxCredentials() {
            let checkboxCredentials = new CheckboxCredentialsRepository({
                filters: {
                    employee: this.model.employee_id
                }
            });

            checkboxCredentials.fetch().then((response) => {
                this.checkboxCredentials = response.rows;

                if (!this.model.isNew()) {
                    this.getMoneyRecieverCashboxes();
                }
            });
        },
    }
}
</script>
