<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto"
        @header-filter-updated="updateList" >
        <template
            slot="featured"
            slot-scope="props" >
            <featured 
                :model="props.rowData" 
                :featured-list="featuredList"
                @featured-changed="featuredChanged" />
        </template>
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
import ProxyRepository from '@/repositories/proxy-repository';
import Result from '@/models/analysis/result';
import Featured from './Featured.vue';
import TableFilter from '@/mixins/appointment/analysis/static-table-filter';

export default {
    mixins: [
        TableFilter
    ],
    components: {
        Featured,
    },
    props: {
        filters: Object,
        featuredList: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getAnalysis();
            }),
            fields: [
                {
                    name: 'featured',
                    title: '',
                    width: "30px",
                    dataClass: 'text-center',
                },
                {
                    name: 'analysis.laboratory_code',
                    title: __('Код лаборатории'),
                    width: "120px",
                    filter: true,
                    filterField: 'laboratory_code',
                },
                {
                    name: 'analysis.laboratory_name',
                    title: __('Название лаборатории'),
                    width: "150px",
                    filter: true,
                    filterField: 'laboratory_name',
                },
                {
                    name: 'analysis.clinic.code',
                    title: __('Код клиники'),
                    width: "90px",
                    filter: true,
                    filterField: 'clinic_code',
                },
                {
                    name: 'analysis.name',
                    title: __('Название анализов'),
                    filter: true,
                    filterField: 'name',
                },
                {
                    name: 'analysis.clinic.duration_days',
                    title: __('Кол-во дней на анализ'),
                    width: "150px",
                },
                {
                    name: 'analysis.price',
                    title: __('Стоимость, грн'),
                    width: "100px",
                },
                
                {
                    name: 'add-analysis',
                    title: '',
                    width: "130px",
                    dataClass: 'text-right',
                },
            ],
            filteredResults: this.featuredList,
        };
    },
    watch: {
        ['featuredList']() {
            this.filteredResults = this.featuredList;
            this.refresh();
        },
    },
    methods: {
        getAnalysis() {
            return Promise.resolve({
                rows: this.filteredResults,
            });
        },
        createResultModel(data) {
            let result = new Result();
            result.castAnalysisDataToEntity(data, this.filters);
            return result;
        },
        updateList(updates) {
            this.syncFilters(updates);
            this.filteredResults = this.filterResults([...this.featuredList],  _.onlyFilled(this.filter));
            this.refresh();
        },
        toggleSelection(row) {
            this.$emit('selection-changed', {row});
        },
        featuredChanged(result) {
            this.$emit('featured-changed', result);
        },
    },
}   
</script>