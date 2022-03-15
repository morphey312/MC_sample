<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        table-height="auto"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
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
                <a href="#" @click.prevent="toggleSelection(props.rowData)">{{ __('Выбрать услугу') }}</a>
            </div>
        </template>
    </manage-table>
</template>

<script>
import ServiceRepository from '@/repositories/service';
import SpecializationRepository from '@/repositories/specialization';
import Service from '@/models/appointment/service';
import ProxyRepository from '@/repositories/proxy-repository';
import Featured from './Featured.vue';

export default {
    components: {
        Featured,
    },
    props: {
        appointmentData: Object,
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
                    filterField: 'name',
                    filter: true,
                },
                {
                    name: 'specialization.name',
                    title: __('Специализация'),
                    width: "150px",
                    filterField: 'specialization',
                    filter: new SpecializationRepository({
                        filters: {id: this.appointmentData.specialization}
                    }),
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'price',
                    title: __('Стоимость, грн'),
                    width: "100px",
                },
                {
                    name: 'add-analysis',
                    title: '',
                    width: "135px",
                    dataClass: 'text-right',
                },
            ],
            filters: {
                ...this.appointmentData,
            },
        };
    },
    methods: {
        getServices() {
            if (this.filters.specialization.length == 0) {
                return Promise.resolve({rows: []});
            }

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
            service.castServiceDataToEntity(item, this.appointmentData);
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
