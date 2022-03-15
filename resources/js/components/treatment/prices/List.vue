<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
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
import PriceRepository from '@/repositories/price';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import RangeHeaderFilter from '@/components/general/table/RangeHeaderFilter.vue';

export default {
    props: {
        filters: {
            type: Object,
            default: () => ({}),
        },
        premissions: String,
    },
    data() {
        return {
            fields: [
                {
                    name: 'date_from',
                    sortField: 'date_from',
                    title: __('Дата начала'),
                    width: '14%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'date_to',
                    sortField: 'date_to',
                    title: __('Дата окончания'),
                    width: '14%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'cost',
                    sortField: 'cost',
                    title: __('Стоимость'),
                    width: '14%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                    filter: RangeHeaderFilter,
                },
                {
                    name: 'self_cost',
                    sortField: 'self_cost',
                    title: __('Себестоимость'),
                    width: '14%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                    filter: RangeHeaderFilter,
                },
                {
                    name: 'currency',
                    sortField: 'currency',
                    title: __('Валюта'),
                    width: '14%',
                    formatter: (value) => {
                        return this.$handbook.getOption('currency', value);
                    },
                    filter: 'currency',
                },
                {
                    name: 'clinic_names',
                    filterField: 'clinic',
                    title: __('Клиники, в которых действует тариф'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited(this.premissions),
                        filters: this.getClinicsFilters(),
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
            ],
            initialSortOrder: [
                {field: 'date_from', direction: 'desc'},
            ],
            repository: new PriceRepository(),
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
        getClinicsFilters() {
            if (this.filters.service !== undefined) {
                return {has_service: this.filters.service};
            }
            if (this.filters.analysis !== undefined) {
                return {has_analysis: this.filters.analysis};
            }
            return {};
        },
    },
};
</script>
