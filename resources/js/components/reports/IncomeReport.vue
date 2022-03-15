<template>
    <page
        :title="__('Отчеты Инкам')"
        v-loading="loading"
        :element-loading-text="__('Генерация отчета')"
        type="flex">
        <report-filter
            ref="filter"
            :filters="filters"
            :permission="permission" />
        <section class="grey-cap pt-0">
            <manage-table
                ref="table"
                :fields="fields"
                :repository="repository">
                <div
                    class="buttons"
                    slot="footer-top">
                    <el-button
                        @click="exportExcel"
                        :disabled="!$can('finance-reports.income')">
                        {{ __('Экспорт в Excel') }}
                    </el-button>
                </div>
            </manage-table>
        </section>
    </page>
</template>
<script>
import DefaultReport from './Default.vue';
import IncomeReportRepository from '@/repositories/reports/finance/income';
import AppointmentRepository from '@/repositories/appointment';
import incomeGenerator from './generators/finance/income';
import ReportFilter from './Filter.vue';
import FileSaver from 'file-saver';
import CONSTANTS from '@/constants';
import IncomeMixin from './mixins/income-report';

export default {
    extends: DefaultReport,
    mixins: [
        IncomeMixin,
    ],
    components: {
        ReportFilter,
    },
    data() {
        return {
            loading: false,
            permission: 'finance-reports',
            fields: [
                {
                    name: 'name',
                    title: __('Список отчетов'),
                },
            ],
            filters: {
                clinic: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                is_deleted: false,
            },
            rows: [
                {
                    id: 1,
                    visible: this.$can('finance-reports.income'),
                    name: __('Инкам'),
                    fileName: __('Отчет - График.xlsx'),
                    repository: new IncomeReportRepository(),
                    generator: incomeGenerator,
                },
            ].filter(i => i.visible === true),
        }
    },
    methods: {
        exportExcel() {
            if (this.filters.clinic.length == 0) {
                return this.$error(__('Выберите хотя бы одну клинику'));
            }

            if (_.isVoid(this.filters.date_start) || _.isVoid(this.filters.date_end)) {
                return this.$error(__('Выберите период'));
            }

            let reportRepo = new IncomeReportRepository();
            this.loading = true;

            let getDataRows = async () => {
                let filters = _.onlyFilled(this.filters);
                if (this.$isAccessLimited(this.permission) && filters.clinic.length === 0) {
                    filters.clinic = this.$store.state.user.clinics;
                }

                for (let clinic of filters.clinic) {
                    let currentData = await reportRepo.fetch({...filters, clinic});
                    let currentAppointments = await reportRepo.fetchAppointments(this.getAppointmentFilters(filters));

                    let date_start = this.subtractDate(filters.date_start).startOf('month').format('YYYY-MM-DD');
                    let date_end = this.subtractDate(filters.date_start).endOf('month').format('YYYY-MM-DD');
                    let prevMonthData = await reportRepo.fetch({...filters, clinic, date_start, date_end});
                    let prevMonthAppointments = await reportRepo.fetchAppointments(this.getAppointmentFilters({...filters, date_start, date_end}));

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
                    }

                    incomeGenerator(data).then((book) => {
                        book.xlsx.writeBuffer().then((buffer) => {
                            this.getClinicName(clinic).then((clinicName) => {
                                FileSaver.saveAs(new Blob([buffer]), this.getFileName(clinicName))
                            });
                        }).catch((err) => {
                            console.error(err);
                            this.$error(__('Не удалось сохранить отчет'));
                        });
                    });
                }
            }
            getDataRows().then(() => {
                this.loading = false;
            });
        },
        getAppointmentFilters(filters) {
            return {
                clinic: filters.clinic,
                date_start: filters.date_start,
                date_end: filters.date_end,
                is_deleted: filters.is_deleted,
                skip_system_status: [
                    CONSTANTS.APPOINTMENT.STATUSES.DID_NOT_COME,
                    CONSTANTS.APPOINTMENT.STATUSES.DELETED,
                    CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP,
                    CONSTANTS.APPOINTMENT.STATUSES.PAYED,
                    CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_RECEPTION,
                ],
            }
        },
    },
}
</script>