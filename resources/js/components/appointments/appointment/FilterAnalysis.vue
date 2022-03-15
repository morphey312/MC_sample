<template>
    <section class="grey">
        <form-row name="analysis">
            <transfer-table
                ref="transfer"
                key="analysis"
                :items="analysis_list"
                v-model="filter.analysisResults"
                :left-title="__('Анализы')"
                left-width="475px"
                :right-title="__('Выбранные анализы')"
                right-width="475px"
                height="240px"
                :emptySelectionMessage="placeholder">
            </transfer-table>
        </form-row>
    </section>
</template>
<script>
import AnalysisRepository from '@/repositories/analysis';

export default {
    props: {
        filter: Object,
    },
    data() {
        return {
            analysis_list: [],
            placeholder: this.getPlaceholder(),
        }
    },
    mounted() {
        this.$watch('$refs.transfer.filters', (val) => {
                this.getAnalyses();
            },
            { deep: true }
        );
    },
    watch: {
        ['filter.clinic'](val) {
            this.getAnalyses();
        },
    },
    methods: {
        getAnalyses() {
            let transferFilter = this.getTransferFilterValues();
            if (_.isEmpty(transferFilter)) {
                this.analysis_list = [];
                return;    
            }
            let analysis = new AnalysisRepository();
            analysis.fetchList(this.getAnalysisFilters()).then((response) => {
                this.analysis_list = response;
            });
        },
        getTransfer() {
            return this.$refs.transfer;
        },
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Добавить анализы слева'));
        },
        getAnalysisFilters() {
            return _.onlyFilled({
                clinic: this.filter.clinic,
                disabled: false
            });
        },
        getTransferFilterValues() {
            let filter = this.getTransfer().filters;
            return _.onlyFilled({
                name: filter.value,
            });
        },
    }
}
</script>
