<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <template slot="spacer">
            </template>
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import CallDeleteReasonRepository from '@/repositories/calls/delete-reason';

export default {
    data() {
        return {
            repository: new CallDeleteReasonRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    title: __('Причина удаления звонка'),
                    filter: true,
                    width: '20%',
                },
                {
                    name: 'include_to_report',
                    title: __('Включается в отчет'),
                    dataClass: 'no-dash',
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'use_for_delete',
                    title: __('Не использовать'),
                    dataClass: 'no-dash',
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'comment_required',
                    title: __('Необходим комментарий'),
                    dataClass: 'no-dash',
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'spacer',
                    title: '',
                    width: '35%',
                    dataClass: 'no-dash',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {}
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
    },
}
</script>