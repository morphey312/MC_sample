import ManageMixin from '@/mixins/manage';
import ChartFormatMixin from '@/components/reports/interactive/mixins/chart-format';

export default {
    mixins: [
        ManageMixin,
        ChartFormatMixin,
    ],
    props: {
        elasticSearchClient: Object,
        reportIndex: String,
        clinics: Array,
        specializations: Array,
        laboratories: Array
    },
    data() {
        return {
            tableData: [],
        }
    },
    methods: {
        getFilterUid() {
            return false;
        },
        prepareFilters() {
            let filters = {...this.filters };

            if (!this.filters.clinic || this.filters.clinic.length == 0) {
                filters.clinic = this.clinics.map(c => c.id);
            }
            return filters;
        },
        fetchData(filters) {
            this.loading = true;
            let query = this.getQuery(filters);

            return this.elasticSearchClient.getHits(this.reportIndex, {
                    query: query
                })
                .then(response => {
                    return Promise.resolve(response);
                }).catch(e => {
                    console.error(e);
                    this.$error();
                });
        },
        getQuery(filters) {
            let query = {}

            if (filters.laboratory && filters.laboratory.length != 0) {
                query = {
                    bool: {
                        minimum_should_match: 1,
                        should: [{
                            nested: {
                                path: "services",
                                query: {
                                    bool: {
                                        minimum_should_match: 1,
                                        should: [{
                                            nested: {
                                                path: "services.analysis",
                                                query: {
                                                    bool: {
                                                        must: [{
                                                            terms: {
                                                                'services.analysis.laboratory.laboratory_id': filters.laboratory
                                                            }
                                                        }]
                                                    }
                                                }
                                            }
                                        }]
                                    }
                                }
                            }
                        }],
                        filter: [{
                                range: {
                                    date: {
                                        gte: filters.date_start,
                                        lte: filters.date_end
                                    }
                                },
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
                            ...(filters.specialization && filters.specialization.length != 0 ? [{
                                terms: {
                                    'specialization.id': filters.specialization
                                }
                            }] : []),
                            {
                                term: {
                                    'is_deleted': false
                                }
                            }
                        ]
                    }
                }
            } else {
                query = {
                    bool: {
                        filter: [{
                                range: {
                                    date: {
                                        gte: filters.date_start,
                                        lte: filters.date_end
                                    }
                                },
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
                            ...(filters.specialization && filters.specialization.length != 0 ? [{
                                terms: {
                                    'specialization.id': filters.specialization
                                }
                            }] : []),
                            {
                                term: {
                                    'is_deleted': false
                                }
                            }
                        ]
                    }
                }
            }

            return query
        }
    },
}