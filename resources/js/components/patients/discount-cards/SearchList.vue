<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :row-class="onRowClass"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import IssuedDiscountCardRepository from '@/repositories/patient/issued-discount-card';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new IssuedDiscountCardRepository(),
            fields: [
                {
                    name: 'card_number',
                    title: __('Номер карты'),
                    width: '100px',
                    dataClass: 'text-right no-dash',
                },
                {
                    name: 'owner',
                    title: __('Пациент'),
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return [val.lastname, val.firstname, val.middlename]
                                .filter(_.isFilled)
                                .join(' ');
                    },
                },
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    width: '120px',
                    dataClass: 'no-dash',
                },
                {
                    name: 'type.max_owners',
                    title: __('Максимальное кол-во пользователей'),
                    width: '150px',
                    dataClass: 'text-right no-dash',
                },
                {
                    name: 'active_holders',
                    title: __('Кол-во пользователей'),
                    width: '80px',
                    dataClass: 'text-right no-dash',
                },
            ],
            initialSortOrder: [
                {field: 'card_number', direction: 'asc'},
            ],
        }
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        onRowClass(dataItem, index) {
            if (dataItem.active_holders >= dataItem.type.max_owners) {
                return 'events-none';
            }
        },
    },
}
</script>
