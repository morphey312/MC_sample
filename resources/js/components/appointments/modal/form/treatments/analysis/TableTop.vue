<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        table-height="auto"
        @loaded="loaded"
        @header-filter-updated="syncFilters" >
        <template
            slot="add-analysis"
            slot-scope="props" >
            <div class="has-icon">
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Добавить анализ') }}</a>
            </div>
        </template>
    </manage-table>
</template>

<script>
import AnalysisRepository from '@/repositories/analysis';
import ProxyRepository from '@/repositories/proxy-repository';
import Result from '@/models/analysis/result';
import CreateResultMixin from '@/mixins/appointment/analysis/create-result';
import LaboratoryRepository from '@/repositories/analysis/laboratory';

export default {
    mixins: [
        CreateResultMixin,
    ],
    props: {
        filters: Object,
        insurancePolicy: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, page, limit}) => {
                return this.getAnalysis(sort);
            }),
            fields: [
                {
                    name: 'analysis.laboratory_code',
                    title: __('Код лаборатории'),
                    width: "10%",
                    filter: true,
                    filterField: 'laboratoryCode',
                },
                {
                    name: 'analysis.laboratory_name',
                    sortField: 'laboratory',
                    title: __('Лаборатория'),
                    width: '10%',
                    dataClass: 'no-dash',
                    filterField: 'laboratory',
                    filter: new LaboratoryRepository(),
                },
                {
                    name: 'analysis.clinic.code',
                    title: __('Код клиники'),
                    width: "10%",
                    filter: true,
                    filterField: 'clinicCode',
                },
                {
                    name: 'analysis.name',
                    title: __('Название анализов'),
                    width: "35%",
                    filter: true,
                    filterField: 'name',
                },
                {
                    name: 'analysis.price',
                    title: __('Стоимость, грн'),
                    width: "10%",
                },
                {
                    name: 'analysis.clinic.duration_days',
                    title: __('Кол-во дней на анализ'),
                    width: "10%",
                },
                {
                    name: 'add-analysis',
                    title: '',
                    width: "15%",
                },
            ],
        };
    },
    methods: {
        getAnalysis(sort = []) {
            let repo = new AnalysisRepository();
            let params = {
                sort: [
                    ...sort,
                    { field: 'laboratory_clinic_priority', direction : 'acs'}
                ]};
            if (this.insurancePolicy) {
                params.withInsurer = this.insurancePolicy.insurance_company_id;
            }
            return repo.fetchListForAppointment(this.filters, params).then((response) => {
                return Promise.resolve({
                    rows: response.map((row) => this.createResultModel(row, this.filters))
                });
            });
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        toggleSelection(row) {
            this.$emit('selection-changed', {row});
        },
    }
}
</script>
