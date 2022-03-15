<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import PaymentRepository from '@/repositories/payment';
import ProxyRepository from '@/repositories/proxy-repository';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import ClinicRepository from '@/repositories/clinic';
import ServiceRepository from '@/repositories/service';
import CONSTANTS from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new PaymentRepository(),
            fields: [
                {
                    name: 'created',
                    title: __('Дата'),
                    width: '11%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filterField: 'date',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'appointment.card_number',
                    title: __('№ карты'),
                    width: '8%',
                    dataClass: 'no-dash',
                    filter: true,
                    filterField: 'patient_card_number',
                },
                {
                    name: 'patient.name',
                    title: __('Пациент'),
                    width: '15%',
                    filter: true,
                    filterField: 'patient_name',
                },
                {
                    name: 'payed_amount',
                    title: __('Сумма, грн'),
                    dataClass: 'text-right',
                    titleClass: 'text-right',
                    width: '8%',
                },
                {
                    name: 'cashbox.name',
                    title: __('Форма оплаты'),
                    width: '10%',
                },
                {
                    name: 'payment_destination.name',
                    title: __('Назначение'),
                    width: '10%',
                    dataClass: 'no-dash',
                    filter: new PaymentDestinationRepository(),
                    filterField: 'payment_destination',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'service.name',
                    title: __('Услуга'),
                    dataClass: 'no-dash',
                    width: '20%',
                    filter: new ServiceRepository(),
                    filterField: 'service',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'type',
                    title: __('Возврат'),
                    width: '8%',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.boolToString((val === CONSTANTS.PAYMENT.TYPES.EXPENSE), '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('payment_type'),
                    filterField: 'payment_type',
                },
                {
                    name: 'clinic.name',
                    title: __('Клиника'),
                    width: '15%',
                    dataClass: 'no-dash',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('payments'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'doctor.name',
                    title: __('Врач'),
                    width: '15%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'cashier.name',
                    title: __('Кассир'),
                    width: '15%',
                },
                {
                    name: 'is_deposit',
                    title: __('Вид платежа'),
                    width: '10%',
                    formatter: (val) => {
                        if (val == true) {
                            return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.DEPOSIT);
                        }
                        return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.HAS_APPOINTMENT);
                    },
                },
                {
                    name: 'comment',
                    sortField: 'comment',
                    title: __('Примечание'),
                    dataClass: 'no-dash',
                    width: '15%',
                },
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'clinic',
                'appointment',
                'patient',
                'cashbox',
                'payment_destination',
                'doctor',
                'cashier',
                'service',
            ]
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        loaded() {
            this.$emit('loaded');
        },
    },
}
</script>