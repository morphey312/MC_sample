<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :scopes="scopes"
        :flex-height="true"
        :enablePagination="false"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot="service_name"
            slot-scope="props">
            <span v-if="isAnalisysPayment(props.rowData)">
                 {{ $formatter.listFormat(props.rowData.analysis_name) }}
            </span>
            <span v-else>
                    {{ props.rowData.name }}
            </span>
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
import ProxyRepository from '@/repositories/proxy-repository';
import EmployeeRepository from '@/repositories/employee';
import AppointmentServiceRepository from '@/repositories/appointment/service';
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
                return this.getRows(filters, sort, scopes, page, limit);
            }),
            fields: [
                {
                    name: 'appointment.date',
                    title: __('Дата записи'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'clinic.name',
                    title: __('Клиника'),
                    width: '15%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'card_number',
                    title: __('Номер карты'),
                    width: '15%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'service_name',
                    title: __('Услуга'),
                    width: '40%',
                },
                {
                    name: 'debt',
                    title: __('Долг, грн'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                {
                    name: 'expected_payment',
                    title: __('К оплате, грн'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            scopes: [
                'clinic',
            ],
        }
    },
    methods: {
        isAnalisysPayment(row) {
            if (row) {
                return row.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES;
            }
            return false;
        },
        getRows(filters, sort, scopes, page, limit) {
            let service = new AppointmentServiceRepository();
            return service.fetchDebts(this.getFilters(filters), sort, scopes, page, limit).then((response) => {
                return Promise.resolve({
                    rows: this.prepareRows(response),
                });
            });
        },
        getFilters(filters) {
            let filter = {...filters};
            if (_.isFilled(filter.clinic)) {
                filter.appointment_clinic = filter.clinic;
            }
            return filter;
        },
        prepareRows(rows) {
            return _.orderBy(rows, ['row.appointment.date'], ['desc'])
                    .map((row) => {
                        row.expected_payment = _.isVoid(row.expected_payment) ? 0 : row.expected_payment;
                        row.debt = this.getDebtAttribute(row);
                        return row;
                    });
        },
        getDebtAttribute(row) {
            let cost = Number(row.cost);
            return _.isVoid(row.paid) ? cost : (cost - Number(row.paid));
        },
        setTotal() {
            let rows = this.getTableRows();
            let amount = 0;
            if (rows.length !== 0) {
                amount = rows.reduce((total, row) => {
                    return total += Number(row.debt);
                }, 0);
            }
            this.totalAmount = amount;
        },
    },
}
</script>
