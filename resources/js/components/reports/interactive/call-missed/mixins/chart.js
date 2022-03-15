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
        queue: Array,
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
            return {...this.filters};
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

                query = {
                    bool: {
                        filter: [
                            {
                                range: {
                                    started_at: {
                                        gte: filters.date_start,
                                        lte: filters.date_end
                                    }
                                },
                            },
                            ...(filters.clinic && filters.clinic.length > 0 ? [
                                {
                                    terms: {
                                        clinic_id: filters.clinic
                                    }
                                }
                            ] : []),
                            ...(filters.queue && filters.queue.length > 0 ? [
                                {
                                    terms: {
                                        queue: filters.queue
                                    }
                                }
                            ] : []),
                            {
                                term: {
                                    type: "incoming"
                                }
                            }
                        ]
                    }
                }
            console.log('query', query);
            return query;
        }
    },
}
