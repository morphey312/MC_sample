import ChartFormatMixin from '@/components/reports/interactive/mixins/chart-format';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import CONSTANTS from '@/constants';

const FILTER_TYPE_CLINIC = 'clinic';
const FILTER_TYPE_SPECIALIZATION = 'specialization';

export default {
    mixins: [
        ChartFormatMixin,
    ],
    props: {
        clinicList: {
            type: Array,
            default: () => [],
        },
        specializationList: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            }),
            specializations: new SpecializationRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            }),
            filterTypes: [
                {
                    id: FILTER_TYPE_CLINIC,
                    value: __('Клиникам'),
                },
                {
                    id: FILTER_TYPE_SPECIALIZATION,
                    value: __('Специализациям'),
                }
            ]
        }
    },
    computed: {
        filterByClinic() {
            return FILTER_TYPE_CLINIC;
        },
        filterBySpecialization() {
            return FILTER_TYPE_SPECIALIZATION;
        },
    },
    watch: {
        ['filters.currency']() {
            this.clinics.setFilters(this.getClinicFilters());
        },
        ['filters.clinic']() {
            this.specializations.setFilters(this.getSpecializationFilters());
        },
    },
    methods: {
        onReady(instance, ECharts) {},
        onClick(event, instance, ECharts) {
            console.log(event);
            console.log(instance);
        },
        getFilterUid() {
            return false;
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filters.clinic,
            });
        },
        getClinicFilters() {
            let currency = this.filters.currency === CONSTANTS.CURRENCY.UAH ? null : this.filters.currency;
            return _.onlyFilled({
                currency: currency,
            });
        },
        getSeriesTotal(chart, seriesIndex = 0) {
            if (chart) {
                return chart.series[seriesIndex].data.reduce((total, item) => {
                    return total + Number(item);
                }, 0);
            }
            return 0;
        },
        getDefaultFilters() {
            return {
                is_deleted: false,
                filterType: FILTER_TYPE_CLINIC,
            }
        },
        filterIsEmpty() {
            return _.isVoid(this.filters.date_start) || _.isVoid(this.filters.date_end)
        },
        prepareFilters() {
            let filters = {...this.filters};
            if (!this.filters.clinic || this.filters.clinic.length == 0) {
                filters.clinic = this.clinicList.map(c => c.id);
            }
            return filters;
        },
        getData(updates) {
            this.changeFilters(updates);
            if (this.filterIsEmpty()) {
                return this.$error(__('Выберите фильтры для поиска'));
            }
            let filters = this.prepareFilters();
            this.loading = true;
            if (this.filters.filterType === this.filterBySpecialization) {
                return this.fetchSpecializationReportData(filters);
            }
            return this.fetchClinicReportData(filters);
        },
        mapChartClinics(bucket) {
            return _.sortBy(bucket.map(row => {
                let clinic = this.clinicList.find(c => c.id == row.key);
                if (clinic) {
                    row.itemName = clinic.value;
                }
                return row;
            }), ['itemName']);
        },
        getClinics() {
            return this.clinics.fetchList();
        },
        mapChartSpecializations(bucket) {
            return _.sortBy(bucket.map(row => {
                let specialization = this.specializationList.find(c => c.id == row.key);
                if (specialization) {
                    row.itemName = specialization.short_name;
                }
                return row;
            }), ['itemName']);
        },
        getClinicPayments(filters) {
            return this.elasticSearchClient.getAggregations(this.paymentIndex, {
                size: 0,
                query: {
                    bool: {
                        filter: [
                            ...this.getBasePaymentFilter(filters),
                            ...(filters.specialization && filters.specialization.length != 0
                                ? [{
                                    terms: {
                                        appointment_card_specialization_id: filters.specialization
                                    }
                                }]
                                : []
                            ),
                        ]
                    }
                },
                aggs: {
                    aggr_group: {
                        terms: {
                            size: this.clinicList.length,
                            field: "clinic_id"
                        },
                        aggs: {
                            payed_amount: {
                                sum: {
                                    script: {
                                        lang: "painless",
                                        source: this.getPaymentAggrScript(),
                                    }
                                }
                            }
                        }
                    }
                }
            })
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        getClinicAppointments(filters) {
            return this.elasticSearchClient.getAggregations(this.appointmentIndex, {
                size: 0,
                query: {
                    bool: {
                        filter: [
                            ...this.getBaseAppointmentFilter(filters),
                            ...(filters.specialization && filters.specialization.length != 0
                                ? [{
                                    terms: {
                                        card_specialization_id: filters.specialization
                                    }
                                }]
                                : []
                            )
                        ]
                    }
                },
                aggs: {
                    aggr_group: {
                        terms: {
                            size: this.clinicList.length,
                            field: "clinic_id"
                        },
                        aggs: {
                            is_first: {
                                sum: {
                                    field: 'is_first'
                                }
                            },
                            repeated: {
                                sum: {
                                    script: {
                                        lang: "painless",
                                        source: this.getRepeatedAggrScript(),
                                    }
                                }
                            }
                        }
                    }
                }
            })
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        getSpecializationPayments(filters) {
            return this.elasticSearchClient.getAggregations(this.paymentIndex, {
                size: 0,
                query: {
                    bool: {
                        filter: [
                            ...this.getBasePaymentFilter(filters),
                            ...(filters.specialization && filters.specialization.length != 0
                                ? [{
                                    terms: {
                                        appointment_card_specialization_id: filters.specialization
                                    }
                                }]
                                : []
                            ),
                        ]
                    }
                },
                aggs: {
                    aggr_group: {
                        terms: {
                            size: this.specializationList.length,
                            field: "appointment_card_specialization_id"
                        },
                        aggs: {
                            payed_amount: {
                                sum: {
                                    script: {
                                        lang: "painless",
                                        source: this.getPaymentAggrScript(),
                                    }
                                }
                            }
                        }
                    }
                }
            })
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        getSpecializationAppointments(filters) {
            return this.elasticSearchClient.getAggregations(this.appointmentIndex, {
                size: 0,
                query: {
                    bool: {
                        filter: [
                            ...this.getBaseAppointmentFilter(filters),
                            ...(filters.specialization && filters.specialization.length != 0
                                ? [{
                                    terms: {
                                        card_specialization_id: filters.specialization
                                    }
                                }]
                                : []
                            )
                        ]
                    }
                },
                aggs: {
                    aggr_group: {
                        terms: {
                            size: this.specializationList.length,
                            field: "card_specialization_id"
                        },
                        aggs: {
                            is_first: {
                                sum: {
                                    field: 'is_first'
                                }
                            },
                            repeated: {
                                sum: {
                                    script: {
                                        lang: "painless",
                                        source: this.getRepeatedAggrScript(),
                                    }
                                }
                            }
                        }
                    }
                }
            })
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        getPaymentAggrScript() {
            let field = this.filters.currency !== CONSTANTS.CURRENCY.UAH ? 'base_amount' : 'payed_amount';
            return `
                if(doc['type.keyword'].value == 'expense') {
                    return -(doc['${field}'].value)
                }
                return doc['${field}'].value`;
        },
        getRepeatedAggrScript() {
            return `
                if(doc['is_first'].value == 0) {
                    return 1;
                }
                return 0;
            `;
        },
        getBasePaymentFilter(filters) {
            return [
                {
                    range: {
                        created_at: {
                            gte: filters.date_start,
                            lte: filters.date_end
                        }
                    },
                },
                {
                    term: {
                        is_deleted: filters.is_deleted,
                    }
                },
                ...(filters.clinic && filters.clinic.length != 0
                    ? [{
                        terms: {
                            clinic_id: filters.clinic
                        }
                    }]
                    : []
                ),
            ];
        },
        getBaseAppointmentFilter(filters) {
            return [
                {
                    range: {
                        date: {
                            gte: filters.date_start,
                            lte: filters.date_end
                        }
                    },
                },
                {
                    term: {
                        is_deleted: filters.is_deleted,
                    }
                },
                {
                    terms: {
                        appointment_status_id: this.activeStatuses
                    }
                },
                ...(filters.clinic && filters.clinic.length != 0
                    ? [{
                        terms: {
                            clinic_id: filters.clinic
                        }
                    }]
                    : []
                ),
            ];
        },
    }
}
