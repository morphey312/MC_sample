<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :flex-height="true"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot-scope="props"
            slot="analysis_names">
                {{ getNameRows(props.rowData.results, 'analysis_names') }}
        </template>
        <template
            slot-scope="props"
            slot="clinic_names">
                {{ getNameRows(props.rowData.results, 'clinic_names') }}
        </template>
        <template
            slot-scope="props"
            slot="laboratory_names">
                {{ getNameRows(props.rowData.results, 'laboratory_names') }}
        </template>
        <template
            slot-scope="props"
            slot="card_numbers">
                {{ getNameRows(props.rowData.results, 'card_numbers') }}
        </template>
        <template slot="analysis-info" slot-scope="props">
            <analysis-info :model="props.rowData" >
                <template slot="column">
                    {{ props.rowData.patient.full_name }}
                </template>
            </analysis-info>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import LaboratoryContainerRepository from '@/repositories/analysis/laboratory/container';
import ClinicRepository from '@/repositories/clinic';
import AnalysisInfo from './AnalysisDetails.vue';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import LaboratoryRepository from "@/repositories/analysis/laboratory";

export default {
    components: {
        AnalysisInfo,
    },
    data() {
        return {
            repository: new LaboratoryContainerRepository(),
            fields: [
                {
                    name: 'analysis_names',
                    title: __('Название анализов'),
                    width: '400px',
                },
                {
                    name: 'laboratory_names',
                    title: __('Лаборатории анализа(МЦ)'),
                    width: '150px',
                    /*
                    filter: new LaboratoryRepository({
                        accessLimit: this.$isAccessLimited('laboratory-orders'),
                    }),
                    filterField: 'laboratory',
                    filterProps: {
                        multiple: true,
                    },
                    */
                },
                {
                    name: 'created_at',
                    sortField: 'created_at',
                    title: __('Дата создания'),
                    width: '150px',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'clinic_names',
                    title: __('Клиника записи'),
                    width: '200px',
                   /* filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('laboratory-orders'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                    */
                },
                {
                    name: 'analysis-info',
                    title: __('Пациент'),
                    width: '250px',
                    filterField: 'patient_name',
                    filter: true,
                },
                {
                    name: 'card_numbers',
                    title: __('Номер карты'),
                    width: '150px',
                    filterField: 'card_number',
                    filter: false,
                },
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
            scopes: [
                'patient',
                'results',
            ],
            filters: {
                is_postponed: false,
                has_transfer_id: false,
                clinic: this.$store.state.user.clinics,
            }
        }
    },
    methods: {
        getNameRows(row, attribute) {
            let names = row.map(item => item[attribute]);
            return this.$formatter.listFormat(names);
        },
        showDetails(transfer) {
            this.$emit('show-details', transfer);
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
