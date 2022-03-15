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
        :pagination="false"
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
import MoneyRecieverRepository from '@/repositories/clinic/money-reciever';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS from '@/constants';

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
            repository: new ProxyRepository(({filters, sort}) => {
                    let repository = new PaymentRepository();
                    return repository.fetchAll(this.filters, sort, this.scopes).then((result) => {
                        return Promise.resolve({
                            rows: this.filterResults(result),
                        });
                    });
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
                    name: 'service.name',
                    title: __('Услуга'),
                    dataClass: 'no-dash',
                    width: '20%',
                    filter: serviceRepository,
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
                {
                    name: 'cashier.name',
                    title: __('Кассир'),
                    width: '15%',
                    filter: true,
                    filterField: 'cashier_name',
                    sortField: 'cashier_name',
                },
                {
                    name: 'deposit_types',
                    title: __('Вид платежа'),
                    width: '10%',
                    formatter: (val) => {
                        if (val && val.is_deposit == true) {
                            return val.is_prepayment
                                ? this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.PREPAYMENT)
                                : this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.DEPOSIT);
                        }
                        return this.$handbook.getOption('payment_kind', CONSTANTS.PAYMENT.KINDS.HAS_APPOINTMENT);
                    },
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    dataClass: 'no-dash',
                    filter:true,
                    filterField: 'comment',
                    sortField: 'comment',
                    width: '15%',
                },
                {
                    name: 'money_reciever_name',
                    title: __('Получатель'),
                    dataClass: 'no-dash',
                    filter: new MoneyRecieverRepository(),
                    filterField: 'money_reciever',
                    sortField: 'money_reciever',
                    width: '15%',
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
            ]
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
        unselectAll() {
            this.$refs.table.unselectAll();
        },
        paintRows(){
            let tableDomEl = this.$refs.table.$el;
            let data = this.$refs.table.getData();
            let rows = tableDomEl.querySelectorAll('.vuetable-body tr');

            rows.forEach((row, index) => {
                if (data[index] && data[index].payment_destination){
                    let color = data[index].payment_destination.color;
                    row.removeAttribute('style');
                    if(color !== null){
                        row.setAttribute('style', "background-color:" + color)
                    }
                }

            });
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
        filterResults(rows) {
            let containers = [];
            let analysis_results = rows.filter(p => {
                if(p.service) {
                    return p.service.container_type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS;
                }
               });
            let checks = _.groupBy(analysis_results, 'check_id');
            rows.forEach(row => {
                if (row.check_id && 
                    row.service && 
                    row.service.container_type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS) {
                    let groupId = this.getUIDAnalysisRows(row);
                    let existedInContainer = containers.find(r => r.groupId === groupId);
                    if (!existedInContainer) {
                        let elem = {
                            ...row,
                            groupId: groupId,
                            items: this.getGroupedAnalysis(checks,row)
                        }
                        containers.push(elem);
                    } else{
                        existedInContainer.payed_amount = (parseFloat(existedInContainer.payed_amount) + parseFloat(row.payed_amount)).toFixed(2);
                        existedInContainer.amount = (parseFloat(existedInContainer.amount) + parseFloat(row.amount)).toFixed(2);
                    }
                } else{
                    containers.push(row);
                }
            });
            
            return containers;
        },
        getUIDAnalysisRows(row) {
            return `${row.doctor_id}${row.cashbox_id}${row.check_id}`;
        },
        getGroupedAnalysis(checks,row) {
            let grouped = [];
            Object.keys(checks).forEach(checkId => {
                let check = checks[checkId].filter((item) => 
                    (item.check_id === row.check_id && item.doctor_id === row.doctor.id));
                let cashboxes = _.groupBy(check, 'cashbox_id');
                Object.keys(cashboxes).forEach(cashboxId => {
                    if(row.cashbox_id == cashboxId) {
                        grouped = cashboxes[cashboxId];
                    }
                    
                });
            });
            return grouped;
        },
    },
}
</script>
