<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
        <el-tabs v-model="currentTab" class="tab-group-grey">
            <el-tab-pane
                name="common"
                :label="__('Общее')">
                <filter-main :filter="filter"/>
            </el-tab-pane>
            <el-tab-pane
                name="service"
                :label="__('Услуги')">
                <filter-service :filter="filter" />
            </el-tab-pane>
            <el-tab-pane
                name="analysis"
                :label="__('Анализы')">
                <filter-analysis :filter="filter" />
            </el-tab-pane>
        </el-tabs>
    </search-filter>
</template>

<script>
import FilterMain from './FilterMain.vue';
import FilterService from './FilterService.vue';
import FilterAnalysis from './FilterAnalysis.vue';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    components: {
        FilterMain,
        FilterService,
        FilterAnalysis,
    },
    data() {
        return {
            currentTab: 'common',
            specializations: [],
        }
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                operator: [],
                status: [],
                cardExistence: null,
                isDeleted: null,
                doctor: [],
                workspace: [],
                specialization: [],
                isFirst: null,
                // analysisStatuses: [],
                source: null,
                deleteReason: null,
                analysisResults: [],
                services: [],
                ...fromState,
            };
        },
    },
};
</script>
