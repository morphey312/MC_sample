<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        table-height="auto">
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
            <div class="pr-20">
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Выбрать специализацию') }}</a>
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
        featuredList: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getList();
            }),
            fields: [
                {
                    name: 'featured',
                    title: '',
                    width: "30px",
                    dataClass: 'text-center',
                },
                {
                    name: 'specialization_name',
                    title: __('Спецаилизация'),
                    dataClass: 'text-left',
                },
                {
                    name: 'add-selection',
                    title: '',
                    width: "200px",
                    dataClass: 'text-center',
                },
            ],
            filteredList: this.featuredList,
        };
    },
    watch: {
        ['featuredList']() {
            this.filteredList = this.featuredList;
            this.refresh();
        },
    },
    methods: {
        getList() {
            return Promise.resolve({
                rows: this.filteredList,
            });
        },
        toggleSelection(row) {
            this.$emit('selection-changed', row);
        },
        featuredChanged(featured) {
            this.$emit('featured-changed', featured);
        },
    },
}
</script>
