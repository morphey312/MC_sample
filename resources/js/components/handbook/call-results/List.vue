<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
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
import CallResultRepository from '@/repositories/calls/result';
import FirstHeaderRow from './FirstHeaderRow.vue';

export default {
    data() {
        return {
            repository: new CallResultRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    title: __('Результат звонка'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'use_for_new_calls',
                    title: __('Новых контактов'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'useForNewCall',
                },
                {
                    name: 'use_for_statistics',
                    title: __('Статистики'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'useForStatistic',
                },
                {
                    name: 'use_for_is_first_patient',
                    title: __('Первичных пациентов'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'useForIsPatientFirst',
                },
                {
                    name: 'use_for_repeated_patient',
                    title: __('Повторных пациентов'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'useForRepeatedPatient',
                },
                {
                    name: 'use_for_unspecialized_patient',
                    title: __('Непрофильных пациентов'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'useForUnspecializedPatient',
                },
                {
                    name: 'use_for_not_patient',
                    title: __('Не пациентов'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'useForNotPatient',
                },
                {
                    name: 'esputnik_no_answer',
                    title: __('Отслеживания недозвонов в eSputnik'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'esputnikNoAnswer',
                },
                {
                    name: 'for_wait_list',
                    title: __('Для листов ожидания'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'forWaitList',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {},
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
