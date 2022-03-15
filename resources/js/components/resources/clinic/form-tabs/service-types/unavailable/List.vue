<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded">
        <template
            slot="from"
            slot-scope="props" >
            {{ $formatter.datetimeFormat(`${props.rowData.date_from} ${props.rowData.time_from}:00`) }}
        </template>
        <template
            slot="to"
            slot-scope="props" >
            {{ $formatter.datetimeFormat(`${props.rowData.date_to} ${props.rowData.time_to}:00`) }}
        </template>
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        model: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.model.not_available,
                });
            }),
            fields: [
                {
                    name: 'from',
                    title: __('С'),
                    width: '20%',
                },
                {
                    name: 'to',
                    title: __('По'),
                    width: '20%',
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                }
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
    }
}
</script>