<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository" >
        <template
            slot="balance"
            slot-scope="props">
            {{ getBalanceOutput(props.rowData) }}
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import CashboxMixin from '../mixins/cashbox';

export default {
    mixins: [
        CashboxMixin,
    ],
    props: {
        balances: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.getCashBalances(),
                });
            }),
            fields: [
                {
                    name: 'payment_method.name',
                    title: __('Касса'),
                },
                {
                    name: 'initial_amount',
                    title: __('Начало дня, грн'),
                    width: '210px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'income',
                    title: __('Приход, грн'),
                    width: '210px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'expense',
                    title: __('Расход, грн'),
                    width: '210px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'balance',
                    title: __('Остаток, грн'),
                    width: '210px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
            ],
        }
    },
    methods: {
        refresh() {
            this.$refs.table.refresh();
        },
        getCashBalances() {
            return this.balances.filter(balance => balance.payment_method.use_cash == true);
        },
        getBalanceOutput(row) {
            return this.$formatter.numberFormat(this.getBalance(row) + Number(row.initial_amount));
        },
    },
}
</script>