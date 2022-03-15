<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        table-height="150px">
        <template
            slot="status"
            slot-scope="props" >
            <form-select
                css-class="mb-0"
                :entity="props.rowData"
                :options="getOptions(props.rowData.initial_status)"
                property="status" />
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import CONSTANTS from '@/constants';

export default {
    props: {
        rows: Array,
    },
    data() {
        return {
            selectedRows: [],
            repository: new ProxyRepository(() => {
                return  Promise.resolve({
                    rows: this.rows.map((row) => {
                        row.initial_status = row.status;
                        return row;
                    }),
                });
            }),
            fields: [
                {
                    name: 'analysis.name',
                    title: __('Анализ'),
                    width: '200px',
                    dataClass: 'no-ellipsis',
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    width: '200px',
                },
                {
                    name: 'date_expected_pass',
                    title: __('Дата предп. сдачи'),
                    width: '150px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'date_pass',
                    title: __('Дата сдачи'),
                    width: '150px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'date_expected_ready',
                    title: __('Дата предп. готовности'),
                    width: '150px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'date_ready',
                    title: __('Дата готовности'),
                    width: '150px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'date_sent_email',
                    title: __('Дата отправки на email'),
                    width: '150px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'analysis.clinic.name',
                    title: __('Клиника'),
                    width: '150px',
                },
                {
                    name: 'analysis.clinic.code',
                    title: __('Код клиники'),
                    width: '150px',
                },
                {
                    name: 'analysis.laboratory_name',
                    title: __('Лаборатория'),
                    width: '150px',
                },
                {
                    name: 'analysis.laboratory_code',
                    title: __('Код лаборатории'),
                    width: '150px',
                },
            ],
            options: this.$handbook.getOptions('analysis_status'),
        }
    },
    methods: {
        getOptions(status) {
            return this.options.filter(option => {
                if (option.id == CONSTANTS.ANALYSIS_RESULT.STATUSES.TEST_IN_OTHER_LABORATORY ||
                    option.id == CONSTANTS.ANALYSIS_RESULT.STATUSES.ASSIGNED_BUT_NOT_BE_TEST || 
                    option.id == status) {
                    return option;
                }
            });
        },
    },
}
</script>