<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
        <template
            slot="name"
            slot-scope="props" >
            <div class="has-icon">
                <span class="ellipsis">
                    {{ props.rowData.name_i18n }}
                </span>
                <svg-icon
                	v-if="props.rowData.store_rests.length !== 0"
                    name="info-alt"
                    class="icon-tiny icon-grey"
                    @click.stop="showDetails(props.rowData)" />
            </div>
        </template>
    </manage-table>
</template>

<script>
import MedicineRepository from '@/repositories/medicine';
import RestDetails from './Details.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new MedicineRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Медикамент'),
                    width: '25%',
                    filter: true,
                    filterField: 'name_i18n',
                },
                {
                    name: 'store_list',
                    title: __('Склады'),
                    width: '40%',
                },
                {
                    name: 'measure',
                    title: __('Единица'),
                    width: '10%',
                },
                {
                    name: 'code',
                    title: __('Код'),
                    width: '10%',
                    filter: true,
                },
                {
                    name: 'type',
                    title: __('Тип'),
                    width: '15%',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
        };
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
        showDetails(medicine) {
            this.$modalComponent(RestDetails, {
                medicine,
            }, {}, {
                header: medicine.name,
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
}
</script>
