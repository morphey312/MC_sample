<template>
    <page
        :title="__('Журнал заявок в eHealth')"
        type="flex">
        <section class="grey-cap shrinkable">
            <manage-table 
                ref="table"
                :fields="fields"
                :filters="filters"
                :repository="repository"
                :initial-sort-order="initialSortOrder"
                :flex-height="true"
                @loaded="loaded"
                @header-filter-updated="syncFilters">
                <template slot="statusLink" slot-scope="props">
                    <a 
                        href="#"
                        @click.prevent="showDetails(props.rowData)">
                        {{ $handbook.getOption('ehealth_application_status', props.rowData.status) }}
                    </a>
                </template>
            </manage-table>
        </section>
    </page>
</template>


<script>
import EhealthApplicationRepository from '@/repositories/ehealth/application';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import EmployeeRepository from '@/repositories/employee';
import Details from './Details.vue';

export default {
    data() {
        return {
            repository: new EhealthApplicationRepository(),
            fields: [
                {
                    name: 'statusLink',
                    sortField: 'status',
                    filterField: 'status',
                    title: __('Статус'),
                    width: '20%',
                    filter: 'ehealth_application_status',
                },
                {
                    name: 'subject_type',
                    sortField: 'subject_type',
                    title: __('Тип'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$handbook.getOption('ehealth_application_type', value);
                    },
                    filter: 'ehealth_application_type',
                },
                {
                    name: 'action',
                    sortField: 'action',
                    title: __('Действие'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$handbook.getOption('ehealth_application_action', value);
                    },
                    filter: 'ehealth_application_action',
                },
                {
                    name: 'employee_name',
                    sortField: 'employee_name',
                    filterField: 'employee',
                    title: __('Отправитель'),
                    width: '25%',
                    filter: new EmployeeRepository(),
                },
                {
                    name: 'date',
                    sortField: 'date',
                    title: __('Дата'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
            ],
            initialSortOrder: [
                {field: 'date', direction: 'desc'},
            ],
            filters: {
            },
        };
    },
    methods: {
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        showDetails(item) {
            this.$modalComponent(Details, {
                item,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Детали запроса'),
                width: '800px',
                customClass: 'no-footer',
            });
        }
    },
};
</script>