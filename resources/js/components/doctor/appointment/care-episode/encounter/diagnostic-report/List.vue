<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        :enable-pagination="false"
        @selection-changed="selectionChanged"
        @loaded="loaded">
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        diagnostics: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.diagnostics.map(e => e.data),
                });
            }),
            fields: [
                {
                    name: 'category',
                    title: __('Категория диагностического отчета'),
                    width: '50%',
                    formatter: (value) => {
                        return this.$handbook.getOption('ehealth_diagnostic_report_categories', value);;
                    }
                },
                {
                    name: 'issued',
                    title: __('Дата предоставления услуг/получения отчета'),
                    width: '50%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    }
                },
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
    },
}
</script>
