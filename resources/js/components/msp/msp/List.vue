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
        <template
            slot="name"
            slot-scope="props" >
            <div class="has-icon">
                <span class="ellipsis">
                    {{ props.rowData.name }}
                </span>
                <svg-icon 
                    v-if="props.rowData.get('edr_data')"
                    name="info-alt" 
                    class="icon-tiny icon-grey"
                    @click.stop="showDetails(props.rowData)" />
            </div>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import MspRepository from '@/repositories/msp';
import EdrData from './form-tabs/Edr.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new MspRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '25%',
                    filter: true,
                },
                {
                    name: 'type',
                    sortField: 'type',
                    title: __('Тип'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('msp_type', value);
                    },
                    filter: 'msp_type',
                },
                {
                    name: 'edrpou',
                    sortField: 'edrpou',
                    title: __('Код ЕГРПОУ'),
                    width: '10%',
                    filter: true,
                },
                {
                    name: 'phone',
                    sortField: 'phone',
                    title: __('Номер телефона'),
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'address.address',
                    title: __('Адрес'),
                    width: '20%',
                },
                {
                    name: 'owner.full_name',
                    sortField: 'owner_full_name',
                    title: __('Руководитель'),
                    width: '15%',
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
        showDetails(model) {
            this.$modalComponent(EdrData, {
                model,
            }, {}, {
                header: __('Статус/Данные ЕГР'),
                width: '700px',
                customClass: 'no-footer',
            });
        },
    }
}
</script>
