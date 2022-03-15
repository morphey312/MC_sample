<template>
    <div v-loading="loading">
        <section class="grey">
            <chart-filter
                ref="filter"
                :initial-state="filters"
                :filter-types="filterTypes"
                @changed="getData"/>
        </section>
        <section >
            <div class="echarts" style="height: 35vh" v-if="showChart">
                <ie-charts
                    :option="moneyChart"
                    @ready="onReady"
                    @click="onClick"
                />
            </div>
            <div class="echarts" style="height: 35vh" v-if="showChart">
                <ie-charts
                    :option="patientChart"
                    @click="onClick"
                    @ready="onReady"
                />
            </div>
        </section>
    </div>
</template>
<script>
import ElasticMixin from './mixins/elastic';
import ChartMixin from './mixins/chart';
import ChartFilter from './ChartFilter.vue';

export default {
    mixins: [
        ElasticMixin,
        ChartMixin,
    ],
    components: {
        ChartFilter,
    },
    data() {
        return {
            moneyChart: {
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
                        return name + ' - ' + this.$formatter.numberFormat(this.getPaymentTotal()) + 
                            ' ' + this.$handbook.getOption('currency', this.filters.currency);
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
            patientChart: {
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
                    data: [__('Первичные'), __('Повторные')],
                    formatter: (name) => {
                        let total = 0;
                        if (__('Первичные') === name) {
                            total = this.getIsFirstTotals();
                        } else {
                            total = this.getRepeatedTotals();
                        }
                        return name + ' - ' + total;
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
                        name: __('Первичные'),
                        type: 'bar',
                        barGap: 0,
                        label: this.getLabelOptions(),
                        data: []
                    },
                    {
                        name: __('Повторные'),
                        type: 'bar',
                        label: this.getLabelOptions(),
                        data: []
                    },
                ]
            }
        }
    },
    methods: {
        getIsFirstTotals() {
            return this.getSeriesTotal(this.patientChart);
        },
        getRepeatedTotals() {
            return this.getSeriesTotal(this.patientChart, 1);
        },
        getPaymentTotal() {
            return this.getSeriesTotal(this.moneyChart);
        },
        fetchClinicReportData(filters) {
            this.getClinicPayments(filters).then(payments => {
                this.getClinicAppointments(filters).then(appointments => {
                    let paymentData = this.mapChartClinics(payments.aggr_group);
                    let appointmentData = this.mapChartClinics(appointments.aggr_group);
                    this.mapChartPayments(paymentData);
                    this.mapChartAppointments(appointmentData);
                    this.loading = false;
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
        fetchSpecializationReportData(filters) {
            this.getSpecializationPayments(filters).then(payments => {
                this.getSpecializationAppointments(filters).then(appointments => {
                    let paymentData = this.mapChartSpecializations(payments.aggr_group);
                    let appointmentData = this.mapChartSpecializations(appointments.aggr_group);
                    this.mapChartPayments(paymentData);
                    this.mapChartAppointments(appointmentData);
                    this.loading = false;
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
        mapChartPayments(paymentData) {
            this.moneyChart.xAxis.data = this.mapChartLabels(paymentData);
            this.moneyChart.series[0].data = paymentData.map(item => Number(Math.ceil(item.payed_amount.value)));
        },
        mapChartAppointments(appointmentData) {
            this.patientChart.xAxis.data = this.mapChartLabels(appointmentData);
            this.patientChart.series[0].data = appointmentData.map(item => item.is_first.value);
            this.patientChart.series[1].data = appointmentData.map(item => item.repeated.value);
        },
        mapChartLabels(data) {
            return data.map((item, index) => {
                return this.getAxisLabel(index, item.itemName);
            });
        },
    }
}
</script>
