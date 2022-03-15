<template>
    <div v-loading="loading">
        <section class="grey">
            <report-filter 
                ref="filter"
                :initial-state="filters"
                @changed="getData"/>
        </section>
        <section class="p-0" v-if="!loading">
            <data-table 
               :table-data="tableData"
               :columns="columns"
               :colors="colors"
               :sundays="sundays"
               :employees="employees"
               @export="exportExcel"
            />
        </section>
    </div>
</template>
<script>
import generator from './generator'; 
import DataTable from './Table.vue';
import ReportFilter from './TableFilter.vue';
import ElasticMixin from './mixins/elastic';
import incomeGenerator from '@/components/reports/generators/finance/income';
import FileSaver from 'file-saver';
import IncomeMixin from '@/components/reports/mixins/income-report';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ElasticMixin,
        IncomeMixin,
    ],
    components: {
        DataTable,
        ReportFilter,
    },
    data() {
        return {
            loading: false,
            tableData: [],
            columns: {},
            colors: {},
            employees: {},
            sundays: [],
        }
    },
    methods: {
        getFilterUid() {
            return false;
        },
        filterIsEmpty() {
            return _.isVoid(this.filters.clinic) 
                || _.isVoid(this.filters.date_start) 
                || _.isVoid(this.filters.date_end);
        },
        getData(filters) {
            this.changeFilters(filters);
            if (this.filterIsEmpty()) {
                return this.$error(__('Выберите фильтры для поиска'));
            }
            this.loading = true;
            this.getAppointments(this.filters).then(appointments => {
                this.getPayments(this.filters).then(payments => {
                    let valueField = (this.filters.currency === CONSTANTS.CURRENCY.UAH) ? 'payed_amount' : 'base_amount';
                    let result = generator(payments, appointments, valueField);
                    this.columns = result.columns;
                    this.tableData = result.tableData;
                    this.colors = result.colors;
                    this.getDoctors(result.doctorIds).then(() => {
                        this.setSundays();
                        this.loading = false;
                    });
                }).catch(e => {
                    console.error(e);
                    this.$error();
                    this.loading = false;
                });
            }).catch(e => {
                console.error(e);
                this.$error();
                this.loading = false;
            });
        },
        getDoctors(doctors = []) {
            this.employees = {};
            let repo = new EmployeeRepository();
            doctors = _.uniq(doctors);
            return repo.fetchList({id: doctors}).then(response => {
                doctors.forEach(id => {
                    let doctor = response.find(row => row.id === id);
                    this.employees[id] = doctor ? doctor.value : null;
                });
                return Promise.resolve();
            });
        },
        getPayments(filters) {
            return this.elasticSearchClient.getHits(this.paymentIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                query: {
                    bool: {
                        filter: [
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
                                    clinic_id: filters.clinic
                                }
                            },
                            {
                                term: {
                                    is_deleted: false,
                                }
                            },
                            ...(filters.specialization && filters.specialization.length != 0
                                ? [{
                                    terms: {
                                        appointment_card_specialization_id: filters.specialization
                                    } 
                                }] : []
                            )
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
        getAppointments(filters) {
            return this.elasticSearchClient.getHits(this.appointmentIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                query: {
                    bool: {
                        filter: [
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
                                    clinic_id: filters.clinic
                                }
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
                            ...(filters.specialization && filters.specialization.length != 0
                                ? [{
                                    terms: {
                                        card_specialization_id: filters.specialization
                                    } 
                                }] : []
                            )
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
        setSundays() {
            this.sundays = [];
            let monthStart = this.$moment(this.filters.date_end).startOf('month');
            let monthEnd = this.$moment(this.filters.date_end).endOf('month');
            while(monthStart <= monthEnd) {
                if (monthStart.day() === 0) {
                    this.sundays.push(monthStart.date());
                }
                monthStart.add(1, 'days');
            }
        },
        exportExcel() {
            this.loading = true;
            let getDataRows = async () => {
                let currentData = await this.getPayments(this.filters);
                let currentAppointments = await this.getAppointments(this.filters);
                let date_start = this.subtractDate(this.filters.date_start).startOf('month').format('YYYY-MM-DD');
                let date_end = this.subtractDate(this.filters.date_start).endOf('month').format('YYYY-MM-DD');
                let prevMonthData = await this.getPayments({...this.filters, date_start, date_end});
                let prevMonthAppointments = await this.getAppointments({...this.filters, date_start, date_end});

                let data = {
                    current: {
                        data: currentData,
                        appointments: currentAppointments,
                        dateEnd: this.filters.date_end,
                    },
                    prevMonth: {
                        data: prevMonthData,
                        appointments: prevMonthAppointments,
                        dateEnd: date_end,
                    },
                    employees: this.employees,
                    valueField: (this.filters.currency === CONSTANTS.CURRENCY.UAH) ? 'payed_amount' : 'base_amount',
                }

                incomeGenerator(data).then((book) => {
                    book.xlsx.writeBuffer().then((buffer) => {
                        this.getClinicName(this.filters.clinic).then((clinicName) => {
                            FileSaver.saveAs(new Blob([buffer]), this.getFileName(clinicName))
                        });
                    }).catch((err) => {
                        console.error(err);
                        this.$error(__('Не удалось сохранить отчет'));
                    });
                });
            }
            getDataRows().then(() => {
                this.loading = false;
            });
        },
    }
}
</script>