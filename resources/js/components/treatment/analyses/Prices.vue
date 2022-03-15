<template>
    <page
        v-if="analysis !== null"
        :title="__('Цены на анализ: {name}', {name: analysis.name})"
        :back-route="{name: 'analyses'}"
        type="flex">
        <el-tabs 
            v-model="activeTab"
            @tab-click="changeTab">
            <el-tab-pane 
                v-for="tab in tabs"
                :key="tab.id"
                :label="tab.value" 
                :name="tab.id" />
        </el-tabs>
        <section class="shrinkable">
            <price-list 
                :subject="analysis"
                :base-filters="filters"
                premissions="analysis-prices" />
        </section>
    </page>
</template>

<script>
import PriceList from '../prices/Prices.vue';
import Analysis from '@/models/analysis';
import CONSTANTS from '@/constants';

export default {
    components: {
        PriceList,
    },
    data() {
        return {
            analysis: null,
            filters: {},
            activeTab: CONSTANTS.PRICE.SET_TYPE.BASE,
            tabs: this.$handbook.getOptions('price_set').filter(set => {
                return [CONSTANTS.PRICE.SET_TYPE.BASE, CONSTANTS.PRICE.SET_TYPE.CERTIFICATE]
                        .indexOf(set.id) !== -1;
            }),
        };
    },
    mounted() {
        let analysis = new Analysis({id: this.$route.params.id});
        analysis.fetch().then(() => {
            this.filters = {analysis: analysis.id, set_type: this.activeTab};
            this.analysis = analysis;
        })
    },
    methods: {
        changeTab() {
            this.$nextTick(() => {
                this.filters = {analysis: this.analysis.id, set_type: this.activeTab};
            });
        },
    },
};
</script>