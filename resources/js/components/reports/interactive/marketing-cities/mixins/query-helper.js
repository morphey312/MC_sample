import ManageMixin from '@/mixins/manage';
import CONSTANTS from "@/constants";
import ChartFormatMixin from "@/components/reports/interactive/mixins/chart-format";

export default {
    mixins: [
        ManageMixin,
        ChartFormatMixin
    ],
    props: {
        clinics: Array,
        specializations: Array,
        informationSources: Array,
        elasticSearchClient: Object,
        reportIndex: String,
    },
    data() {
        return {
            tableData: [],
        }
    },
    methods: {
        prepareFilters() {
            let filters = {...this.filters};

            if (!this.filters.clinic || this.filters.clinic.length === 0) {
                filters.clinic = this.clinics.map(c => c.id);
            }

            return filters;
        },

        getCalls(elasticData) {
            return _.filter(elasticData, {'is_call': 1});
        },
        getCities(elasticData){
            let cities = []
            for (let item of elasticData){
                if (item.patient_location) {
                    cities.push(item.patient_location.toLowerCase())
                }
            }

            return cities = Array.from(new Set(cities.map(JSON.stringify))).map(JSON.parse);

        },
        getAppointments(elasticData) {
            return _.filter(elasticData, {'is_appointment': 1});
        },

        getIncomes(elasticData) {
            return _.filter(elasticData, {'is_income': 1});
        },

        getTreatments(elasticData) {
            return _.filter(elasticData, {'is_treatment': 1});
        },

        fetchCallCenterData(filters) {
            return this.elasticSearchClient.getHits(this.callCenterSlicesIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                query: {
                    bool: {
                        filter: [
                            {
                                bool: {
                                    should: [
                                        {
                                            range: {
                                                date: {
                                                    gte: filters.date_start,
                                                    lte: filters.date_end
                                                },
                                            }
                                        },
                                    ]
                                }
                            },
                            ...(_.isArray(filters.clinic) ? [{
                                terms: {
                                    clinic_id: filters.clinic
                                }
                            }] : [{
                                term: {
                                    clinic_id: filters.clinic
                                }
                            }]),
                            {
                                terms: {
                                    patient_source_id: filters.information_sources
                                }
                            },

                        ],
                    }
                }
            }, true)
                .then(response => {
                    return Promise.resolve(response);
                }).catch(e => {
                    console.error(e);
                    this.$error();
                });
        },
    }
}
