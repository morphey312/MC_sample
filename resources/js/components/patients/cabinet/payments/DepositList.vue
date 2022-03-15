<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="getFilters()"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :scopes="scopes"
        :flex-height="true"
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
import PaymentRepository from '@/repositories/payment';
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
            repository: new PaymentRepository(),
            fields: [
                {
                    name: 'created',
                    title: __('Дата оплаты'),
                    width: '5%',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filterField: 'date',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'payed_amount',
                    title: __('Сумма'),
                    titleClass: 'text-right',
                    dataClass: 'no-dash text-right',
                    width: '4%',
                },
                {
                    name: 'cashier.name',
                    title: __('Кассир'),
                    width: '15%',
                },
                {
                    name: 'cashbox.name',
                    title: __('Форма оплаты'),
                    width: '3%',
                },
                {
                    name: 'is_deposit',
                    title: __('Вид платежа'),
                    width: '3%',
                    formatter: (val) => {
                        if (val == true) {
                            return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.DEPOSIT);
                        }
                        return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.HAS_APPOINTMENT);
                    },
                },
                {
                    name: 'type',
                    title: __('Возврат'),
                    titleClass: 'text-left',
                    dataClass: 'no-dash text-left',
                    width: '3%',
                    formatter: (val) => {
                        return this.$formatter.boolToString((val === CONSTANTS.PAYMENT.TYPES.EXPENSE), '<span class="check-yes" />');
                    },
                },
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'appointment',
                'cashbox',
                'doctor',
                'cashier',
                'service',
            ]
        }
    },
    methods: {
        getFilters() {
            return {
                ...this.filters,
                is_deposit: true,
                is_prepayment: false,
            };
        },
    },
}
</script>
