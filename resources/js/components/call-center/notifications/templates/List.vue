<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
    >
        <template
            slot="clinic_names"
            slot-scope="props"
        >
            <span
            >{{ $formatter.listFormat(props.rowData.settings_clinic_names) }}</span>

            <span
            > {{ $formatter.listFormat( props.rowData.clinic_names)  }}</span>
        </template>
        <template slot="footer-top">
            <template slot="spacer">
            </template>
            <slot name="buttons"/>
        </template>
    </manage-table>
</template>

<script>
import NotificationTemplateRepository from '@/repositories/notification/template';

export default {
    data() {
        return {
            repository: new NotificationTemplateRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название'),
                    width: '20%',
                },
                {
                    name: 'parent_name',
                    sortField: 'parent_name',
                    title: __('Родительский шаблон'),
                    width: '15%',
                },
                {
                    name: 'scenario',
                    sortField: 'scenario',
                    title: __('Сценарий'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('notification_scenario', value);
                    },
                },
                {
                    name: 'channel_name',
                    sortField: 'channel_name',
                    title: __('Канал связи'),
                    width: '15%',
                },
                {
                    name: 'disabled',
                    sortField: 'disabled',
                    title: __('Неактивен'),
                    width: '10%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'clinic_names',
                    title: __('Клиники'),
                    width: '25%',
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
