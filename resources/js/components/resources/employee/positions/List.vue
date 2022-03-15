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
        <template slot="spacer">
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import PositionRepository from '@/repositories/employee/position';

export default
{
    data() {
        return {
            repository: new PositionRepository({
                limitClinics: this.$isAccessLimited('positions')
            }),
            fields: [
                {
                    name: 'name_i18n',
                    sortField: 'name',
                    width: '22%',
                    title: __('Название'),
                    filter: true,
                },
                {
                    name: 'has_specialization',
                    sortField: 'hasSpecialization',
                    title: __('Специализации'),
                    width: '12%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
                {
                    name: 'has_voip',
                    sortField: 'has_voip',
                    title: __('Телефония'),
                    width: '10%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
                {
                    name: 'is_doctor',
                    sortField: 'is_doctor',
                    title: __('Врач'),
                    width: '8%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
                {
                    name: 'is_operator',
                    sortField: 'is_operator',
                    title: __('Оператор'),
                    width: '8%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
                {
                    name: 'is_superviser',
                    sortField: 'is_superviser',
                    title: __('Супервайзер'),
                    width: '10%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
                {
                    name: 'is_cashier',
                    sortField: 'is_cashier',
                    title: __('Кассир'),
                    width: '8%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
                {
                    name: 'is_marketing',
                    sortField: 'is_marketing',
                    title: __('Маркетинг'),
                    width: '10%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
                {
                    name: 'is_reception',
                    sortField: 'is_reception',
                    title: __('Регистратура'),
                    width: '12%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
                {
                    name: 'is_collector',
                    sortField: 'is_collector',
                    title: __('Коллектор'),
                    width: '10%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
                {
                    name: 'is_surgery',
                    sortField: 'is_surgery',
                    title: __('У чавствует в операции'),
                    width: '10%',
                    filter: 'yes_no',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {
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
