<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        @header-filter-updated="syncFilters">
        <template slot="statusLink" slot-scope="props">
            <a
                href="#"
                @click.prevent="showDetails(props.rowData)">
                {{ formatStatus(props.rowData.status) }}
            </a>
        </template>
    </manage-table>
</template>


<script>
import ProcessLogRepository from '@/repositories/calls/process-log';
import Details from './Details.vue';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS  from '@/constants';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ProcessLogRepository(),
            fields: [
                {
                    name: 'statusLink',
                    sortField: 'status',
                    filterField: 'status',
                    title: __('Статус'),
                    width: '15%',
                    filter: 'call_process_status',
                },
                {
                    name: 'status_comment',
                    title: __('Примечание'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'is_patient',
                    sortField: 'is_patient',
                    title: __('Пациент'),
                    width: '8%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'call_process_is_patient',
                },
                {
                    name: 'is_first_visit',
                    sortField: 'is_first_visit',
                    title: __('Первичный визит'),
                    width: '12%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'call_process_visit_type',
                },
                {
                    name: 'source',
                    sortField: 'source',
                    title: __('Источник обработки'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$handbook.getOption('call_log_source', value);
                    },
                    filter: 'call_log_source',
                },
                {
                    name: 'operator.name',
                    sortField: 'operator_name',
                    filterField: 'operator',
                    title: __('Оператор'),
                    width: '12%',
                    filter: new EmployeeRepository({
                        filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR},
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'started_at',
                    sortField: 'started_at',
                    title: __('Время начала'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'processed_at',
                    sortField: 'processed_at',
                    title: __('Время завершения'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'auto_process',
                    title: __('Автоматическое завершение'),
                    width: '20%',
                    formatter: (value) => {
                        return value !== null
                            ? [
                                value.operator.name,
                                ', ',
                                this.$formatter.datetimeFormat(value.started_at),
                                '&mdash;',
                                this.$formatter.datetimeFormat(value.processed_at),
                            ].join('')
                            : null;
                    },
                },
            ],
            initialSortOrder: [
                {field: 'started_at', direction: 'desc'},
            ],
            scopes: [
                'default',
                'auto_process',
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        formatStatus(value) {
            return this.$handbook.getOption('call_process_status', value);
        },
        showDetails(item) {
            this.$modalComponent(Details, {
                item,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Детали обработки'),
                width: '1000px',
                customClass: 'no-footer',
            });
        },
    },
};
</script>
