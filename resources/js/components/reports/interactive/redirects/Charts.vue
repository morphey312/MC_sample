<template>
    <div v-loading="loading">
        <section class="grey">
            <chart-filter 
                ref="filter"
                :initial-state="filters"
                @changed="getReportData"/>
        </section>
        <section>
            <div class="echarts" style="height: 35vh" v-if="showChart">
                <ie-charts
                    :option="commonChart"
                    @ready="onReady"
                    @click="onClick"
                />
            </div>
            <div class="echarts" style="height: 35vh" v-if="showChart">
                <ie-charts
                    :option="redirectsChart"
                    @click="onClick"
                    @ready="onReady"
                />
            </div>
        </section>
    </div>
</template>
<script>
import ChartMixin from './mixins/chart';
import ChartFilter from './ChartFilter.vue';

export default {
    mixins: [
        ChartMixin,
    ],
    components: {
        ChartFilter,
    },
    data() {
        return {
            filteredValues: {},
            reportType: 'chart',
            commonChart: {
                tooltip: this.getTooltip(),
                toolbox: {
                    feature: {
                        saveAsImage: {
                            show: true,
                            title: __('Сохранить изображение')
                        }
                    }
                },
                color: ['#B7B308'],
                legend: {
                    data: [__('Оборот')],
                    formatter: (name) => {
                        return name + ' - ' + this.getPaymentTotal() + __(', грн');
                    }
                },
                xAxis: {
                    type: 'category',
                    axisTick: {show: false},
                    data: [],
                    axisLabel: {
                        inside: false,
                        interval: 0,
                        textStyle: {
                            color: '#000',
                            fontSize: 12
                        }
                    },
                },
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: [
                    {
                        name: __('Оборот'),
                        type: 'bar',
                        label: this.getLabelOptions(),
                        data: []
                    },
                ]
            },
            redirectsChart: {
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
                legend: {
                    data: [__('Внешние'), __('Внутренние')],
                    formatter: (name) => {
                        let total = 0;
                        if (__('Внешние') === name) {
                            total = this.getExternalTotals();
                        } else {
                            total = this.getInternalTotals();
                        }
                        return name + ' - ' + total + __(', грн');
                    }
                },
                xAxis: {
                    type: 'category',
                    axisTick: {show: false},
                    data: [],
                    axisLabel: {
                        inside: false,
                        interval: 0,
                        textStyle: {
                            color: '#000000',
                            fontSize: 10
                        }
                    },
                },
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: [
                    {
                        name: __('Внешние'),
                        type: 'bar',
                        barGap: 0,
                        label: this.getLabelOptions(),
                        data: []
                    },
                    {
                        name: __('Внутренние'),
                        type: 'bar',
                        label: this.getLabelOptions(),
                        data: []
                    },
                ]
            }
        }
    },
    methods: {
        onReady() {
        },
        onClick() {
        },
        getExternalTotals() {
            return this.getLabelTotal(this.redirectsChart);
        },
        getInternalTotals() {
            return this.getLabelTotal(this.redirectsChart, 1);
        },
        getPaymentTotal() {
            return this.getLabelTotal(this.commonChart);
        },
        getLabelTotal(chart, seriesIndex = 0) {
            return chart ? this.$formatter.numberFormat(_.sum(chart.series[seriesIndex].data)) : 0;
        },
        getReportData(filters) {
            this.getData(filters, this.reportType).then(data => {
                this.mapChartData(data);
                this.loading = false;
            });
        },
        mapChartData(employeeData) {
            this.filteredValues = {};
            employeeData = this.addClinicNameToEmployee(employeeData);
            let clinicData = this.groupByKey(employeeData);
            let labelKeys = Object.keys(clinicData).filter(c => _.isFilled(c)).sort();
            this.mapChartlabels(clinicData, labelKeys);
            this.mapRedirectsChart(clinicData, labelKeys);
            this.mapCommonChart(clinicData, labelKeys);
        },
        mapChartlabels(clinicData, labelKeys) {
            let labels = labelKeys.map((item, index) => {
                return this.getAxisLabel(index, item);
            });
            this.commonChart.xAxis.data = labels;
            this.redirectsChart.xAxis.data = labels;
        },
        mapCommonChart(clinicData, labelKeys) {
            this.commonChart.series[0].data = [];

            labelKeys.forEach(clinic => {
                let total = Number(
                    this.$formatter.numberFormat(this.filteredValues[clinic].external + this.filteredValues[clinic].internal)
                );
                this.commonChart.series[0].data.push(total);
            });
        },
        mapRedirectsChart(clinicData, labelKeys) {
            this.redirectsChart.series[0].data = [];
            this.redirectsChart.series[1].data = [];

            labelKeys.forEach(clinic => {
                let totalExternal = 0;
                let totalInternal = 0;
                clinicData[clinic].forEach(item => {
                    totalExternal += item.external.payed;
                    totalInternal += item.internal.payed;
                });
                this.redirectsChart.series[0].data.push(totalExternal);
                this.redirectsChart.series[1].data.push(totalInternal);
                this.filteredValues[clinic] = {
                    external: totalExternal,
                    internal: totalInternal,
                }
            });
        },
    }
}
</script>