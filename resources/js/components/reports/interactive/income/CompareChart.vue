<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-switch
                        :entity="filters"
                        :options="filterTypes"
                        property="filterType"
                        :label="__('Показать по:')"
                    />
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
                    <form-select
                        :entity="filters"
                        :options="specializations"
                        :multiple="true"
                        :clearable="true"
                        property="specialization"
                        :label="__('Специализация')" />
                </el-col>
                <el-col :span="6">
                    <form-row
                        name="dates"
                        :label="__('Период сравнения 1')">
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
                    <form-row
                        name="dates"
                        :label="__('Период сравнения 2')">
                        <div class="form-input-group">
                            <form-date
                                :entity="filters"
                                property="alternative_date_start"
                                :clearable="true" />
                            <form-date
                                :entity="filters"
                                property="alternative_date_end"
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
                <ie-charts
                    :option="options"
                    @ready="onReady"
                    @click="onClick"
                />
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
                specialization: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                alternative_date_start: this.$moment().format('YYYY-MM-DD'),
                alternative_date_end: this.$moment().format('YYYY-MM-DD'),
                compare: COMPARE_PAYMENTS,
                currency: CONSTANTS.CURRENCY.UAH,
                filterType: null,
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
        }
    },
    mounted() {
        this.filters.filterType = this.filterByClinic;
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
            return _.isVoid(this.filters.date_start)
                || _.isVoid(this.filters.date_end)
                || _.isVoid(this.filters.alternative_date_start)
                || _.isVoid(this.filters.alternative_date_end);
        },
        getData() {
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
        fetchSpecializationReportData(filters) {
            let alteraneFilters = this.getAlternateFilters(filters);
            Promise.all([this.getSpecializationPayments(filters), this.getSpecializationPayments(alteraneFilters)])
                .then(payments => {
                    Promise.all([this.getSpecializationAppointments(filters), this.getSpecializationAppointments(alteraneFilters)])
                        .then(appointments => {
                            this.setChartDimensions(this.filterBySpecialization);
                            this.payments = payments;
                            this.appointments = appointments;
                            this.mapSpecializationResult().then(() => {
                                this.loading = false;
                            });
                        });
                });
        },
        fetchClinicReportData(filters) {
            let alteraneFilters = this.getAlternateFilters(filters);
            Promise.all([this.getClinicPayments(filters), this.getClinicPayments(alteraneFilters)])
                .then(payments => {
                    Promise.all([this.getClinicAppointments(filters), this.getClinicAppointments(alteraneFilters)])
                        .then(appointments => {
                            this.setChartDimensions(this.filterByClinic);
                            this.payments = payments;
                            this.appointments = appointments;
                            this.mapClinicResult().then(() => {
                                this.loading = false;
                            });
                        });
                });
        },
        getAlternateFilters(filters) {
            let tempFilters = {...filters};
            tempFilters.date_start = filters.alternative_date_start;
            tempFilters.date_end = filters.alternative_date_end;
            return tempFilters;
        },
        mapClinicResult() {
            if (this.filters.compare === COMPARE_IS_FIRST || this.filters.compare === COMPARE_REPEATED) {
                return this.mapClinicChartAppointments();
            } else if (this.filters.compare === COMPARE_PAYMENTS) {
                return this.mapClinicChartPayments();
            }
        },
        mapSpecializationResult() {
            if (this.filters.compare === COMPARE_IS_FIRST || this.filters.compare === COMPARE_REPEATED) {
                return this.mapSpecializationChartAppointments();
            } else if (this.filters.compare === COMPARE_PAYMENTS) {
                return this.mapSpecializationChartPayments();
            }
        },
        mapClinicChartPayments() {
            let periodPayments = this.mapChartClinics(this.payments[0].aggr_group);
            let altPeriodPayments = this.mapChartClinics(this.payments[1].aggr_group);
            return this.mapChartPayments(periodPayments, altPeriodPayments, this.filterByClinic);
        },
        mapClinicChartAppointments() {
            let periodAppointments = this.mapChartClinics(this.appointments[0].aggr_group);
            let altPeriodAppointments = this.mapChartClinics(this.appointments[1].aggr_group);
            return this.mapChartAppointments(periodAppointments, altPeriodAppointments, this.filterByClinic);
        },
        mapSpecializationChartPayments() {
            let periodPayments = this.mapChartSpecializations(this.payments[0].aggr_group);
            let altPeriodPayments = this.mapChartSpecializations(this.payments[1].aggr_group);
            return this.mapChartPayments(periodPayments, altPeriodPayments, this.filterBySpecialization);
        },
        mapSpecializationChartAppointments() {
            let periodAppointments = this.mapChartSpecializations(this.appointments[0].aggr_group);
            let altPeriodAppointments = this.mapChartSpecializations(this.appointments[1].aggr_group);
            return this.mapChartAppointments(periodAppointments, altPeriodAppointments, this.filterBySpecialization);
        },
        mapChartPayments(periodPayments, altPeriodPayments, rowKey) {
            let chartKeys = this.getChartDataKeys(periodPayments, altPeriodPayments);
            let chartData = [];
            chartKeys.forEach((key, index) => {
                let row = {};
                let period = periodPayments.find(p => p.itemName === key);
                let alternate = altPeriodPayments.find(p => p.itemName === key);
                row[rowKey] = this.getAxisLabel(index, key);
                row[this.options.dataset.dimensions[1]] = period ? Number(Math.ceil(period.payed_amount.value)) : 0;
                row[this.options.dataset.dimensions[2]] = alternate ? Number(Math.ceil(alternate.payed_amount.value)) : 0;
                chartData.push(row);
            });
            this.options.dataset.source = chartData;
            return Promise.resolve();
        },
        mapChartAppointments(periodAppointments, altPeriodAppointments, rowKey) {
            let chartKeys = this.getChartDataKeys(periodAppointments, altPeriodAppointments);
            let chartData = [];
            let patientKey = this.filters.compare;
            chartKeys.forEach((key, index) => {
                let row = {};
                let period = periodAppointments.find(p => p.itemName === key);
                let alternate = altPeriodAppointments.find(p => p.itemName === key);
                row[rowKey] = this.getAxisLabel(index, key);
                row[this.options.dataset.dimensions[1]] = period ? period[patientKey].value : 0;
                row[this.options.dataset.dimensions[2]] = alternate ? alternate[patientKey].value : 0;
                chartData.push(row);
            });
            this.options.dataset.source = chartData;
            return Promise.resolve();
        },
        setChartDimensions(dimension) {
            let dateStart = this.getFormattedDate(this.filters.date_start);
            let dateEnd = this.getFormattedDate(this.filters.date_end);
            let dateAltStart = this.getFormattedDate(this.filters.alternative_date_start);
            let dateAltEnd = this.getFormattedDate(this.filters.alternative_date_end);
            this.options.dataset.dimensions = [dimension];
            this.options.dataset.dimensions.push(dateStart + ' - ' + dateEnd);
            this.options.dataset.dimensions.push(dateAltStart + ' - ' + dateAltEnd);
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
    },
    watch: {
        ['filters.compare']() {
            if (this.filters.filterType === this.filterBySpecialization) {
                this.mapSpecializationResult();
            } else {
                this.mapClinicResult();
            }
        },
    }
}

</script>
