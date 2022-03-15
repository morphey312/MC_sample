<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        table-height="200px"
        @loaded="loaded"
        @header-filter-updated="syncFilters" >
        <template
            slot="add-analysis"
            slot-scope="props" >
            <div class="has-icon">
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Выбрать') }}</a>
            </div>
        </template>
    </manage-table>
</template>

<script>
import AnalysisRepository from '@/repositories/analysis';
import ProxyRepository from '@/repositories/proxy-repository';

export default {
     props: {
        filters: Object,
        laboratories: {
            type: Array,
            default: () => []
        },
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, sort, page, limit}) => {
                return this.getAnalysis(sort);
            }),
            fields: [
                {
                    name: 'laboratory_code',
                    title: __('Код лаборатории'),
                    width: "10%",
                    filter: true,
                    filterField: 'laboratoryCode',
                },
                {
                    name: 'laboratory_name',
                    sortField: 'laboratory',
                    title: __('Лаборатория'),
                    width: '10%',
                    dataClass: 'no-dash',
                    filterField: 'laboratory',
                     filterProps: {
                        multiple: true,
                    },
                    filter: this.laboratories,
                    
                },
                {
                    name: 'clinic_names',
                    title: __('Клиники'),
                    width: "10%",
                    filter: true,
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    },
                    filterField: 'clinicCode',
                },
                {
                    name: 'name',
                    title: __('Название анализов'),
                    width: "35%",
                    filter: true,
                    filterField: 'name',
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
            return repo.fetch(this.filters, params).then((response) => {
                return Promise.resolve({
                     rows: response.rows
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
