<template>
    <page
        :title="__('Отчет - Оборот по специализации')"
        type="flex"
    >
        <el-tabs
            v-model="activeTab"
            class="tab-group-grey insurance-service-act"
        >
            <el-tab-pane
                :lazy="true"
                :label="__('Таблица')"
                name="specialization-table"
            >
                <report-table
                    :specializations="specializations"
                    :elastic-search-client="elasticSearchClient"
                />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import ReportTable from "./doctor-specialization/ReportTable";
import ElasticSearchClient from "@/services/elasticsearch";
import SpecializationRepository from "@/repositories/specialization";
import CONSTANTS from "@/constants";


export default {
    name: "DoctorSpecializationReport",
    components: {
        ReportTable,
    },
    data() {
        return {
            activeTab: 'specialization-table',
            specializations: [],
            informationSources: [],
            elasticSearchClient: new ElasticSearchClient(),
        }
    },
    computed: {
        incomePaymentsIndex()  {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.INCOME_PAYMENTS);
        },
    },
    created() {
        this.getSpecializations();
    },
    methods: {
        getSpecializations(filters = []) {
            let specialization = new SpecializationRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            })
            specialization.setFilters(filters);
            return specialization.fetchList().then(response => {
                this.specializations = response;
                return Promise.resolve();
            });
        },
    },
}
</script>

<style scoped>

</style>
