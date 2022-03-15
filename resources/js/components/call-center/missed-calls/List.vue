<template>
    <manage-table 
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        @header-filter-updated="syncFilters">
        <template 
            slot="phone_number" 
            slot-scope="props">
            <a 
                href="#"
                @click.prevent="selectCaller(props.rowData)">
                {{ $formatter.phoneNumberFormat(props.rowData.phone_number) }}
            </a>
        </template>
        <template slot="spacer">
        </template>
    </manage-table>
</template>


<script>
import CallLogRepository from '@/repositories/calls/call-log';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import SelectContactMixin from '../mixins/select-contact';

export default {
    mixins: [
        SelectContactMixin,
    ],
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new CallLogRepository(),
            fields: [
                {
                    name: 'phone_number',
                    sortField: 'phone_number',
                    title: __('Номер телефона'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'started_at',
                    sortField: 'started_at',
                    title: __('Дата звонка'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    'filter': DateHeaderFilter,
                },
                {
                    name: 'queue',
                    sortField: 'queue',
                    title: __('Очередь'),
                    width: '15%',
                    filter: 'voip_queue',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'process.status',
                    sortField: 'process_status',
                    filterField: 'process_status',
                    title: __('Статус обработки'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('call_process_status', value);
                    },
                    filter: 'missed_call_status',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'process.operator.name',
                    sortField: 'operator_name',
                    filterField: 'operator_name',
                    title: __('Оператор'),
                    width: '15%',
                    filter: true,
                },
                {
                    name: 'spacer',
                    title: '',
                    width: '25%',
                    dataClass: 'no-dash',
                },
            ],
            initialSortOrder: [
                {field: 'started_at', direction: 'desc'},
            ],
            scopes: [
                'patient',
                'process',
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        selectCaller(call) {
            if (call.patient !== null) {
                this.selectPatientContact(call.patient);
            } else {
                this.selectUnknownContact(call.phone_number);
            }
        },
    },
};
</script>