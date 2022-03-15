<template>
    <page
        v-if="service !== null"
        :title="__('Цены на услугу: {name}', {name: service.name})"
        :back-route="{name: 'services'}"
        type="flex">
        <el-tabs 
            v-model="activeTab"
            class="tab-group-beige tab-no-border"
            @tab-click="changeTab">
            <el-tab-pane 
                v-for="tab in tabs"
                :key="tab.id"
                :label="tab.value" 
                :name="tab.id" />
        </el-tabs>
        <section class="grey-cap shrinkable pt-0">
            <price-list 
                :subject="service"
                :base-filters="filters"
                premissions="service-prices" />
        </section>
    </page>
</template>

<script>
import PriceList from '../prices/Prices.vue';
import Service from '@/models/service';
import CONSTANTS from '@/constants';

export default {
    components: {
        PriceList,
    },
    data() {
        return {
            service: null,
            filters: {},
            activeTab: CONSTANTS.PRICE.SET_TYPE.BASE,
            tabs: this.$handbook.getOptions('price_set').filter(set => {
                return [CONSTANTS.PRICE.SET_TYPE.BASE, CONSTANTS.PRICE.SET_TYPE.CERTIFICATE]
                        .indexOf(set.id) !== -1;
            }),
        };
    },
    mounted() {
        let service = new Service({id: this.$route.params.id});
        service.fetch().then(() => {
            this.filters = {service: service.id, set_type: this.activeTab};
            this.service = service;
        })
    },
    methods: {
        changeTab() {
            this.$nextTick(() => {
                this.filters = {service: this.service.id, set_type: this.activeTab};
            });
        },
    },
};
</script>