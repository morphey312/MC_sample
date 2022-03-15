<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="clinics"
                        :clearable="true"
                        :multiple="true"
                        :filterable="true"
                        property="clinic"
                        :label="__('Клиника')" />
                </el-col>
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="periods"
                        property="periodGroup"
                        :label="__('Группировать по:')" />
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
            </el-row>
            <div class="buttons">
                <el-button
                    @click="getReportData()"
                    type="primary">
                    {{ __('Показать') }}
                </el-button>
            </div>
        </section>
        <section class="pt-0">
            <div class="echarts" style="height: 70vh; width: 100%" v-if="showChart">
                <ie-charts :option="options" />
            </div>
        </section>
    </div>
</template>
<script>
import ChartMixin from './mixins/chart';
import CONSTANTS from '@/constants';

const GROUP_ONE_WEEK = 'week';
const GROUP_ONE_MONTH = 'month';
const ISO_WEEK = 'isoWeek';

export default {
    mixins: [
        ChartMixin,
    ],
    data() {
        return {
            reportType: 'period-chart',
            filters:  {
                clinic: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                periodGroup: GROUP_ONE_MONTH,
            },
            periods: [
                {
                    id: GROUP_ONE_MONTH,
                    value: __('Месяцу'),
                }
            ],
            options: {
                tooltip: this.getTooltip(),
                dataZoom: this.getDataZoom(),
                toolbox: {
                    orient: 'vertical',
                    feature: {
                        magicType: {
                            show: true,
                            type: ['line', 'bar'],
                            title: {
                                line: __('line charts'),
                                bar: __('bar charts'),
                            }
                        },
                        restore: {
                            show: true,
                            title: __('Обновить'),
                        },
                        saveAsImage: {
                            show: true,
                            title: __('Сохранить изображение')
                        }
                    }
                },
                legend: {
                    data: [__('Внешние'), __('Внутренние'), __('Оборот, грн')],
                    top: 30,
                },
                color: ['#E87C7C', '#1B6EBE', '#B7B308'],
                xAxis: [
                    {
                        type: 'category',
                        data: [],
                        axisPointer: {
                            type: 'shadow'
                        },
                        axisLabel: {
                            inside: false,
                            interval: 0,
                            textStyle: {
                                color: '#000',
                                fontSize: 9
                            },
                        },
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: __('Количество платежей'),
                    },
                    {
                        type: 'value',
                        name: __('Оборот, грн'),
                        axisLabel: {
                            formatter: '{value}' + __(', грн')
                        }
                    }
                ],
                series: [
                    {
                        name: __('Внешние'),
                        type: 'bar',
                        data: [],
                        label: this.getLabelOptions(),
                    },
                    {
                        name:  __('Внутренние'),
                        type: 'bar',
                        data: [],
                        label: this.getLabelOptions(),
                    },
                    {
                        name: __('Оборот, грн'),
                        type: 'line',
                        yAxisIndex: 1,
                        data: []
                    }
                ]
            }
        }
    },
    methods: {
        getReportData() {
            this.getData(this.filters, this.reportType).then(data => {
                this.prepareData(data);
                this.loading = false;
            }).catch(e => {
                console.error(e);
                this.$error();
                this.loading = false;
            });;
        },
        prepareData(data) {
            let chartKeys = _.uniq([...Object.keys(data[0].payments), ...Object.keys(data[1].payments)]).sort();
            this.mapChartData(data, chartKeys);
            this.options.xAxis[0].data = this.mapChartLabels(chartKeys);
        },
        mapChartLabels(chartKeys) {
            let format = 'DD.MM.YYYY';
            return chartKeys.map((key, index) => {
                let date = this.$moment(key);
                let label = this.$formatter.dateFormat(date, format) + '-' + this.$formatter.dateFormat(date.endOf(this.filters.periodGroup), format);
                return this.getAxisLabel(index, label);
            });
        },
        mapChartData(data, chartKeys) {
            let external = [];
            let internal = [];
            let total = [];
            chartKeys.forEach(key => {
                let externalPayments = data[0].payments[key];
                let internalPayments = data[1].payments[key];
                external.push(externalPayments ? externalPayments.length : 0);
                internal.push(internalPayments ? internalPayments.length : 0);

                total.push(
                    [...externalPayments, ...internalPayments].reduce((sum, payment) => {
                        if (payment.type === CONSTANTS.PAYMENT.TYPES.INCOME) {
                            sum += Number(payment.payed_amount);
                        } else if (payment.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
                            sum -= Number(payment.payed_amount);
                        }
                        return sum;
                    }, 0)
                );
            });
            this.options.series[0].data = external;
            this.options.series[1].data = internal;
            this.options.series[2].data = total;
        },
    }
}
</script>
