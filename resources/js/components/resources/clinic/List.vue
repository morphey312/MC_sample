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
import ClinicRepository from '@/repositories/clinic';
import CountryRepository from '@/repositories/country';
import ClinicGroupRepository from '@/repositories/clinic/group';
import MspRepository from '@/repositories/msp';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ClinicRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'official_name',
                    sortField: 'official_name',
                    title: __('Официальное название'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'city',
                    sortField: 'city',
                    title: __('Город'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('city', value);
                    },
                    filter: 'city',
                },
                {
                    name: 'country_name',
                    sortField: 'country',
                    filterField: 'country',
                    title: __('Страна'),
                    width: '10%',
                    filter: new CountryRepository(),
                    scopes: ['country'],
                },
                {
                    name: 'group_name',
                    sortField: 'group',
                    filterField: 'group',
                    title: __('Группа'),
                    width: '10%',
                    filter: new ClinicGroupRepository(),
                    scopes: ['group'],
                },
                {
                    name: 'status',
                    sortField: 'status',
                    title: __('Статус'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$handbook.getOption('active_status', value);
                    },
                    filter: 'active_status',
                },
                {
                    name: 'msp_name',
                    title: __('Предоставитель мед. услуг'),
                    width: '10%',
                    filterField: 'msp',
                    filter: new MspRepository(),
                    scopes: ['msp'],
                },
                {
                    name: 'voip_queue',
                    sortField: 'voip_queue',
                    title: __('Очередь звонков'),
                    width: '10%',
                    filter: 'voip_queue',
                },
                {
                    name: 'short_name',
                    sortField: 'short_name',
                    title: __('Краткое название'),
                    width: '10%',
                    filter: true,
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
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
            this.$emit('header-filter-updated', updates);
        },
    },
}
</script>
