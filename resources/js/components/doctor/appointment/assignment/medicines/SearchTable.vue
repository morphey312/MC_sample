<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto"
        :empty-message="__('Введите название медикамента для поиска')"
        @header-filter-updated="syncFilters"
        @loaded="loaded">
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
                <a v-if="isDoctor"
                    href="#"
                    @click.prevent="toggleSelection(props.rowData)">
                    {{ __('Выбрать медикамент') }}
                </a>
            </div>
        </template>
    </manage-table>
</template>
<script>
import MedicineRepository from '@/repositories/medicine';
import ProxyRepository from '@/repositories/proxy-repository';
import AssignedMedicine from '@/models/patient/assigned-medicine';
import Featured from './Featured.vue';

export default {
    components: {
        Featured,
    },
    props: {
        clinic: Number,
        featuredList: Array,
        isDoctor: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getRows();
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
                    width: "105px",
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
                    name: 'add-selection',
                    title: '',
                    width: "270px",
                    dataClass: 'text-right',
                },
            ],
            filter: {
                name: null,
                clinic: this.clinic,
            },
        };
    },
    methods: {
        getRows() {
            if (_.isVoid(this.filter.name)) {
                return Promise.resolve({rows: []});
            }

            let repo = new MedicineRepository();
            return repo.fetchForAssign(this.getMedicineFilter(), [{field: 'name', direction: 'asc'},]).then((response) => {
                return Promise.resolve({
                    rows: response.rows.filter(row => row.cost > 0)
                });
            });
        },
        getMedicineFilter() {
            return _.onlyFilled(this.filter);
        },
        toggleSelection(row) {
            this.$emit('selection-changed', {row});
        },
        loaded() {
            this.$emit('loaded');
        },
        featuredChanged(item) {
            this.$emit('featured-changed', item);
        },
        syncFilters(updates) {
            this.filter = _.onlyFilled({...this.filter, ...updates});
        },
    }
}
</script>
