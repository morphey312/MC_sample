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
import MoneyRecieverRepository from '@/repositories/clinic/money-reciever';

export default {
    data() {
        return {
            repository: new MoneyRecieverRepository({
                limitClinics: this.$isAccessLimited('money-reciever')
            }),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '25%',
                    filter: true,
                },
                {
                    name: 'clinic_names',
                    title: __('Клиники'),
                    width: '25%',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                {
                    name: 'signer',
                    sortField: 'signer',
                    title: __('Подписант'),
                    width: '25%',
                    filter: true,
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {},
            scopes: [
                'clinics'
            ],
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
