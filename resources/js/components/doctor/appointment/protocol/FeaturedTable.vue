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
            slot="add"
            slot-scope="props" >
            <div class="has-icon">
                <a href="#" @click.prevent="add(props.rowData)">{{ __('Выбрать услугу') }}</a>
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
         appointment: Object,
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
                    name: 'service.name',
                    sortField: 'name',
                    filterField: 'name',
                    title: __('Название услуги'),
                    filter: true,
                },
                {
                    name: 'service.protocol_templates',
                    filterField: 'protocol_template',
                    title: __('Название протокола'),
                    width: '200px',
                    filter: true,
                    formatter: (value) => {
                        return this.$formatter.listFormat(value, 'name');
                    },
                },
                {
                    name: 'cost',
                    title: __('Стоимость, грн.'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    width: '90px',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'discount',
                    title: __('Скидка, %'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    width: '90px',
                },
                {
                    name: 'costWithDiscount',
                    title: __('С учетом скидки, грн.'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    width: '90px',
                    formatter: (value) => {
                        return this.$formatter.numberFormat(value);
                    },
                },
                {
                    name: 'add',
                    title: ' ',
                    width: '110px',
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
        add(row) {
            this.$confirm(__('Добавить «{name}» в запись на прием?', {name: row.service.name}), () => {
                this.appointment.addService({
                    service_id: row.service.id,
                    cost: row.costWithDiscount,
                    quantity: 1,
                    discount: row.discount,
                    price_id: row.priceId, 
                }).then(() => {
                    this.$info(__('Услуга успешно добавлена к записи на прием'));
                    this.$emit('added');
                });
            });
        },
        featuredChanged(result) {
            this.$emit('featured-changed', result);
        },
    },
}   
</script>