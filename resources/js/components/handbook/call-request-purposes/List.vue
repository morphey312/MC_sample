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
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import CallRequestPurposeRepository from '@/repositories/call-request/purpose';

export default {
    data() {
        return {
            repository: new CallRequestPurposeRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    title: __('Цель прозвона'),
                    width: '34%',
                    filter: true,
                },
                {
                    name: 'auto_add',
                    title: __('Цель для автоматического добавления первичных пациентов'),
                    width: '33%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'autoAdd',
                },
                {
                    name: 'manual_add',
                    title: __('Цель для операторов по умолчанию (ручное добавление)'),
                    width: '33%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'manualAdd',
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
    },
}
</script>