<template>
    <page
        :title="__('Отчет по пропущенным звонкам')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey insurance-service-act">
            <el-tab-pane
                :lazy="true"
                :label="__('Звонки')"
                name="missed-calls-table" >
                <calls-missed-tab
                    :elastic-search-client="elasticSearchClient"
                    :report-index="callsIndex"
                    :clinics="clinics"
                    :queue="queue" />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import ElasticSearchClient from '@/services/elasticsearch';
import CONSTANTS from '@/constants';
import ClinicRepository from "@/repositories/clinic";
import CallsMissedTab from "./call-missed/CallsMissedTab";

export default {
    components: {
        CallsMissedTab,
    },
    data() {
        return {
            elasticSearchClient: new ElasticSearchClient(),
            activeTab: 'missed-calls-table',
            clinics: [],
            queue: [],
        }
    },
    computed: {
        callsIndex()  {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.CALL_CENTER_LOGS);
        },
    },
    mounted() {
        this.getClinics();
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
    },
}
</script>

<style scoped>

</style>
