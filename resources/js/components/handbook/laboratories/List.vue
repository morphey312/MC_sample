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
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import ClinicRepository from "@/repositories/clinic";

export default {
    data() {
        return {
            repository: new LaboratoryRepository({
                limitClinics: this.$isAccessLimited('laboratories')
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название'),
                    filter: true,
                    filterField: 'name',
                },
                {
                    name: 'clinic_names',
                    title: __('Клиники'),
                    width: '60%',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('laboratories')
                    }),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filterProps: {
                        multiple: true,
                    },
                    filterField: 'clinics',
                },
                {
                    name: 'disabled',
                    sortField: 'disabled',
                    title: __('Не использовать'),
                    width: '12%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'service_status',
                }
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {},
            scopes: [
                'clinics'
            ]
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
