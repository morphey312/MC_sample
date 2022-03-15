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
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import DoctorIncomePlanRepository from '@/repositories/employee/doctor-income-plan';
import ClinicRepository from '@/repositories/clinic';
import YearHeaderFilter from '@/components/general/table/YearHeaderFilter.vue';
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repository = new DoctorIncomePlanRepository();
                return repository.fetch(filters, sort, scopes, page, limit).then((result) => {
                    return {
                        rows: this.prepareRows(result.rows),
                        pagination: result.pagination,
                    }
                });
            }),
            
            fields: [
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    width: '5%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('appointments'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'specialization',
                    title: __('Специализация'),
                    width: '5%',
                    formatter: (val) => {
                        let mark = val['plan_service_mark'] ? this.$handbook.getOption('doctor_plan_service_mark', val['plan_service_mark']) : null;
                        return val['name'] + (mark ? (' ' + mark) : '');
                    },
                },
                {
                    name: 'doctor_name',
                    title: __('Врач'),
                    width: '15%',
                },
                {
                    name: 'year',
                    title: __('Год'),
                    width: '5%',
                    filter: YearHeaderFilter,
                    filterField: 'year',
                },
                {
                    name: 'january',
                    title: __('Январь'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'february',
                    title: __('Февраль'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'march',
                    title: __('Март'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'april',
                    title: __('Апрель'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'may',
                    title: __('Май'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'june',
                    title: __('Июнь'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'july',
                    title: __('Июль'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'august',
                    title: __('Август'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'september',
                    title: __('Сентябрь'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'october',
                    title: __('Октябрь'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'november',
                    title: __('Ноябрь'),
                    width: '5%',
                    dataClass: 'text-right',
                },
                {
                    name: 'december',
                    title: __('Декабрь'),
                    width: '5%',
                    dataClass: 'text-right',
                },
            ],
            initialSortOrder: [
                {field: 'specialization_name', direction: 'asc'},
            ],
            scopes: [
                'clinic',
                'specialization',
                'doctor'
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        prepareRows(resultRows) {
            let totalRow = {
                clinic_name: __('Итого'),
                specialization: {name: ''},
                doctor_name: ''
            };
            totalRow.january = this.sumTotal(resultRows, 'january');
            totalRow.february = this.sumTotal(resultRows, 'february');
            totalRow.march = this.sumTotal(resultRows, 'march');
            totalRow.april = this.sumTotal(resultRows, 'april');
            totalRow.may = this.sumTotal(resultRows, 'may');
            totalRow.june = this.sumTotal(resultRows, 'june');
            totalRow.july = this.sumTotal(resultRows, 'july');
            totalRow.august = this.sumTotal(resultRows, 'august');
            totalRow.september = this.sumTotal(resultRows, 'september');
            totalRow.october = this.sumTotal(resultRows, 'october');
            totalRow.november = this.sumTotal(resultRows, 'november');
            totalRow.december = this.sumTotal(resultRows, 'december');
            resultRows.push(totalRow);
            return resultRows;
        },
        sumTotal(rows, monthName) {
            return rows.reduce((sum, row) => {
                return sum + Number(row[monthName]);
            }, 0);
        },
    },
}
</script>