<template>
    <div>
        <manage-table
            ref="table"
            :fields="fields"
            :repository="repository"
            :selectable-rows="true"
            :show-table-settings="false"
            :enable-pagination="false"
            @selection-changed="selectionChanged"
            @loaded="loaded">
        </manage-table>
    </div>
</template>

<script>
import MoneyReciever from "@/models/clinic/money-reciever";
import MoneyRecieverCashboxRepository from "@/repositories/money-reciever-cashbox";

export default {
    props: {
        moneyReciever: Object,
    },
    data() {
        return {
            model: new MoneyReciever({id: this.moneyReciever.id}),
            repository: new MoneyRecieverCashboxRepository({
                filters: {
                    moneyReciever: this.moneyReciever.id,
                },
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название кассы'),
                },
            ],
        }
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
    }
}
</script>

<style scoped>

</style>
