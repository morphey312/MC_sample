<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :flex-height="true"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
    </manage-table>
</template>

<script>
import DiagnosisRepository from '@/repositories/diagnosis';
import ServiceRepository from '@/repositories/service';

export default {
    data() {
        return {
            filters: {},
            repository: new DiagnosisRepository(),
            fields: [
                {
                    name: 'code',
                    sortField: 'code',
                    title: __('Код МКБ'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'description',
                    sortField: 'description',
                    title: __('Название'),
                    width: '30%',
                    filter: true,
                },
                {
                    name: 'service_names',
                    sortField: 'services',
                    filterField: 'services',
                    title: __('Услуги'),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    width: '50%',
                    filter: new ServiceRepository({ filters: { disabled: 0 } }),
                },
            ],
            initialSortOrder: [
                {field: 'code', direction: 'asc'},
            ],
            scopes: [
                'services'
            ]
        };
    },
    methods: {
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        loaded() {
            this.$emit('loaded');
        },
    },
};
</script>
