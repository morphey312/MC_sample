import * as TimeMapper from '@/services/report/time-mapper';
import CONSTANTS from '@/constants';
import DaySheetRepository from '@/repositories/day-sheet';

export default {
    props: {
        elasticSearchClient: Object,
        clinics: {
            type: Array,
            default: () => [],
        },
        activeStatuses: {
            type: Array,
            default: () => [],
        },
        appointmentIndex: String,
        paymentIndex: String,
    },
    data() {
        return {
            loading: false,
        }
    },
    methods: {
        prepareFilters() {
            let filters = {...this.filters};
            if (!this.filters.clinic || this.filters.clinic.length == 0) {
                filters.clinic = this.clinics.map(c => c.id);
            }
            return filters;
        },
        getClinicName(clinicId) {
            let clinic = this.clinics.find(item => item.id == clinicId);
            return clinic ? clinic.value : '';
        },
        getDaySheetFilters(filters) {
            let dateStart = TimeMapper.getStartOf(this.$moment(filters.date_start)).format('YYYY-MM-DD');
            let dateEnd = TimeMapper.getEndOf(this.$moment(filters.date_end)).format('YYYY-MM-DD');

            return {
                clinic: filters.clinic,
                date_start: dateStart,
                date_end: dateEnd,
                owner_type: CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE
            };
        },
        getDaySheets(filters) {
            let daySheet = new DaySheetRepository();
            return daySheet.fetchReportList(filters, [{field: 'date', direction: 'asc'}]);
        },
        countDaySheets(filters) {
            let daySheet = new DaySheetRepository();
            return daySheet.fetchCount(filters);
        },
        getPayments(filters) {
            return this.elasticSearchClient.getHits(this.paymentIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                sort: [{ id : "asc" }],
                query: {
                    bool: {
                        filter: [
                            ...this.getPaymentsRequestFilter(filters),
                            {
                                term: {
                                    clinic_id: filters.clinic
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
        getAppointments(filters) {
            return this.elasticSearchClient.getHits(this.appointmentIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                sort: [{ id : "asc" }],
                query: {
                    bool: {
                        filter: [
                            ...this.getAppointmentsRequestFilter(filters),
                            {
                                term: {
                                    clinic_id: filters.clinic
                                }
                            },
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
        getPaymentsRequestFilter(filters) {
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
                        is_deleted: false,
                    }
                },
            ];
        },
        getAppointmentsRequestFilter(filters) {
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
                        is_deleted: false,
                    }
                },
                {
                    terms: {
                        appointment_status_id: this.activeStatuses
                    }
                },
            ]
        },
    },
}