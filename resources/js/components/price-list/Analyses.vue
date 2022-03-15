<template>
    <page
        :title="__('Цены на анализы')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey">
                <analysis-filter 
                    ref="filter"
                    :default-price-set="activeTab"
                    :initial-state="filters"
                    permissions="analysis-prices"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <el-tabs v-model="activeTab" class="tab-group-grey">
            <el-tab-pane 
                v-for="tab in tabs"
                :key="tab.value"
                :label="tab.value" 
                :name="tab.id" />
        </el-tabs>
        <section class="darkgrey-cap pt-0 shrinkable price-grid">
            <price-grid 
                ref="grid"
                :repository="repository"
                :filters="filters"
                :sort-order="sortOrder"
                :batch-request="batchRequest"
                :extra-columns="extraColumns"
                :price-sets="priceSets"
                permissions="analysis-prices"
                @header-filter-updated="syncFilters" />
        </section>
    </page>
</template>

<script>
import AnalysisFilter from '@/components/treatment/analyses/Filter.vue';
import PriceGrid from './AnalysisPriceGrid';
import AnalysisRepository from '@/repositories/analysis';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import BatchRequest from '@/services/batch-request';
import PriceSetRepository from '@/repositories/price/set';
import CONSTANTS from '@/constants';

export default {
    components: {
        AnalysisFilter,
        PriceGrid,
    },
    data() {
        let today = this.$moment().format('YYYY-MM-DD');
        return {
            repository: new AnalysisRepository({
                limitClinics: this.$isAccessLimited('analysis-prices'),
            }),
            batchRequest: new BatchRequest('/api/v1/analyses/prices/batch'),
            filters: {
                set_type: CONSTANTS.PRICE.SET_TYPE.BASE,
                disabled: 0,
                has_price: {
                    from: today,
                    to: today,
                },
            },
            displayFilter: true,
            sortOrder: [
                {field: 'name', 'direction': 'asc'},
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
                    width: '15%',
                    filter: new LaboratoryRepository(),
                    filterField: 'laboratory',
                    get: (laboratory_name) => laboratory_name,
                },
                {
                    name: 'service.laboratory_code',
                    title: __('Код лаб.'),
                    width: '10%',
                    filterField: 'laboratory_code',
                    filter: true,
                    get: (laboratory_code) => laboratory_code,
                },
                {
                    name: 'service.clinics',
                    title: __('Код клиники'),
                    width: '10%',
                    filterField: 'clinic_code',
                    filter: true,
                    get: (clinics) => this.$formatter.listFormat(clinics, 'code'),
                },
            ],
            activeTab: CONSTANTS.PRICE.SET_TYPE.BASE,
            tabs: this.$handbook.getOptions('price_set').filter(set => {
                return [CONSTANTS.PRICE.SET_TYPE.BASE, CONSTANTS.PRICE.SET_TYPE.CERTIFICATE]
                        .indexOf(set.id) !== -1;
            }),
            priceSets: [],
        };
    },
    mounted() {
        this.getPriceSets();
    },
    methods: {
        changeFilters(filters) {
            this.filters = {...filters, set_type: this.activeTab};
        },
        clearFilters() {
            this.filters = {set_type: this.activeTab};
        },
        syncFilters(updates) {
            this.$refs.filter.sync(updates);
        },
        getPriceSets() {
            let set = new PriceSetRepository();
            set.fetchList().then(response => {
                this.priceSets = response;
            });
        },
    },
};
</script>
