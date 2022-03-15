<template>
    <page
        :title="__('Цены на услуги')"
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
                <service-filter
                    ref="filter"
                    :default-price-set="activeTab"
                    :initial-state="filters"
                    permissions="service-prices"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <el-tabs v-model="activeTab" class="tab-group-grey" v-if="displayTable">
            <el-tab-pane
                v-for="tab in tabs"
                :key="tab.id"
                :label="tab.value"
                :name="tab.id" />
        </el-tabs>
        <section class="darkgrey-cap pt-0 shrinkable price-grid" v-if="displayTable">
            <price-grid
                ref="grid"
                :repository="repository"
                :filters="filters"
                :sort-order="sortOrder"
                :batch-request="batchRequest"
                :extra-columns="extraColumns"
                :tabs="tabs"
                :price-sets="priceSets"
                permissions="service-prices"
                @header-filter-updated="syncFilters" />
        </section>
    </page>
</template>

<script>
import ServiceFilter from '@/components/treatment/services/Filter.vue';
import PriceGrid from './PriceGrid.vue';
import ServiceRepository from '@/repositories/service';
import SpecializationRepository from '@/repositories/specialization';
import BatchRequest from '@/services/batch-request';
import PriceSetRepository from '@/repositories/price/set';
import CONSTANTS from '@/constants';

export default {
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
            batchRequest: new BatchRequest('/api/v1/services/prices/batch'),
            filters: {
                set_type: CONSTANTS.PRICE.SET_TYPE.BASE,
                disabled: 0,
                has_price: {
                    from: today,
                    to: today,
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
                    name: 'service.name_i18n',
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
            this.displayTable = true;
        },
        clearFilters() {
            this.filters = {set_type: this.activeTab};
            this.displayTable = false;
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
    }
};
</script>
