<template>
    <page
        :title="__('Города')"
        type="flex"
    >
        <el-tabs
            v-model="activeTab"
            class="tab-group-grey insurance-service-act"
        >
            <el-tab-pane
                :lazy="true"
                :label="__('Таблица')"
                name="marketing-table"
            >
                <report-table
                    :clinics="clinics"
                    :specializations="specializations"
                    :information-sources="informationSources"
                    :elastic-search-client="elasticSearchClient"
                    :call-center-slices-index="callCenterSlicesIndex"
                    :income-payments-index="incomePaymentsIndex"
                    :media-types="mediaTypes"
                    @changed-specializations="getSpecializations"
                    @changed-information-sources="getInformationSources"
                />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>

import ReportTable from "./marketing-cities/ReportTable.vue";
import ClinicRepository from "@/repositories/clinic";
import SpecializationRepository from "@/repositories/specialization";
import InformationSources from "@/repositories/patient/information-source";
import ElasticSearchClient from "@/services/elasticsearch";
import CONSTANTS from "@/constants";
import handbook from "@/services/handbook";

export default {
    components: {
        ReportTable,
    },
    data() {
        return {
            activeTab: 'marketing-table',
            clinics: [],
            specializations: [],
            informationSources: [],
            elasticSearchClient: new ElasticSearchClient(),
            mediaTypes: handbook.getOptions('media_type')
        }
    },
    computed: {
        callCenterSlicesIndex()  {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.CALL_CENTER_SLICES);
        },
        incomePaymentsIndex() {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.INCOME_PAYMENTS);
        }
    },
    mounted() {
        this.getClinics();
        this.getSpecializations();
        this.getInformationSources();
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
        getInformationSources(filters = []) {
            let informationSources = new InformationSources();
            informationSources.setFilters(filters);
            return informationSources.fetchList().then(response => {
                this.informationSources = response;
                return Promise.resolve();
            });
        },
        updateSpecializations(filters) {
            this.specializations.setFilters(filters);
        }
    },
}

</script>
