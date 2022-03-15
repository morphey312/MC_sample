<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-date
                        :entity="filters"
                        property="date_start"
                        :label="__('Дата от')" />
                </el-col>
                <el-col :span="6">
                    <form-date
                        :entity="filters"
                        property="date_end"
                        :label="__('Дата до')" />
                </el-col>
            </el-row>
            <div class="buttons">
                <el-button 
                    @click="getReportData()"
                    type="primary">
                    {{ __('Показать') }}
                </el-button>
            </div>
        </section>
        <section class="p-0" v-if="!loading && tableData.length != 0">
            <data-table 
                v-for="(rows, index) in tableData"
                :key="index"
                :table-data="tableData[index]"
                :clinics-to-display="clinicsToDisplay"
                :month="getMonth(index)"
            />
        </section>
    </div>
</template>
<script>
import DataTable from './TotalReportTable.vue';
import * as IncomeBuilder from '@/services/report/income-builder';
import * as TimeMapper from '@/services/report/time-mapper';
import ElasticReportMixin from '@/components/reports/interactive/doctor-income-plan/mixins/elastic';
import totalsTableGenerator from './totals-table-generator';

export default {
    mixins: [
        ElasticReportMixin,
    ],
    components: {
        DataTable,
    },
    data() {
        return {
            filters: {
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
            },
            tableData: [],
            daySheets: {},
            clinicsToDisplay: [],
            months: {},
        }
    },
    methods: {
        getMonth(index) {
            let monthKeys = Object.keys(this.months);
            return this.months[monthKeys[index]];
        },
        getReportData() {
            let filters = this.prepareFilters();
            this.loading = true;
            this.months = TimeMapper.getMonthData(filters);
            let monthNames = Object.keys(this.months);
            this.tableData = [];

            this.countDaySheets({
                date_start: this.filters.date_start,
                date_end: this.filters.date_end,
            }).then(daySheets => {
                this.daySheets = _.groupBy(
                    daySheets.map(item => {
                        item.month_name = item.month_name.toLowerCase();
                        return item;
                    }),
                    'month_name'
                );
                
                let getData = async () => {
                    for (let i = 0; i < monthNames.length; i++) {
                        let month = monthNames[i];
                        let results = await this.fetchData(this.getRequestFilters(this.months[month], filters));
                        let data = this.mapResults(results, this.daySheets[month]);
                        this.tableData.push(data);
                    }
                }
                getData().then(() => {
                    this.loading = false;
                });
            });
        },
        getRequestFilters(monthData, filters) {
            let dateStart = (monthData.month_start < filters.date_start) ? filters.date_start : monthData.month_start;
            let dateEnd = (monthData.month_end > filters.date_end) ? filters.date_end : monthData.month_end;
            return {
                ...filters,
                date_start: dateStart,
                date_end: dateEnd,
            };
        },
        fetchData(filters) {
            let resultData = {};
            this.clinicsToDisplay = [];
            return new Promise((resolve) => {
                let getDataRows = async () => {
                    let clinics = filters.clinic;
                    let clinicsCount = clinics.length;

                    for(let i = 0; i < clinicsCount; i++) {
                        let filter = {...filters, clinic: clinics[i] };
                        let payments = await this.getPayments({...filter, clinic: clinics[i] });
                        let appointments = await this.getAppointments({...filter, clinic: clinics[i] });
                        let results = IncomeBuilder.prepareData(payments, false);
                        if (payments.length == 0 && appointments.length == 0) {
                            continue;
                        }
                        let clinicName = this.getClinicName(clinics[i]);

                        if (this.clinicsToDisplay.indexOf(clinicName) == -1) {
                            this.clinicsToDisplay.push(clinicName);
                        }
                        resultData[clinicName] = {
                            clinic_id: clinics[i],
                            payments: IncomeBuilder.addMissingAppointmentColumns(results, appointments, false),
                            appointments,
                        };
                    }
                    resolve(resultData);
                }
                getDataRows();
            });
        },
        getDaySheets(filters) {
            let daySheet = new DaySheetRepository();
            return daySheet.fetchCount(filters);
        },
        mapResults(results, daySheets) {
            return totalsTableGenerator(results, daySheets);
        },
    },
}
</script>