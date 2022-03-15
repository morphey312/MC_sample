<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :scopes="scopes"
        :flex-height="true"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
            <div class="buttons">
                {{ __('Итого: {amount} грн', {amount: $formatter.numberFormat(totalAmount)}) }}
            </div>
        </template>
    </manage-table>
</template>
<script>
import PrepaymentRepository from '@/repositories/patient/prepayment';
import ProxyRepository from '@/repositories/proxy-repository';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS from '@/constants';
import ListMixin from './mixins/list';

export default {
    mixins: [
        ListMixin,
    ],
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repository = new PrepaymentRepository();
                return repository.fetch(this.getFilters(filters), sort, scopes, page, limit);
            }),
            fields: [
                {
                    name: 'created',
                    title: __('Дата оплаты'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filterField: 'date',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'amount',
                    title: __('Сумма'),
                    titleClass: 'text-right',
                    dataClass: 'no-dash text-right',
                    width: '10%',
                },
                {
                    name: 'service_name',
                    title: __('Услуга'),
                    width: '20%',
                },
                {
                    name: 'payment.cashier_name',
                    title: __('Кассир'),
                    width: '15%',
                },
                {
                    name: 'payment.cashbox_name',
                    title: __('Форма оплаты'),
                    width: '10%',
                },
                {
                    name: 'is_deposit',
                    title: __('Вид платежа'),
                    width: '10%',
                    formatter: (val) => {
                        return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.PREPAYMENT);
                    },
                },
                {
                    name: 'used',
                    title: __('Использован'),
                    titleClass: 'text-left',
                    dataClass: 'no-dash text-left',
                    width: '10%',
                    formatter: (val) => {
                        return this.$formatter.boolToString(val, '<span class="check-yes" />');
                    },
                },
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'payment',
                'cashier',
                'service',
            ],
        }
    },
    methods: {
        getFilters(filters) {
            return {
                ...filters,
                is_deposit: true,
                is_prepayment: true,
            };
        },
        setTotal() {
            let rows = this.getTableRows();
            let amount = 0;
            if (rows.length !== 0) {
                amount = rows.reduce((total, row) => {
                    return total += Number(row.amount);
                }, 0);
            }
            this.totalAmount = amount;
        },
    },
}
</script>
