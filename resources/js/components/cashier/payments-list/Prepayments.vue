<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot="patient_name"
            slot-scope="props">
            <a href="#" @click.prevent="showPatientPayments(props.rowData.patient)">
                {{ (props.rowData.patient ? props.rowData.patient.name : '') }}
            </a>
        </template>
        <template slot="footer-top">1
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import PrepaymentRepository from '@/repositories/patient/prepayment';
import ProxyRepository from '@/repositories/proxy-repository';
import ServiceRepository from '@/repositories/service';
import PaymentMethodRepository from '@/repositories/payment-method';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository:  new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repository = new PrepaymentRepository();
                return repository.fetch(filters, sort, scopes, page, limit);
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
                    name: 'patient_name',
                    title: __('Пациент'),
                    width: '15%',
                    filter: true,
                    filterField: 'patient_name',
                },
                {
                    name: 'amount',
                    title: __('Остаток предоплаты'),
                    titleClass: 'text-right',
                    dataClass: 'no-dash text-right',
                    width: '5%',
                },
                {
                    name: 'payment.payed_amount',
                    title: __('Оплачено'),
                    titleClass: 'text-right',
                    dataClass: 'no-dash text-right',
                    width: '5%',
                },
                {
                    name: 'payment.cashbox_name',
                    title: __('Форма оплаты'),
                    width: '10%',
                    filter: new PaymentMethodRepository({filters: {is_enabled: true}}),
                    filterField: "payment_method",
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'service_name',
                    title: __('Услуга'),
                    width: '20%',
                    filter: new ServiceRepository({
                        filters: {
                            disabled: false
                        }
                    }),
                    filterField: 'service',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'payment.cashier_name',
                    title: __('Кассир'),
                    width: '15%',
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
                    filterField: 'used',
                    filter: 'yes_no',
                },
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'payment',
                'cashier',
                'service',
                'patient',
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        showPatientPayments(patient) {
            this.$emit('show-patient-payments', patient);
        },
        getManageTable() {
            return this.$refs.table;
        },
        unselectAll() {
            this.$refs.table.unselectAll();
        }
    },
}
</script>
