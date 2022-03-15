<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="clinics"
                        :multiple="true"
                        :filterable="true"
                        :clearable="true"
                        property="clinic"
                        :label="__('Клиника')" />
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
                </el-col>
                <el-col :span="6">
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
            </el-row>
            <div class="buttons">
                <el-button
                    @click="getReportData()"
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
import ChartMixin from './mixins/chart';

export default {
    mixins: [
        ChartMixin,
    ],
    data() {
        return {
            reportType: 'chart',
            filters: {
                clinic: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                alternative_date_start: this.$moment().format('YYYY-MM-DD'),
                alternative_date_end: this.$moment().format('YYYY-MM-DD'),
            },
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
                legend: {},
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
        }
    },
    methods: {
        getAlternateFilters(filters) {
            let tempFilters = {...filters};
            tempFilters.date_start = filters.alternative_date_start;
            tempFilters.date_end = filters.alternative_date_end;
            return tempFilters;
        },
        getReportData() {
            let filters = this.prepareFilters();
            let alteraneFilters = this.getAlternateFilters(filters);
            this.fetchData(filters, this.reportType).then(periodData => {
                this.fetchData(alteraneFilters, this.reportType).then(alternatePeriodData => {
                    this.mapChartData(periodData, alternatePeriodData);
                    this.loading = false;
                });
            })
        },
        mapChartData(firstPeriodData, alternatePeriodData) {
            firstPeriodData = this.addClinicNameToEmployee(firstPeriodData);
            alternatePeriodData = this.addClinicNameToEmployee(alternatePeriodData);
            let firstPeriodClinicData = this.groupByKey(firstPeriodData);
            let alternatePeriodClinicData = this.groupByKey(alternatePeriodData);
            this.setChartDimensions();
            this.mapClinicChart(firstPeriodClinicData, alternatePeriodClinicData);
        },
        mapClinicChart(firstPeriodClinicData, alternatePeriodClinicData) {
            let chartData = [];
            let chartKeys = _.uniq([
                    ...Object.keys(firstPeriodClinicData),
                    ...Object.keys(alternatePeriodClinicData)
                ].sort()
            );
            chartKeys.forEach((key, index) => {
                let row = {};
                let clinicFirst = firstPeriodClinicData[key];
                let clinicAlternate = alternatePeriodClinicData[key];

                row['clinic'] = this.getAxisLabel(index, key);
                row[this.options.dataset.dimensions[1]] = clinicFirst ? this.getRedirectsSum(clinicFirst) : 0;
                row[this.options.dataset.dimensions[2]] = clinicAlternate ? this.getRedirectsSum(clinicAlternate) : 0;
                chartData.push(row);
            });
            this.options.dataset.source = chartData;
        },
        getRedirectsSum(list) {
            return list.reduce((sum, employee) => {
                return sum + (employee.external.payed + employee.internal.payed);
            }, 0);
        },
        setChartDimensions() {
            let dateStart = this.getFormattedDate(this.filters.date_start);
            let dateEnd = this.getFormattedDate(this.filters.date_end);
            let dateAltStart = this.getFormattedDate(this.filters.alternative_date_start);
            let dateAltEnd = this.getFormattedDate(this.filters.alternative_date_end);
            this.options.dataset.dimensions = ['clinic'];
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
    }
}
</script>
