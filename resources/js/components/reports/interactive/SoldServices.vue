<template>
    <page
        :title="__('Проданные услуги V2')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey insurance-service-act">
            <el-tab-pane
                :lazy="true"
                :label="__('Услуги')"
                name="service-table" >
                <service-table
                    :elastic-search-client="elasticSearchClient"
                    :report-index="serviceIndex"
                    :clinics="clinics"
                    :specializations="specializations" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Анализы')"
                name="analyse-table" >
                <analyse-table
                    :elastic-search-client="elasticSearchClient"
                    :report-index="serviceIndex"
                    :clinics="clinics"
                    :laboratories="laboratories" />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import ElasticSearchClient from '@/services/elasticsearch';
import CONSTANTS from '@/constants';
import ClinicRepository from "@/repositories/clinic";
import ServiceTable from "./sold-services/ServicesTable";
import AnalyseTable from "./sold-services/AnalysesTable";
import LaboratoryRepository from "@/repositories/analysis/laboratory";
import SpecializationRepository from "@/repositories/specialization";

export default {
    components: {
        ServiceTable,
        AnalyseTable
    },
    data() {
        return {
            elasticSearchClient: new ElasticSearchClient(),
            activeTab: 'service-table',
            clinics: [],
            specializations: [],
            laboratories: [],
        }
    },
    computed: {
        serviceIndex()  {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.ENCOUNTERS);
        },
    },
    mounted() {
        this.getClinics();
        this.getSpecializations();
        this.getLaboratories();
    },
    methods: {
        getClinics() {
            let clinic = new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            return clinic.fetchList().then(response => {
                this.clinics = response;
                return Promise.resolve();
            });
        },
        getSpecializations() {
            let specialization = new SpecializationRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            return specialization.fetchList().then(response => {
                this.specializations = response;
                return Promise.resolve();
            });
        },
        getLaboratories() {
            let laboratories = new LaboratoryRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            });
            return laboratories.fetchList().then(response => {
                this.laboratories = response;
                return Promise.resolve();
            });
        }
    },
}
</script>

<style scoped>

</style>
