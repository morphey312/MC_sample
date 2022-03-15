<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded">
        <template slot="footer-top">
            <template slot="spacer">
            </template>
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import NotificationMailingProvidersRepository from "../../../../repositories/notification/mailing-provider";

export default {
    data() {
        return {
            repository: new NotificationMailingProvidersRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '25%',
                },
                {
                    name: 'type',
                    sortField: 'type',
                    title: __('Тип'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('channel_type', value);
                    },
                },
                {
                    name: 'account_name',
                    sortField: 'account_name',
                    title: __('Аккаунт'),
                    width: '20%',
                },
                {
                    name: 'spacer',
                    title: '',
                    width: '40%',
                    dataClass: 'no-dash',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
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
