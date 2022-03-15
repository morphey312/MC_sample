import ManageMixin from '@/mixins/manage';
import ChartFormatMixin from '@/components/reports/interactive/mixins/chart-format';
import generator from '../generator';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ManageMixin,
        ChartFormatMixin,
    ],
    props: {
        elasticSearchClient: Object,
        activeStatuses: Array,
        externalIndex: String,
        internalIndex: String,
        clinics: Array,
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
        getData(updates, reportType = 'table') {
            this.changeFilters(updates);
            let filters = this.prepareFilters();
            return this.fetchData(filters, reportType);
        },
        fetchData(filters, reportType = 'table') {
            this.loading = true;
            let resultData = [];

            return new Promise((resolve) => {
                let getDataRows = async () => {
                    let clinicSize = filters.clinic.length;
                    for (let i = 0; i < clinicSize; i++) {
                        let internal = await this.getInternal({...filters, clinic: filters.clinic[i]});
                        let external = await this.getExternal({...filters, clinic: filters.clinic[i]});
                        let result = generator({internal, external}, filters, reportType);
                        resultData = [...resultData, ...result];
                    }
                    resolve(resultData);
                }
                getDataRows();
            });
        },
        getExternal(filters) {
            return this.elasticSearchClient.getHits(this.externalIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                sort: [{ id : "asc" }],
                query: {
                    bool: {
                        minimum_should_match: 1,
                        should: this.getShouldQuery(filters),
                        filter: [
                            ...this.getFilterQuery(filters),
                            {
                                script: {
                                    script: {
                                        source: this.getExternalScript(),
                                        lang: "painless",
                                        params: {
                                            dateStart: new Date(filters.date_start).toISOString(),
                                            dateEnd: new Date(filters.date_end).toISOString()
                                        }
                                    }
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
        getInternal(filters) {
            return this.elasticSearchClient.getHits(this.internalIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                sort: [{ id : "asc" }],
                query: {
                    bool: {
                        minimum_should_match : 1,
                        should: this.getShouldQuery(filters),
                        filter: this.getFilterQuery(filters),
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
        subPeriod(filters, field, periodNum = 30, periodName = 'days') {
            return this.$moment(filters[field]).subtract(periodNum, periodName).format('YYYY-MM-DD');
        },
        getShouldQuery(filters) {
            return [
                {
                    range: {
                        date: {
                            gte: filters.date_start,
                            lte: filters.date_end
                        }
                    }
                },
                {
                    range: {
                        date: {
                            gte: this.subPeriod(filters, 'date_start'),
                            lte: this.subPeriod(filters, 'date_end')
                        }
                    }
                }
            ];
        },
        getFilterQuery(filters) {
            return [
                {term: {is_deleted: false,}},
                {terms: {appointment_status_id: this.activeStatuses}},
                {term: {clinic_id: filters.clinic}},
            ];
        },
        getExternalScript() {
            return `
                long start = ZonedDateTime.parse(params.dateStart).toInstant().toEpochMilli();
                long end = ZonedDateTime.parse(params.dateEnd).toInstant().toEpochMilli();
                for(date in doc['payment_dates']) {
                    long curDate = date.toInstant().toEpochMilli();
                    if (curDate >= start && curDate <= end) {
                        return true;
                    }
                }
                return false;
            `;
        },
        addClinicNameToEmployee(employeeData) {
            employeeData.forEach(row => {
                let clinic = this.clinics.find(clinic => clinic.id == row.clinic_id);
                if (clinic) {
                    row['itemName'] = clinic.value;
                }
            });
            return employeeData;
        },
        groupByKey(list, key = 'itemName') {
            return _.groupBy(list, key);
        }
    }
}
