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
        conditions: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.conditions.map(e => e.data),
                });
            }),
            fields: [
                {
                    name: 'clinical_status',
                    title: __('Клинический статус'),
                    width: '50%',
                    formatter: (value) => {
                        return this.$handbook.getOption('ehealth_condition_clinical_statuses', value);;
                    }
                },
                {
                    name: 'onset_date',
                    title: __('Дата установления диагноза'),
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
