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
import AppointmentStatusRepository from '@/repositories/appointment/status';

export default {
    data() {
        return {
            repository: new AppointmentStatusRepository(),
            fields: [
                {
                    name: 'name_i18n',
                    title: __('Название статуса'),
                    width: '20%',
                    sortField: 'name',
                    filter: true,
                },
                {
                    name: 'color',
                    title: __('Цвет статуса'),
                    dataClass: 'td-color-wrapper',
                    width: '5%',
                    formatter: (value) => {
                        if (value) {
                            return `<div class="status-color" style="background: ${value}"></div>`;
                        }
                        return '';
                    },
                },
                {
                    name: 'comment_required',
                    title: __('Нужен комментарий'),
                    width: '10%',
                    sortField: 'commentRequired',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'commentRequired',
                },
                {
                    name: 'status_reason',
                    title: __('Нужна причина смены статуса'),
                    width: '10%',
                    sortField: 'statusReason',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'statusReason',
                },
                {
                    name: 'sms_for_card',
                    title: __('Нужен SMS пациенту'),
                    width: '10%',
                    sortField: 'smsForCard',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'smsForCard',
                },
                {
                    name: 'patient_card_required',
                    title: __('Нужно указать карту пациента'),
                    width: '10%',
                    sortField: 'patientCardRequired',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'patientCardRequired',
                },
                {
                    name: 'service_in_cost',
                    title: __('Услуги и анализы идут в долг пациенту'),
                    width: '10%',
                    sortField: 'serviceInCost',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'serviceInCost',
                },
                {
                    name: 'service_in_order',
                    title: __('Услуги и анализы идут в приходной ордер'),
                    width: '10%',
                    sortField: 'serviceInOrder',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'serviceInOrder',
                },
                {
                    name: 'is_active',
                    title: __('В специализации только одна первичная запись пациента'),
                    width: '11%',
                    sortField: 'isActive',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: this.$handbook.getOptions('yes_no'),
                    filterField: 'isActive',
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
