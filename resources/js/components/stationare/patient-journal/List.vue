<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :scopes="scopes"
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
import ProxyRepository from '@/repositories/proxy-repository';
import TreatmentCourseRepository from '@/repositories/treatment-course';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';

export default {
    props: {
        filters: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repo = new TreatmentCourseRepository();
                return repo.fetchJournalList(filters, sort, scopes, page, limit);
            }),
            fields: [
                {
                    name: 'start',
                    sortField: 'date_start',
                    title: __('Дата'),
                    dataClass: 'no-dash',
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value, 'DD/MM/YYYY');
                    },
                    filterField: 'date_from',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'start_time',
                    title: __('Время'),
                    dataClass: 'no-dash',
                    width: '80px',
                },
                {
                    name: 'patient_full_name',
                    title: __('ФИО'),
                    dataClass: 'no-dash',
                    width: '200px',
                },
                {
                    name: 'patient_birthday',
                    title: __('Дата рождения'),
                    dataClass: 'no-dash',
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value, 'DD/MM/YYYY');
                    },
                },
                {
                    name: 'patient_full_address',
                    title: __('Место проживания'),
                    dataClass: 'no-dash',
                    width: '100px',
                },
                {
                    name: 'reffered',
                    title: __('Направлен'),
                    dataClass: 'no-dash',
                    width: '100px',
                },
                {
                    name: 'specialization_name',
                    title: __('Отделение'),
                    dataClass: 'no-dash',
                    width: '100px',
                },
                {
                    name: 'patient_card_number',
                    title: __('Номер карты'),
                    dataClass: 'no-dash',
                    width: '100px',
                    filterField: 'patient_card_number',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'initial_diagnosis',
                    title: __('Диагноз при направлении'),
                    dataClass: 'no-dash',
                    width: '150px',
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    },
                },
                {
                    name: 'final_diagnosis',
                    title: __('Диагноз при выписке'),
                    dataClass: 'no-dash',
                    width: '150px',
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    },
                },
                {
                    name: 'patient_status',
                    title: __('Статус больного'),
                    dataClass: 'no-dash',
                    width: '150px',
                },
                {
                    name: 'end',
                    title: __('Дата выписки'),
                    sortField: 'date_end',
                    width: '70px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value, 'DD/MM/YYYY');
                    },
                    filterField: 'date_to',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'refuse_reason',
                    title: __('Причина отказа от лечения'),
                    dataClass: 'no-dash',
                    width: '120px',
                    formatter: (value) => {
                        return value ? this.$handbook.getOption('reason_refusing_treatment', value) : '';
                    },
                },
                {
                    name: 'treatment_event',
                    title: __('Мероприятия'),
                    dataClass: 'no-dash',
                    width: '100px',
                },
                {
                    name: 'services',
                    title: __('Описание мероприятий'),
                    dataClass: 'no-dash',
                    width: '200px',
                },
            ],
            initialSortOrder: [
                {field: 'date_start', direction: 'asc'},
            ],
            scopes: [
                'patient',
                'specialization',
            ]
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
            this.$emit('header-filter-updated', updates);
        },
    }
}
</script>
