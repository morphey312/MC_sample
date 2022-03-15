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
        procedures: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.procedures.map(e => e.data),
                });
            }),
            fields: [
                {
                    name: 'category',
                    title: __('Категория процедуры'),
                    width: '50%',
                    formatter: (value) => {
                        return this.$handbook.getOption('ehealth_procedure_categories', value);;
                    }
                },
                {
                    name: 'performed_date_time',
                    title: __('Дата и время'),
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
