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
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot="patient.name"
            slot-scope="props">
            <a href="#" @click.prevent="showPatientPayments(props.rowData.patient)">
                {{ (props.rowData.patient ? props.rowData.patient.name : '') }}
            </a>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import PaymentRepository from '@/repositories/payment';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import PaymentMethodRepository from '@/repositories/payment-method';
import ClinicRepository from '@/repositories/clinic';
import ServiceRepository from '@/repositories/service';
import MoneyRecieverRepository from '@/repositories/clinic/money-reciever';
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
                    width: '80px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filterField: 'date',
                    filter: DateHeaderFilter,
                    sortField: 'created',
                },
                {
                    name: 'card_number',
                    title: __('№ карты'),
                    width: '100px',
                    dataClass: 'no-dash',
                    filter: true,
                    filterField: 'patient_card_number',
                    sortField: 'patient_card_number',
                },
                {
                    name: 'appointment.specialization_name',
                    title: __('Специализация записи'),
                    width: '100px',
                    dataClass: 'no-dash',
                    sortField: 'specialization_name',
                },
                {
                    name: 'patient.name',
                    title: __('Пациент'),
                    width: '200px',
                    filter: true,
                    filterField: 'patient_name',
                    sortField: 'patient_name',
                },
                {
                    name: 'payed_amount',
                    title: __('Сумма, грн'),
                    dataClass: 'text-right',
                    titleClass: 'text-right',
                    width: '100px',
                    sortField: 'payed_amount',
                },
                {
                    name: 'cashbox.name',
                    title: __('Форма оплаты'),
                    width: '120px',
                    filter: new PaymentMethodRepository({filters: {is_enabled: true}}),
                    filterField: "payment_method",
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'payment_method',
                },
                {
                    name: 'payment_destination.name',
                    title: __('Назначение'),
                    width: '120px',
                    dataClass: 'no-dash',
                    filter: new PaymentDestinationRepository(),
                    filterField: 'payment_destination',
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'payment_destination',
                },
                {
                    name: 'service.name',
                    title: __('Услуга'),
                    dataClass: 'no-dash',
                    width: '250px',
                    filter: new ServiceRepository({
                        filters: {
                            disabled: false
                        }
                    }),
                    filterField: 'service',
                    filterProps: {
                        multiple: true,
                        reserveKeyword: true,
                    },
                },
                {
                    name: 'service.not_debt',
                    title: __('Нет долга'),
                    width: '80px',
                    dataClass: 'no-dash',
                    sortField: 'not_debt',
                    filter: 'yes_no',
                    filterField: 'not_debt',
                    formatter: (val) => {
                        return this.$formatter.boolToString(val, '<span class="check-yes" />')
                    },
                },
                {
                    name: 'type',
                    title: __('Возврат'),
                    width: '90px',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.boolToString((val === CONSTANTS.PAYMENT.TYPES.EXPENSE), '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('payment_type'),
                    filterField: 'payment_type',
                    sortField: 'payment_type',
                },
                {
                    name: 'clinic.name',
                    title: __('Клиника'),
                    width: '120px',
                    dataClass: 'no-dash',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('payments'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'clinic_name',
                },
                {
                    name: 'doctor.name',
                    title: __('Врач'),
                    width: '200px',
                    dataClass: 'no-dash',
                    filter: true,
                    filterField: 'doctor_name',
                    sortField: 'doctor_name',
                },
                {
                    name: 'cashier.name',
                    title: __('Кассир'),
                    width: '200px',
                    filter: true,
                    filterField: 'cashier_name',
                    sortField: 'cashier_name',
                },
                {
                    name: 'is_deposit',
                    title: __('Вид платежа'),
                    width: '100px',
                    formatter: (val) => {
                        if (val == true) {
                            return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.DEPOSIT);
                        }
                        return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.HAS_APPOINTMENT);
                    },
                    filter: this.$handbook.getOptions('payment_kind'),
                    filterField: 'payment_kind',
                    sortField: 'is_deposit',
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    dataClass: 'no-dash',
                    width: '150px',
                },
                {
                    name: 'money_reciever_name',
                    title: __('Получатель'),
                    dataClass: 'no-dash',
                    filter: new MoneyRecieverRepository(),
                    filterField: 'money_reciever',
                    sortField: 'money_reciever',
                    width: '150px',
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
                'money_reciever',
            ]
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        loaded() {
            this.$emit('loaded');

            this.$nextTick(() => {
                this.paintRows();
            })
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        showPatientPayments(patient) {
            this.$emit('show-patient-payments', patient);
        },
        paintRows() {
            let tableDomEl = this.$refs.table.$el;
            let data = this.$refs.table.getData();
            let rows = tableDomEl.querySelectorAll('.vuetable-body tr');

            rows.forEach((row, index) => {
                if (data[index] && data[index].payment_destination){
                    let color = data[index].payment_destination.color;
                    row.removeAttribute('style');
                    if (color !== null) {
                        row.setAttribute('style', "background-color:" + color)
                    }
                }
            });
        },
        unselectAll() {
            this.$refs.table.unselectAll();
        },
    },
}
</script>
