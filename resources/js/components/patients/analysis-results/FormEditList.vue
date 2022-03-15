<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        table-height="150px" />
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        rows: Array,
    },
    data() {
        return {
            selectedRows: [],
            repository: new ProxyRepository(() => {
                return  Promise.resolve({
                            rows: this.rows
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
                    formatter: (val) => {
                        return this.$handbook.getOption('analysis_status', val);
                    },
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
        }
    },
}
</script>