<template>
    <page
        :title="__('Прайс-лист на анализы. Страховые компании')"
        type="flex"
        v-if="!loading">
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
                    :selected-price-set="activeTab"
                    :initial-state="filters"
                    :price-sets="tabs"
                    permissions="insurance-prices"
                    @changed="changeFilters"
                    @cleared="clearFilters"
                    @change-clinics="changeAvailableclinics" />
            </section>
        </drawer>
        <el-tabs v-model="activeTab" class="tab-group-grey" @tab-click="switchTab" v-if="displayTable">
            <el-tab-pane
                v-for="tab in tabs"
                :key="tab.id"
                :label="tab.value"
                :name="tab.value" />
        </el-tabs>
        <section
            v-if="headerClinicList !== null && displayTable"
            class="darkgrey-cap pt-0 shrinkable price-grid">
            <price-grid
                ref="grid"
                :repository="repository"
                :filters="filters"
                :sort-order="sortOrder"
                :batch-request="batchRequest"
                :extra-columns="extraColumns"
                :price-sets="tabs"
                :available-clinics="headerClinicList"
                permissions="insurance-prices"
                @header-filter-updated="syncFilters" />
        </section>
    </page>
</template>
<script>
import AnalysisFilter from './AnalysisFilter.vue';
import PriceGrid from './AnalysisPriceGrid.vue';
import AnalysisRepository from '@/repositories/analysis';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import BatchRequest from '@/services/batch-request';
import CONSTANTS from '@/constants';
import PriceMixin from './mixins/prices';

export default {
    mixins: [
        PriceMixin,
    ],
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
            batchRequest: new BatchRequest('/api/v1/insurance/prices/analyses-batch'),
            filters: {
                set_id: null,
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
                    name: 'service.clinic_prices',
                    title: __('Цена в клиниках'),
                    width: '15%',
                    get: (clinic_prices) => {
                        return this.getClinicPriceData(clinic_prices).join('; ');
                    }
                },
            ],
        };
    },
    methods: {
        getClinicPriceData(clinic_prices) {
            if (clinic_prices.length === 0) {
                return [];
            }
            let basePrices = clinic_prices.filter(price => price.set_type === CONSTANTS.PRICE.SET_TYPE.BASE);
            return basePrices.map(price => {
                return price.clinic_names.join(', ') + ": " + price.cost;
            });
        },
    },
};
</script>
