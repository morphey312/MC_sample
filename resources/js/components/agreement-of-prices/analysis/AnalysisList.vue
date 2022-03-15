<template>
    <section class="p-0 shrinkable-tabs">
        <div class="content-wrapper">
            <section class="grey">
                <analysis-filter
                    ref="filter"
                    :default-price-set="priceType"
                    :initial-state="filters"
                    permissions="analysis-prices"
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
                    permission="price-agreement-acts.create-analysis"
                    @header-filter-updated="syncFilters" />
            </section>
        </div>
    </section>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import AnalysisRepository from '@/repositories/analysis';
import AnalysisFilter from './Filter.vue';
import PriceGrid from '../PriceGrid.vue';
import BatchRequest from "@/services/batch-request";
import CONSTANTS from "@/constants";
import PriceSetRepository from "@/repositories/price/set";
import LaboratoryRepository from "@/repositories/analysis/laboratory";
import Analysis from '@/models/analysis';

export default {
    components: {
        AnalysisFilter,
        PriceGrid
    },
    mixins: [
        FilterMixin,
    ],
    data() {
        let today = this.$moment().format('YYYY-MM-DD');
        return {
            repository: new AnalysisRepository(),
            batchRequest: new BatchRequest('/api/v1/analyses/prices/batch'),
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
                    title: __('Название анализа'),
                    filter: true,
                    filterField: 'name',
                    get: (analysis_name) => analysis_name,
                },
                {
                    name: 'service.laboratory_name',
                    title: __('Лаборатория'),
                    width: '7%',
                    filter: new LaboratoryRepository(),
                    filterField: 'laboratory',
                    get: (laboratory_name) => laboratory_name,
                },
                {
                    name: 'service.laboratory_code',
                    title: __('Код лаб.'),
                    width: '7%',
                    filterField: 'laboratory_code',
                    filter: true,
                    get: (laboratory_code) => laboratory_code,
                },
                {
                    name: 'service.clinics',
                    title: __('Код клиники'),
                    width: '7%',
                    filterField: 'clinic_code',
                    filter: true,
                    get: (clinics) => this.$formatter.listFormat(clinics, 'code'),
                },
            ],
            priceSets: [],
            priceType: CONSTANTS.PRICE.SET_TYPE.BASE,
        };
    },
    computed: {
        getType() {
            return CONSTANTS.PRICE.SERVICE_TYPE.ANALYSIS;
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
