<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="24">
                        <div>
                            <form-select
                                :entity="model"
                                property="money_reciever_id"
                                :required="true"
                                :options="moneyRecievers"
                                :label="__('Выберите получателя денег')"
                            />
                        </div>
                        <div>
                            <form-select
                                :entity="model"
                                property="money_reciever_cashbox_id"
                                :required="true"
                                :options="cashboxesList"
                                :label="__('Выберите кассу Checkbox')"
                            />
                        </div>
                    </el-col>
                </el-row>
                <slot name="buttons"></slot>
            </section>
        </div>
    </model-form>
</template>

<script>
import MoneyRecieverRepository from "@/repositories/clinic/money-reciever";
import MoneyRecieverCashboxRepository from "@/repositories/money-reciever-cashbox";
import CheckboxCredentialsRepository from "@/repositories/employee/checkbox-credentials";

export default {
    props: {
        model: Object,
        checkboxCashboxes : {
            type: Array,
            default: () => {},
        },
    },
    data() {
        return {
            moneyRecievers: [],
            moneyRecieverCashboxes: [],
            checkboxCredentials: new CheckboxCredentialsRepository(),
            cashboxesList: [],
        }
    },
    watch: {
        ['model.money_reciever_id'](val) {
            if (val) {
                this.moneyRecieverCashboxes = [];
                this.cashboxesList = [];
                this.model.money_reciever_cashbox_id = null;
                let moneyRecieverCashbox = new MoneyRecieverCashboxRepository({
                    filters: {
                        hasCredentials: val,
                    }
                });

                moneyRecieverCashbox.fetch().then((response) => {
                    this.moneyRecieverCashboxes = response.rows;
                    this.moneyRecieverCashboxes.forEach(cashbox => {
                        this.cashboxesList.push({
                            id: cashbox.id,
                            value: cashbox.name,
                        });
                    })
                });
            }
        },
        ['model.money_reciever_cashbox_id'](val) {
            if (val) {
                this.checkboxCredentials.fetch().then((response) => {
                    let credentials = response.rows.find((item) => {
                        return item.money_reciever_cashbox_id === val &&
                            item.employee_id === this.model.employee_id;
                    })
                    this.model.login = credentials.login;
                    this.model.password = credentials.password;
                    this.model.cashbox_key = this.moneyRecieverCashboxes.find((item) => item.id === val).cashbox_key;
                })
            }
        }
    },
    created() {
        this.model.employee_id = this.$store.state.user.employee.id;
    },
    mounted() {
        this.getMoneyRecievers();
    },
    methods: {
        getMoneyRecievers() {
            let moneyReciever = new MoneyRecieverRepository();

            moneyReciever.fetch(null, null, ['money_reciever_cashboxes',]
            ).then((response) => {
                response.rows.forEach((row) => {
                    if (!this.checkboxCashboxes.find((cashbox) => cashbox.money_reciever_id === row.id) && row.cashboxes && row.cashboxes.length) {
                        this.moneyRecievers.push(row);
                    }
                })

            })
        }
    }
}
</script>
