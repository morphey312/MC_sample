<template>
    <page
        :title="__('Прайс-лист на услуги. Страховые компании')"
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
                <service-filter
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
import ServiceFilter from './ServiceFilter.vue';
import PriceGrid from './PriceGrid.vue';
import SpecializationRepository from '@/repositories/specialization';
import ServiceRepository from '@/repositories/service';
import BatchRequest from '@/services/batch-request';
import PriceMixin from './mixins/prices';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        PriceMixin,
    ],
    components: {
        ServiceFilter,
        PriceGrid,
    },
    data() {
        let today = this.$moment().format('YYYY-MM-DD');
        return {
            repository: new ServiceRepository({
                limitClinics: this.$isAccessLimited('service-prices'),
            }),
            batchRequest: new BatchRequest('/api/v1/insurance/prices/services-batch'),
            filters: {
                set_id: null,
                disabled: 0,
                has_price: {
                    from: today,
                    to: today,
                    set_id: null,
                },
            },
            displayFilter: true,
            sortOrder: [
                {field: 'name', 'direction': 'asc'},
                {field: 'specialization', 'direction': 'asc'},
            ],
            extraColumns: [
                {
                    name: 'service.name',
                    title: __('Название услуги'),
                    filter: true,
                    filterField: 'name',
                    get: (name) => name,
                },
                {
                    name: 'service.specialization_name',
                    title: __('Специализация'),
                    width: '15%',
                    filter: new SpecializationRepository(),
                    filterField: 'specialization',
                    get: (specialization_name) => specialization_name,
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
    }
}
</script>
