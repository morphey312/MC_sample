<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="clinics"
                        :multiple="true"
                        :clearable="true"
                        :filterable="true"
                        property="clinic"
                        :label="__('Клиника')" />
                </el-col>
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
            <data-table :table-data="tableData" >
                <div class="p-10" slot="buttons">
                    <el-button
                        :disabled="tableData.length === 0"
                        @click="exportExcel">
                        {{ __('Экспорт в Excel') }}
                    </el-button>
                </div>
            </data-table>
        </section>
    </div>
</template>
<script>
import DaySheetRepository from '@/repositories/day-sheet';
import DoctorIncomePlanRepository from '@/repositories/employee/doctor-income-plan';
import CONSTANTS from '@/constants';
import doctorTableGenerator from './doctor-table-generator';
import DataTable from './RatingsTable.vue';
import * as IncomeBuilder from '@/services/report/income-builder';
import * as TimeMapper from '@/services/report/time-mapper';
import ElasticReportMixin from '@/components/reports/interactive/doctor-income-plan/mixins/elastic';
import * as fileGenerator from './generator';
import FileSaver from 'file-saver';

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
                clinic: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
            },
            sessions: [],
            doctorPlans: [],
            tableData: [],
        }
    },
    methods: {
        filterIsEmpty() {
            return _.isVoid(this.filters.clinic);
        },
        mapResults(results, filters, daySheetFilter) {
            let tempData = [];
            let months = TimeMapper.getMonthData(filters);

            Object.keys(results).sort().forEach(clinic => {
                let clinicData = results[clinic].payments;
                let appointments = results[clinic].appointments;
                Object.keys(clinicData).sort().forEach(spec => {
                    if (spec == __('Аванс')) {
                        return;
                    }
                    let doctors = clinicData[spec].doctors;
                    Object.keys(doctors).forEach(docId => {
                        if (docId == __('Кабинет')) {
                            return;
                        }
                        let row = {
                            clinic_name: clinic,
                            specialization: spec,
                            employee: doctors[docId][0].doctor_name,
                        };

                        let planned = this.calcPlanPayed(results[clinic].daySheets, docId, months, filters);
                        let fact = IncomeBuilder.calcPayed(doctors[docId]);
                        let periodAppointments = IncomeBuilder.filterAppointments(appointments, docId, spec);
                        let isFirstCount = IncomeBuilder.getPatientVisitType(periodAppointments);
                        let repeatedCount = IncomeBuilder.getPatientVisitType(periodAppointments, 0);
                        let treatmentsCount = IncomeBuilder.getTreatmentTotal(periodAppointments);;
                        let totalPatients = isFirstCount + repeatedCount;

                        row['plan'] = this.$formatter.numberFormat(planned);
                        row['fact'] = this.$formatter.numberFormat(fact);
                        row['progression'] = this.$formatter.numberFormat(fact - planned);
                        row['is_first'] = isFirstCount;
                        row['repeated'] = repeatedCount;
                        row['treatments'] = treatmentsCount;
                        row['treatment_percent'] = this.$formatter.numberFormat((isFirstCount > 0) ? (treatmentsCount / isFirstCount * 100) : 0);
                        row['avg_check'] = this.$formatter.numberFormat((totalPatients > 0) ? (fact / totalPatients) : 0);

                        tempData.push(row);
                    });
                });
            });
            this.tableData = tempData;
        },
        calcPlanPayed(daySheets, docId, months, filters) {
            let totalPlanned = 0;
            let doctorPlan = this.doctorPlans.find(p => p.employee_id == docId);

            Object.keys(months).forEach(month => {
                let monthData = months[month];
                let planIncome = doctorPlan ? doctorPlan[month] : 0;

                let plannedSessions = daySheets.filter(daySheet => {
                    return daySheet.day_sheet_owner_id == docId
                        && daySheet.date >= monthData.month_start
                        && daySheet.date <= monthData.month_end;
                });

                let monthStart = monthData.month_start;
                let monthEnd = monthData.month_end;

                if (monthStart < filters.date_start) {
                    monthStart = filters.date_start;
                }

                if (monthEnd > filters.date_end) {
                    monthEnd = filters.date_end;
                }

                let factSessionsCount = plannedSessions.filter(row => {
                    return row.date >= monthStart && row.date <= monthEnd;
                }).length;

                totalPlanned += (plannedSessions.length > 0) ? (planIncome / plannedSessions.length * factSessionsCount) : 0;
            });
            return totalPlanned;
        },
        getReportData() {
            let filters = this.prepareFilters();
            if (this.filterIsEmpty()) {
                return this.$error(__('Выберите фильтры для поиска'));
            }
            this.loading = true;
            this.getIncomePlans(filters).then(response => {
                this.doctorPlans = response;
                let daySheetFilter = this.getDaySheetFilters(filters);
                this.fetchData(filters, daySheetFilter).then(results => {
                    this.mapResults(results, filters, daySheetFilter);
                    this.loading = false;
                });
            });
        },
        fetchData(filters, daySheetFilter) {
            let resultData = {};
            return new Promise((resolve) => {
                let getDataRows = async () => {
                    let clinics = filters.clinic;
                    let clinicsCount = clinics.length;

                    for(let i = 0; i < clinicsCount; i++) {
                        let filter = {...filters, clinic: clinics[i] };
                        let payments = await this.getPayments({...filter, clinic: clinics[i] });
                        let appointments = await this.getAppointments({...filter, clinic: clinics[i] });
                        let daySheets = await this.getDaySheets({...daySheetFilter, clinic: clinics[i] });
                        let results = IncomeBuilder.prepareData(payments);
                        let clinicName = this.getClinicName(clinics[i]);
                        resultData[clinicName] = {
                            clinic_id: clinics[i],
                            payments: IncomeBuilder.addMissingAppointmentColumns(results, appointments),
                            appointments,
                            daySheets
                        };
                    }
                    resolve(resultData);
                }
                getDataRows();
            });
        },
        getIncomePlans(filters) {
            let plan = new DoctorIncomePlanRepository();
            let filter = {
                clinic: filters.clinic,
                year: new Date(filters.date_start).getFullYear(),
            };
            return plan.fetchList(filter, null, ['specialization']);
        },
        prepareExportRows() {
            return this.tableData.map(row => {
                return [
                    row.clinic_name,
                    row.specialization,
                    row.employee,
                    Number(row.plan),
                    Number(row.fact),
                    Number(row.progression),
                    Number(row.is_first),
                    Number(row.repeated),
                    Number(row.treatments),
                    Number(row.treatment_percent),
                    Number(row.avg_check),
                ];
            });
        },
        exportExcel() {
            let rows = this.prepareExportRows();
            let columns = [
                {name: __('Клиника')},
                {name: __('Специализация')},
                {name: __('Врач')},
                {name: __('План')},
                {name: __('Факт')},
                {name: __('Выполнение плана')},
                {name: __('I пац')},
                {name: __('II пац')},
                {name: __('Взявшие леч.')},
                {name: __('% за лечение')},
                {name: __('Средний чек')},
            ];

            fileGenerator.exportPlain(rows, columns).then((book) => {
                book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), __('Рейтинг') + '.xlsx')
                }).catch((err) => {
                    console.error(err);
                    this.$error(__('Не удалось сохранить отчет'));
                });
            });
        },
    },
}
</script>
