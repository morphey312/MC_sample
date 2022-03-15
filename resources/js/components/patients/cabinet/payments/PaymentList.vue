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
        <template
            slot="service_name"
            slot-scope="props">
            <span v-if="isAnalisysPayment(props.rowData)">
                 {{ $formatter.listFormat(props.rowData.service.analysis_items) }}
                </span>
                <span v-else>
                    {{ props.rowData.is_deposit ? '' : props.rowData.service.name }}
                    </span>
            </template>
        <template
            slot="not_debt"
            slot-scope="props">
            <span v-html="getNotDebt(props.rowData)" />
        </template>
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
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import AppointmentManager from '@/components/appointments/mixin/manager';
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
                    name: 'clinic.name',
                    title: __('Клиника'),
                    width: '15%',
                    filterField: 'clinic',
                    filter: new ClinicRepository(),
                    filterProps: {
                        multiple: true,
                    }
                },
                {
                    name: 'created',
                    title: __('Дата оплаты'),
                    width: '11%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filterField: 'date',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'card_number',
                    title: __('Номер карты'),
                    width: '10%',
                },
                {
                    name: 'service_name',
                    title: __('Услуга'),
                    width: '20%',
                   /* filter: false,
                    filterField: 'service_name',*/
                },
                {
                    name: 'doctor.name',
                    title: __('Врач'),
                    width: '20%',
                    filter: true,
                    filterField: 'doctor_name',
                },
                {
                    name: 'amount',
                    title: __('Оплачено, грн'),
                    titleClass: 'text-right',
                    dataClass: 'no-dash text-right',
                    width: '10%',
                },
                {
                    name: 'from_deposit',
                    title: __('Опл. из аванса'),
                    titleClass: 'text-left',
                    dataClass: 'no-dash text-left',
                    width: '9%',
                    formatter: (val) => {
                        return this.$formatter.boolToString((val === CONSTANTS.PAYMENT.TYPES.EXPENSE), '<span class="check-yes" />');
                    },
                },
                {
                    name: 'type',
                    title: __('Возврат'),
                    titleClass: 'text-left',
                    dataClass: 'no-dash text-left',
                    width: '14%',
                    formatter: (val) => {
                        return this.$formatter.boolToString((val === CONSTANTS.PAYMENT.TYPES.EXPENSE), '<span class="check-yes" />');
                    },
                },
                {
                    name: 'not_debt',
                    title: __('Нет долга'),
                    width: '10%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'cashbox.name',
                    title: __('Форма оплаты'),
                    width: '10%',
                },
                {
                    name: 'deposit_types',
                    title: __('Вид платежа'),
                    width: '11%',
                    formatter: (val) => {
                        if (val && val.is_deposit == true) {
                            return val.is_prepayment
                                ? this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.PREPAYMENT)
                                : this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.DEPOSIT);
                        }
                        return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.HAS_APPOINTMENT);
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
                'service',
                'clinic'
            ],
        }
    },
    methods: {
        getNotDebt(row) {
            if (row.type === CONSTANTS.PAYMENT.TYPES.INCOME || !row.service) {
                return '';
            }
            return this.$formatter.boolToString(row.service.not_debt, '<span class="check-yes" />');
        },
        isAnalisysPayment(row) {
            if (row.service) {
                return row.service.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES;
            }
            return false;

        }
    },
}
</script>
