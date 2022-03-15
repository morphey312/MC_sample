<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import AppointmentDeleteReasonRepository from '@/repositories/appointment/delete-reason';

export default {
    data() {
        return {
            repository: new AppointmentDeleteReasonRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    title: __('Причина удаления записи'),
                    width: '40%',
                    sortField: 'name',
                    filter: true,
                },
                {
                    name: 'include_to_report',
                    title: __('Включается в отчет'),
                    dataClass: 'no-dash',
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'includeToReport',
                },
                {
                    name: 'not_use_for_appointment_delete',
                    title: __('Не использовать'),
                    dataClass: 'no-dash',
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'notUseForAppointmentDelete',
                },
                {
                    name: 'comment_required',
                    title: __('Необходим комментарий'),
                    dataClass: 'no-dash',
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'commentRequired',
                },
            ],
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
            filters: {},
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
}
</script>