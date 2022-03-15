<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="compareOptions"
                        property="compare"
                        :label="__('Тип сравнения')" />
                </el-col>
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
                    <form-row
                        name="dates"
                        :label="__('Период')">
                        <div class="form-input-group">
                            <form-date
                                :entity="filters"
                                property="date_start"
                                :clearable="true" />
                            <form-date
                                :entity="filters"
                                property="date_end"
                                :clearable="true" />
                        </div>
                    </form-row>
                </el-col>
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        options="currency"
                        property="currency"
                        :label="__('Валюта')" />
                </el-col>
            </el-row>
            <div class="buttons">
                <el-button
                    @click="getData()"
                    type="primary">
                    {{ __('Показать') }}
                </el-button>
            </div>
        </section>
        <section>
            <div class="echarts" style="height: 70vh; width: 100%" v-if="showChart">
                <ie-charts :option="options" />
            </div>
        </section>
    </div>
</template>
<script>
import ElasticMixin from './mixins/elastic';
import ChartMixin from './mixins/chart';
import CONSTANTS from '@/constants';

const COMPARE_PAYMENTS = 'payments';
const COMPARE_IS_FIRST = 'is_first';
const COMPARE_REPEATED = 'repeated';

export default {
    mixins: [
        ElasticMixin,
        ChartMixin,
    ],
    data() {
        return {
            filters: {
                clinic: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                compare: COMPARE_PAYMENTS,
                currency: CONSTANTS.CURRENCY.UAH,
            },
            compareOptions: [
                {
                    id: COMPARE_PAYMENTS,
                    value: __('Оборот'),
                },
                {
                    id: COMPARE_IS_FIRST,
                    value: __('Первичные'),
                },
                {
                    id: COMPARE_REPEATED,
                    value: __('Повторные'),
                }
            ],
            options: {
                tooltip: this.getTooltip(),
                toolbox: {
                    feature: {
                        saveAsImage: {
                            show: true,
                            title: __('Сохранить изображение')
                        }
                    }
                },
                color: ['#E87C7C', '#1B6EBE'],
                dataset: {
                    dimensions: [],
                    source: []
                },
                legend: {
                    formatter: (name) => {
                        if (name == this.options.dataset.dimensions[1] || name == this.options.dataset.dimensions[2]) {
                            return name + '(' + this.getLegendTotal(name) + ')';
                        }
                        return name;
                    }
                },
                xAxis: {
                    type: 'category',
                    axisLabel: {
                        inside: false,
                        interval: 0,
                        textStyle: {
                            color: '#000000',
                            fontSize: 10
                        }
                    },
                },
                yAxis: {},
                series: [
                    {
                        type: 'bar',
                        label: '{@[1]}',
                    },
                    {
                        type: 'bar',
                        label: '{@[2]}',
                    },
                ]
            },
            appointments: [],
            payments: [],
            daysInMonth: this.$moment().daysInMonth(),
            selectedDaysCount: 1,
        }
    },
    methods: {
        getLegendTotal(name) {
            if (this.options) {
                let total = this.options.dataset.source.reduce((total, item) => {
                    return total + item[name];
                }, 0);
                return (this.filters.compare === COMPARE_PAYMENTS)
                    ? this.$formatter.numberFormat(total)
                    : total;
            }
            return 0;
        },
        filterIsEmpty() {
            return _.isVoid(this.filters.date_start) || _.isVoid(this.filters.date_end);
        },
        getData() {
            if (this.filterIsEmpty()) {
                return this.$error(__('Выберите фильтры для поиска'));
            }
            let filters = this.prepareFilters();
            this.loading = true;
            return this.fetchClinicReportData(filters);
        },
        fetchClinicReportData(filters) {
            this.getClinicPayments(filters).then(payments => {
                this.getClinicAppointments(filters).then(appointments => {
                    this.setDaysCount();
                    this.setChartDimensions();
                    this.payments = payments;
                    this.appointments = appointments;
                    this.mapClinicResult().then(() => {
                        this.loading = false;
                    });
                });
            });
        },
        setDaysCount() {
            this.selectedDaysCount = this.$moment(this.filters.date_end).diff(this.$moment(this.filters.date_start), 'days') + 1;
        },
        setChartDimensions() {
            let dateStart = this.getFormattedDate(this.filters.date_start);
            let dateEnd = this.getFormattedDate(this.filters.date_end);
            let monthStart = this.getFormattedDate(this.$moment().startOf('month'));
            let monthEnd = this.getFormattedDate(this.$moment().endOf('month'));
            this.options.dataset.dimensions = ['clinic'];
            this.options.dataset.dimensions.push(dateStart + ' - ' + dateEnd);
            this.options.dataset.dimensions.push(monthStart + ' - ' + monthEnd);
            this.setChartLabelSettings();
        },
        setChartLabelSettings() {
            this.options.series[0].label = this.getLabelSettings(1);
            this.options.series[1].label = this.getLabelSettings(2);
        },
        getLabelSettings(index) {
            return {
                ...this.getLabelOptions(),
                distance: 40,
                formatter: (this.isOdd(index) ? '\n\n\n' : '') + `{@[${index}]}`,
            }
        },
        mapClinicResult() {
            if (this.filters.compare === COMPARE_IS_FIRST || this.filters.compare === COMPARE_REPEATED) {
                return this.mapClinicChartAppointments();
            } else if (this.filters.compare === COMPARE_PAYMENTS) {
                return this.mapClinicChartPayments();
            }
        },
        mapClinicChartPayments() {
            let payments = this.mapChartClinics(this.payments.aggr_group);
            return this.mapChartPayments(payments, this.filterByClinic);
        },
        mapClinicChartAppointments() {
            let appointments = this.mapChartClinics(this.appointments.aggr_group);
            return this.mapChartAppointments(appointments, this.filterByClinic);
        },
        getPrognosis(val) {
            let dayAvg = Number(val) / this.selectedDaysCount;
            return Math.ceil(dayAvg * this.daysInMonth);
        },
        mapChartPayments(payments, rowKey) {
            let chartKeys = this.getChartDataKeys(payments);
            let chartData = [];
            chartKeys.forEach((key, index) => {
                let row = {};
                let clinicPayments = payments.find(p => p.itemName === key);
                row[rowKey] = this.getAxisLabel(index, key);
                row[this.options.dataset.dimensions[1]] = clinicPayments ? Number(Math.ceil(clinicPayments.payed_amount.value)) : 0;
                row[this.options.dataset.dimensions[2]] = clinicPayments ? this.getPrognosis(clinicPayments.payed_amount.value) : 0;
                chartData.push(row);
            });
            this.options.dataset.source = chartData;
            return Promise.resolve();
        },
        mapChartAppointments(appointments, rowKey) {
            let chartKeys = this.getChartDataKeys(appointments);
            let chartData = [];
            let patientKey = this.filters.compare;
            chartKeys.forEach((key, index) => {
                let row = {};
                let clinicAppointments = appointments.find(p => p.itemName === key);
                row[rowKey] = this.getAxisLabel(index, key);
                row[this.options.dataset.dimensions[1]] = clinicAppointments ? clinicAppointments[patientKey].value : 0;
                row[this.options.dataset.dimensions[2]] = clinicAppointments ? this.getPrognosis(clinicAppointments[patientKey].value) : 0;
                chartData.push(row);
            });
            this.options.dataset.source = chartData;
            return Promise.resolve();
        },
    },
    watch: {
        ['filters.compare']() {
            this.mapClinicResult();
        },
    }
}
</script>
