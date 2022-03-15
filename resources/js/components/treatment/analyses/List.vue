<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
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
import AnalysisRepository from '@/repositories/analysis';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        filters: Object,
    },
    data() {
        let laboratoriesRepo = new LaboratoryRepository();

        return {
            repository: new AnalysisRepository(),
            fields: [
                {
                    name: 'name',
                    sortField: 'name',
                    title: __('Название анализа'),
                    width: '20%',
                    dataClass: 'text-select',
                    filter: true,
                },
                {
                    name: 'description',
                    sortField: 'description',
                    title: __('Описание'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'laboratory_name',
                    sortField: 'laboratory',
                    filterField: 'laboratory',
                    title: __('Лаборатория'),
                    width: '10%',
                    filter: laboratoriesRepo,
                },
                {
                    name: 'laboratory_code',
                    sortField: 'laboratory_code',
                    title: __('Код анализа лаборатории'),
                    width: '12%',
                    filter: true,
                },
                {
                    name: 'clinics',
                    filterField: 'clinic_code',
                    title: __('Код анализа клиники'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value, 'code');
                    },
                    filter: true,
                },
                {
                    name: 'disabled',
                    sortField: 'disabled',
                    title: __('Не использовать'),
                    width: '15%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'service_status',
                },
                {
                    name: 'lab_analysis_id',
                    sortField: 'lab_analysis_id',
                    title: __('Используеться в ЛИС'),
                    width: '15%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'clinic_names',
                    filterField: 'clinic',
                    title: __('Клиники'),
                    width: '25%',
                    formatter: (value) => {
                        return value ? value.join(', ') : '';
                    },
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('analyses'),
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
            ],
            laboratoriesRepo,
            initialSortOrder: [
                {field: 'name', direction: 'asc'},
            ],
        };
    },
    watch: {
        ['filters.clinic']: {
            immediate: true,
            handler() {
                this.laboratoriesRepo.setFilters(this.getLaboratoriesFilters());
            }
        },
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
        getLaboratoriesFilters() {
            return _.onlyFilled({
                clinics: this.filters.clinic,
            });
        },
    },
}
</script>
