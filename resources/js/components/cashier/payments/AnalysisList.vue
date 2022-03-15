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
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import PaymentRepository from '@/repositories/payment';
import ProxyRepository from '@/repositories/proxy-repository';
import ServiceRepository from '@/repositories/service';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS from '@/constants';
import AnalysisRepository from '@/repositories/analysis';

export default {
    props: {
        filters: Object,
        paymentMethods: {
            type: Array,
            default: () => [],
        },
         repo: {
            type: Object,
        },
    },
    data() {
        let paymentMethodRepository = new ProxyRepository(() => {
            return Promise.resolve(this.paymentMethods);
        });

        let serviceRepository = new ServiceRepository({
            filters: {
                disabled: false
            }
        });

        return {
            serviceRepository,
            paymentMethodRepository,
            repository: new PaymentRepository({
                 filters: {
                    service_container: 'analysis_results',
                },
            }),
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
                    sortField: 'created',
                },
                {
                    name: 'card_number',
                    title: __('№ карты'),
                    width: '8%',
                    dataClass: 'no-dash',
                    filter: true,
                    filterField: 'patient_card_number',
                    sortField: 'patient_card_number',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'patient_name',
                    title: __('Пациент'),
                    width: '15%',
                    filter: true,
                    filterField: 'patient_name',
                    sortField: 'patient_name',
                },
                {
                    name: 'payed_amount',
                    title: __('Сумма, грн'),
                    dataClass: 'text-right',
                    titleClass: 'text-right',
                    width: '8%',
                    sortField: 'payed_amount',
                },
                {
                    name: 'cashbox.name',
                    title: __('Форма оплаты'),
                    width: '10%',
                    filter: paymentMethodRepository,
                    filterField: "payment_method",
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'payment_method',
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
                    sortField: 'payment_destination',
                },
                {
                    name: 'service.analysis_items',
                    title: __('Название анализа'),
                    dataClass: 'no-dash text-select',
                    width: '35%',
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    },
                    filter: new ProxyRepository(({filters}) => {
                        let repo = new AnalysisRepository();
                            return repo.fetchListForFilter(filters);
                    }),
                    filterField: 'service_analysis',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'service.analysis_count',
                    title: __('Кол-во'),
                    dataClass: 'no-dash',
                    width: '5%',
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
                    sortField: 'payment_type',
                },
                {
                    name: 'is_technical',
                    title: __('Тех.'),
                    width: '8%',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.boolToString(val, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'is_technical',
                    sortField: 'is_technical',
                },
                {
                    name: 'doctor.name',
                    title: __('Врач'),
                    width: '15%',
                    dataClass: 'no-dash',
                    filter: true,
                    filterField: 'doctor_name',
                    sortField: 'doctor_name',
                },
                
                
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'appointment',
                'patient',
                'cashbox',
                'payment_destination',
                'doctor',
                'cashier',
                'service',
                'clinic',
                'check',
                'money_reciever',
            ],

        };
    },
    watch: {
        paymentMethods() {
            this.paymentMethodRepository.notifyWatcher('filters');
        },
        ['filters.clinic']: {
            handler() {
                this.serviceRepository.setFilters(this.getServiceFilters());
            }
        },
        ['filters.createdEnd']: {
            handler() {
                this.serviceRepository.setFilters(this.getServiceFilters());
            }
        },
        ['filters.createdStart']: {
            handler() {
                this.serviceRepository.setFilters(this.getServiceFilters());
            }
        }
    },
    methods: {
        refresh() {
            this.$refs.table.refresh();
        },
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
        unselectAll() {
            this.$refs.table.unselectAll();
        },
        getManageTable() {
            return this.$refs.table;
        },
        getServiceFilters() {
            return _.onlyFilled({
                is_in_payments: {
                    from: this.filters.createdStart,
                    to: this.filters.createdEnd,
                    clinic: [this.filters.clinic]
                },
                disabled: 0,
                clinic: this.filters.clinic,
            });
        },
    },
}
</script>
