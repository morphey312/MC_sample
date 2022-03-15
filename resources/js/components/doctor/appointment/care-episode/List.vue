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
        episodes: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.episodes.map(e => e.data),
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название'),
                    width: '40%',
                },
                {
                    name: 'type',
                    title: __('Тип'),
                    width: '40%',
                    formatter: (value) => {
                        return this.$handbook.getOption('ehealth_episode_type', value);
                    }
                },
                {
                    name: 'date_start',
                    title: __('Дата начала'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
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