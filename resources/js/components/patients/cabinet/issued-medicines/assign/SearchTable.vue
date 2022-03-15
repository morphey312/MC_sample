<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto"
        @header-filter-updated="syncFilters"
        @loaded="loaded">
        <template
            slot="add-selection"
            slot-scope="props" >
            <div class="pr-20">
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Выбрать медикамент') }}</a>
            </div>
        </template>
    </manage-table>
</template>
<script>
import MedicineRepository from '@/repositories/medicine';
import ProxyRepository from '@/repositories/proxy-repository';
import AssignedMedicine from '@/models/patient/assigned-medicine';

export default {
    props: {
        clinic: Array,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getRows();
            }),
            fields: [
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
            let repo = new MedicineRepository();
            return repo.fetchForAssign(this.getMedicineFilter()).then((response) => {
                return Promise.resolve({
                    rows: response.rows.filter(row => row.cost > 0),
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
        syncFilters(updates) {
            this.filter = _.onlyFilled({...this.filter, ...updates});
        },
    }
}
</script>
