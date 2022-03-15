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
import InformationSourceRepository from '@/repositories/patient/information-source';
import ClinicRepository from '@/repositories/clinic';

export default {
    data() {
        return {
            repository: new InformationSourceRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    sortField: 'name',
                    title: __('Название'),
                    width: '25%',
                    filter: true,
                },
                {
                    name: 'media_type',
                    sortField: 'media_type',
                    title: __('Вид рекламы'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$handbook.getOption('media_type', value);
                    },
                    filter: 'media_type',
                },
                {
                    name: 'is_active',
                    sortField: 'is_active',
                    title: __('Активен'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'is_collective_form',
                    sortField: 'is_collective_form',
                    title: __('Собирательный'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'clinic_names',
                    filterField: 'clinic',
                    title: __('Клиники'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('information-sources'),
                    }),
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {
                is_active: 1,
            },
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