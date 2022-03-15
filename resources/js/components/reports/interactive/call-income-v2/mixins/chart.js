import ManageMixin from '@/mixins/manage';
import ChartFormatMixin from '@/components/reports/interactive/mixins/chart-format';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ManageMixin,
        ChartFormatMixin,
    ],
    props: {
        elasticSearchClient: Object,
        reportIndex: String,
        clinics: Array,
        positions: Array,
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
            let filters = {...this.filters};

            if (!this.filters.clinic || this.filters.clinic.length == 0) {
                filters.clinic = this.clinics.map(c => c.id);
            }
            return filters;
        },
        fetchData(filters) {
            this.loading = true;
            return this.elasticSearchClient.getHits(this.reportIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                query: {
                    bool: {
                        filter: [
                            ...(filters.time_start && filters.time_end ? [
                                {
                                    range: {
                                        full_date: {
                                            gte: this.$moment(filters.date_start + ' ' + filters.time_start).unix(),
                                            lte: this.$moment(filters.date_end + ' ' + filters.time_end).unix(),
                                        }
                                    }
                                }
                            ] : [
                                {
                                    range: {
                                        date: {
                                            gte: filters.date_start,
                                            lte: filters.date_end
                                        }
                                    }
                                },
                            ]),
                            ...(_.isArray(filters.clinic) ? [{
                                terms: {
                                    clinic_id: filters.clinic
                                }
                            }] : [{
                                term: {
                                    clinic_id: filters.clinic
                                }
                            }]),
                            ...(filters.position && filters.position.length != 0 ? [
                                {
                                    terms: {
                                        position_id: filters.position
                                    }
                                }
                            ] : [])
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
        }
    },
}
