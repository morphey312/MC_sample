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
import ClinicRepository from '@/repositories/clinic';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';

export default {
    data() {
        return {
            repository: new PaymentDestinationRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    sortField: 'name',
                    title: __('Назначение платежа'),
                    width: '40%',
                    filter: true,
                },
                {
                    name: 'color',
                    title: __('Цвет строк'),
                    width: '10%',
                    formatter: (value) => {
                        if (value) {
                            return `<div class="status-color" style="background: ${value}">&nbsp;</div>`;
                        }
                        return '';
                    },
                },
                {
                    name: 'clinic_names',
                    filterField: 'clinic',
                    title: __('Клиники'),
                    width: '50%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value, false, true);
                    },
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('limitations'),
                    }),
                    filterProps: {
                        multiple: true,
                    },
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
