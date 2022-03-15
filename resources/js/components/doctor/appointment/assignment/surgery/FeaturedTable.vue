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
            slot="add-selection"
            slot-scope="props" >
            <div class="has-icon">
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Выбрать услугу') }}</a>
            </div>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import Service from '@/models/appointment/service';
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
        featuredList: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getServices();
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
                    title: __('Название услуги'),
                },
                {
                    name: 'price',
                    title: __('Стоимость, грн'),
                    width: "100px",
                    dataClass: "text-right",
                    titleClass: "text-right",
                },
                {
                    name: 'self_cost',
                    title: __('Себестоимость, грн'),
                    width: "130px",
                    dataClass: "text-right",
                    titleClass: "text-right",
                },
                {
                    name: 'add-selection',
                    title: '',
                    width: "170px",
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
        getServices() {
            return Promise.resolve({
                rows: this.filteredResults,
            });
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