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
            slot="add-medicine"
            slot-scope="props" >
            <div class="pr-20">
                <a 
                    v-if="props.rowData.rests > 0"
                    href="#" 
                    @click.prevent="toggleSelection(props.rowData)">
                    {{ __('Выбрать медикамент') }}
                </a>
            </div>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import Result from '@/models/analysis/result';
import Featured from './Featured.vue';
import AssignedMedicine from '@/models/patient/assigned-medicine';

export default {
    components: {
        Featured,
    },
    props: {
        clinic: Number,
        featuredList: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getMedicines();
            }),
            fields: [
                {
                    name: 'featured',
                    title: '',
                    width: "30px",
                    dataClass: 'text-center',
                },
                {
                    name: 'name',
                    title: __('Название медикамента'),
                    filter: true,
                    filterField: 'name',
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн.'),
                    width: "98px",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'rests',
                    title: __('Остаток на складе'),
                    width: "100px",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'add-medicine',
                    title: '',
                    width: "270px",
                    dataClass: 'text-right',
                },
            ],
            filter: {
                name: null,
            },
            filteredResults: this.featuredList,
        };
    },
    watch: {
        ['featuredList']() {
            this.filteredResults = [];
            this.filteredResults = this.featuredList;
            this.refresh();
        },
    },
    methods: {
        getMedicines() {
            return Promise.resolve({
                rows: this.filteredResults,
            });
        },
        updateList(updates) {
            this.syncFilters(updates);
            this.filteredResults = this.filterResults(this.featuredList,  _.onlyFilled(this.filter));
            this.refresh();
        },
        filterResults(results, filters) {
            if (!_.isEmpty(filters) && results.length !== 0) {
                Object.keys(filters).forEach((key) => {
                    results = results.filter((item) => {
                        let field = item[key];
                        return field.toLowerCase().includes(filters[key].toLowerCase());
                    });
                });
            }
            return results;
        },
        refresh() {
            this.$refs.table.refresh();
        },
        syncFilters(updates) {
            this.filter = {...this.filter, ...updates};
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