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
            return _.filter(elasticData, call => {
                return (call.is_call === 1 && call.is_first === 1 && call.is_appointment === 1)
                    || (call.is_call === 1 && call.is_appointment !== 1)
            });
        },

        getAppointments(elasticData) {
            return _.filter(elasticData, {'is_appointment': 1, 'is_first': 1});
        },

        getIncomes(elasticData) {
            return _.filter(elasticData, {'is_income': 1, 'is_first': 1});
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
                                        {
                                            range: {
                                                date: {
                                                    gte: filters.date_start_month_ago,
                                                    lte: filters.date_end_month_ago
                                                },
                                            }
                                        },
                                        {
                                            range: {
                                                date: {
                                                    gte: filters.date_start_year_ago,
                                                    lte: filters.date_end_year_ago
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
                            ...(this.$refs.filter.filter.information_sources.length > 0 || this.$refs.filter.filter.media_types.length > 0) ? [{
                                terms: {
                                    patient_source_id: filters.information_sources
                                }
                            }] : {},
                            {
                                terms: {
                                    specialization_id: filters.specialization
                                }
                            }
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

        fetchIncomePayments(filters) {
            return this.elasticSearchClient.getHits(this.incomePaymentsIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                query: {
                    bool: {
                        filter: [
                            {
                                bool: {
                                    should: [
                                        {
                                            range: {
                                                created_at: {
                                                    gte: filters.date_start,
                                                    lte: filters.date_end
                                                },
                                            }
                                        },
                                        {
                                            range: {
                                                created_at: {
                                                    gte: filters.date_start_month_ago,
                                                    lte: filters.date_end_month_ago
                                                },
                                            }
                                        },
                                        {
                                            range: {
                                                created_at: {
                                                    gte: filters.date_start_year_ago,
                                                    lte: filters.date_end_year_ago
                                                },
                                            }
                                        },
                                    ]
                                }
                            },
                            {
                                term: {
                                    is_deleted: false,
                                }
                            },
                            {
                                term: {
                                    clinic_id: filters.clinic
                                }
                            },
                            ...(this.$refs.filter.filter.information_sources.length > 0 || this.$refs.filter.filter.media_types.length > 0) ? [{
                                terms: {
                                    patient_source_id: filters.information_sources
                                }
                            }] : {},
                            ...(this.$refs.filter.filter.specialization.length > 0 ) ? [{
                                terms: {
                                    appointment_card_specialization_id: filters.specialization
                                }
                            }] : {}
                        ]
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
