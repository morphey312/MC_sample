<template>
    <section class="p-0 shrinkable-tabs">
        <div class="content-wrapper">
            <section class="grey">
                <service-filter
                    parent-component="price-agreement-act"
                    ref="filter"
                    :default-price-set="priceType"
                    :initial-state="filters"
                    permissions="service-prices"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
            <section class="grey-cap p-20 shrinkable">
                <price-grid
                    v-if="displayTable"
                    ref="grid"
                    class="price-grid"
                    :repository="repository"
                    :filters="filters"
                    :sort-order="sortOrder"
                    :service-type="getType"
                    :batch-request="batchRequest"
                    :extra-columns="extraColumns"
                    :price-sets="priceSets"
                    permission="price-agreement-acts.create-service"
                    permission-prices="service-prices"
                    @header-filter-updated="syncFilters" />
            </section>
        </div>
    </section>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ServiceRepository from '@/repositories/service';
import PriceGrid from '../PriceGrid.vue';
import SpecializationRepository from "@/repositories/specialization";
import BatchRequest from "@/services/batch-request";
import CONSTANTS from "@/constants";
import ServiceFilter from './Filter.vue';
import PriceSetRepository from "@/repositories/price/set";
import Service from '@/models/service';

export default {
    components: {
        PriceGrid,
        ServiceFilter
    },
    mixins: [
        FilterMixin,
    ],
    data() {
        let today = this.$moment().format('YYYY-MM-DD');
        return {
            repository: new ServiceRepository(),
            batchRequest: new BatchRequest('/api/v1/services/prices/batch'),
            filters: {
                set_type: CONSTANTS.PRICE.SET_TYPE.BASE,
                disabled: 0,
                has_price: {
                    from: today,
                    to: today,
                    set: CONSTANTS.PRICE.SET_TYPE.BASE,
                },
            },
            displayFilter: true,
            displayTable: !!this.$isAccessLimited('service-prices'),
            sortOrder: [
                {field: 'name', 'direction': 'asc'},
                {field: 'specialization', 'direction': 'asc'},
            ],
            extraColumns: [
                {
                    name: 'service.name',
                    title: __('Название услуги'),
                    filter: true,
                    sortField: 'name',
                    filterField: 'name_i18n',
                    get: (name) => name,
                },
                {
                    name: 'service.specialization_name',
                    title: __('Специализация'),
                    width: '15%',
                    sortField: 'specialization',
                    filter: new SpecializationRepository(),
                    filterField: 'specialization',
                    get: (specialization_name) => specialization_name,
                }
            ],
            priceSets: [],
            priceType: CONSTANTS.PRICE.SET_TYPE.BASE,
        };
    },
    computed: {
        getType() {
            return CONSTANTS.PRICE_AGREEMENT_ACT.TYPE.SERVICES; 
        },
    },
    methods: {
        changeFilters(filters) {
            this.filters = {...filters, set_type: this.priceType};
            this.displayTable = true;
        },
        clearFilters() {
            this.filters = {set_type: this.priceType};
            this.displayTable = false;
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    }
}
</script>
