<template>
    <div v-loading="loading">
        <section class="grey">
            <chart-filter 
                ref="filter"
                :initial-state="filters"
                :filter-types="filterTypes"
                @changed="getData"/>
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
            options: {
                tooltip: this.getTooltip(),
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
                    data: [__('Первичные'), __('Повторные'), __('Оборот')],
                    formatter: (name) => {
                        let total = 0;
                        if (__('Первичные') === name) {
                            total = this.getIsFirstTotals();
                        } else if (__('Повторные') === name) {
                            total = this.getRepeatedTotals();
                        } else {
                            total = this.getPaymentTotal();
                        }
                        return name + ' - ' + total;
                    },
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
                                color: '#000000',
                                fontSize: 10
                            }
                        },
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: __('Количество пациентов'),
                        min: 0,
                        max: 0,
                    },
                    {
                        type: 'value',
                        name: __('Оборот, грн'),
                        min: 0,
                        max: 0,
                        axisLabel: {
                            formatter: '{value}'
                        }
                    }
                ],
                series: [
                    {
                        name: __('Первичные'),
                        type: 'bar',
                        data: [],
                        label: this.getLabelOptions(),
                    },
                    {
                        name:  __('Повторные'),
                        type: 'bar',
                        data: [],
                        label: this.getLabelOptions(),
                    },
                    {
                        name: __('Оборот'),
                        type: 'line',
                        yAxisIndex: 1,
                        data: []
                    }
                ]
            }
        }
    },
    methods: {
        getIsFirstTotals() {
            return this.getSeriesTotal(this.options);
        },
        getRepeatedTotals() {
            return this.getSeriesTotal(this.options, 1);
        },
        getPaymentTotal() {
            return this.getSeriesTotal(this.options, 2);
        },
        fetchClinicReportData(filters) {
            this.getClinicPayments(filters).then(payments => {
                this.getClinicAppointments(filters).then(appointments => {
                    this.prepareClinicChartData(payments, appointments);
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
                    this.prepareSpecializationChartData(payments, appointments);
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
        prepareClinicChartData(payments, appointments) {
            let paymentsData = this.mapChartClinics(payments.aggr_group);
            let appointmentsData = this.mapChartClinics(appointments.aggr_group);
            this.prepareData(paymentsData, appointmentsData);
        },
        prepareSpecializationChartData(payments, appointments) {
            let paymentsData = this.mapChartSpecializations(payments.aggr_group);
            let appointmentsData = this.mapChartSpecializations(appointments.aggr_group);
            this.prepareData(paymentsData, appointmentsData);
        },
        prepareData(paymentsData, appointmentsData) {
            let chartKeys = _.uniq([...paymentsData, ...appointmentsData]
                .map(c => c.itemName))
                .sort();
            this.options.xAxis[0].data = this.mapChartLabels(chartKeys);
            this.mapChartPayments(paymentsData, chartKeys);
            this.mapChartAppointments(appointmentsData, chartKeys);
        },
        mapChartLabels(data) {
            return data.map((label, index) => {
                return this.getAxisLabel(index, label);
            });
        },
        mapChartPayments(payments, chartKeys) {
            let results = [];
            chartKeys.forEach(key => {
                let clinicIndex = payments.findIndex(item => item.itemName == key);
                if (clinicIndex != -1) {
                    results.push(Number(Math.ceil(payments[clinicIndex].payed_amount.value)));
                } else {
                    results.push(0);
                }
            });
            this.options.series[2].data = results;
            this.options.yAxis[1].max = _.max(results);
        },
        mapChartAppointments(appointments, chartKeys) {
            let isFirst = [];
            let repeated = [];
            chartKeys.forEach(key => {
                let clinicIndex = appointments.findIndex(item => item.itemName == key);
                if (clinicIndex != -1) {
                    isFirst.push(appointments[clinicIndex].is_first.value);
                    repeated.push(appointments[clinicIndex].repeated.value);
                } else {
                    isFirst.push(0);
                    repeated.push(0);
                }
            });
            this.options.series[0].data = isFirst;
            this.options.series[1].data = repeated;
            this.options.yAxis[0].max = _.max([...isFirst, ...repeated]);
        },
    },
}
</script>