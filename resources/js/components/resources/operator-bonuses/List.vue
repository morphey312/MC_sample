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
        :table-headers="addHeaderRow"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import BonusNormRepository from '@/repositories/clinic/bonus-norm';
import FirstHeaderRow from './FirstHeaderRow.vue';

export default {
    data() {
        return {
            repository: new BonusNormRepository(),
            fields: [
                {
                    name: 'clinic_name',
                    sortField: 'clinic_name',
                    title: __('Клиника'),
                    width: '10%',
                },
                {
                    name: 'appointment_norm',
                    title: __('Записи'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'income_norm',
                    title: __('Приходу'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'mistakes_norm',
                    title: __('Кол-во ошибок'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'evaluation_norm',
                    title: __('Норма по оцен. листу, %'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'night_repeated_patient',
                    title: __('Ночные'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'day_repeated_patient',
                    title: __('Дневные'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'night_post_call',
                    title: __('Ночной'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'day_post_call',
                    title: __('Дневной'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'rate_minimum',
                    title: __('Минимальная'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'rate_medium',
                    title: __('Средняя'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'rate_maximum',
                    title: __('Максимальная'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                
            ],
            initialSortOrder: [
                {field: 'clinic_name', direction: 'asc'},
            ],
            filters: {},
            scopes: ['clinic'],
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        addHeaderRow(rows) {
            return [
                FirstHeaderRow,
                ...rows
            ];
        },
    },
}
</script>