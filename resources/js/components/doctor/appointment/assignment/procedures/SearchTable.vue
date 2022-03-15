<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        table-height="auto"
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
            <div class="has-icon">
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Выбрать услугу') }}</a>
            </div>
        </template>
    </manage-table>
</template>

<script>
import ServiceRepository from '@/repositories/service';
import Service from '@/models/appointment/service';
import ProxyRepository from '@/repositories/proxy-repository';
import Featured from './Featured.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        Featured,
    },
    props: {
        filters: Object,
        featuredList: Array,
        insurancePolicy: Object,
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
                    name: 'add-selection',
                    title: '',
                    width: "135px",
                    dataClass: 'text-right',
                },
            ],
        };
    },
    methods: {
        getServices() {
            let repo = new ServiceRepository();
            let params = {};
            if (this.insurancePolicy) {
                params.withInsurer = this.insurancePolicy.insurance_company_id;
            }
            return repo.fetchListForAppointment(this.filters, params).then((response) => {
                return Promise.resolve({
                    rows: response.map(row => this.createServiceModel(row))
                });
            });
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        createServiceModel(item) {
            let service = new Service();
            service.castServiceDataToEntity(item, this.filters);
            return service;
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
    }
}
</script>