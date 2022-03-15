<template>
    <div v-loading="loading">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="clinics"
                        :clearable="true"
                        :filterable="true"
                        property="clinic"
                        :label="__('Клиника')" />
                </el-col>
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="positions"
                        :multiple="true"
                        :clearable="true"
                        property="position"
                        :label="__('Должность')" />
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
import generator from './generator';

export default {
    mixins: [
        ChartMixin,
    ],
    data() {
        return {
            filters:  {
                clinic: null,
                position: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
            },
            options: {
                toolbox: {
                    feature: {
                        saveAsImage: {
                            show: true,
                            title: __('Сохранить изображение')
                        }
                    }
                },
                xAxis: {
                    type: 'category',
                    data: []
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value} %'
                    },
                },
                color: ['#E87C7C', '#1B6EBE'],
                legend: {
                    top: 30,
                    data: [__('% записи'), __('% прихода')],
                },
                series: [{
                    name: __('% записи'),
                    data: [],
                    type: 'line',
                    smooth: true,
                    label: {
                        ...this.getLabelOptions(),
                        formatter: '{c}%'
                    }
                },
                {
                    name: __('% прихода'),
                    data: [],
                    type: 'line',
                    smooth: true,
                    label: {
                        ...this.getLabelOptions(),
                        formatter: '{c}%'
                    }
                }]
            }
        }
    },
    methods: {
        getReportData() {
            if (_.isVoid(this.filters.clinic)) {
                return this.$error(__('Выберите клинику'));
            }

            this.fetchData(this.filters).then(response => {
                this.prepareData(generator(response));
                this.loading = false;
            });
        },
        prepareData(data) {
            let periods = [];
            let appointments = [];
            let income = [];

            Object.keys(data.byMonth).sort().forEach(key => {
                let month = data.byMonth[key];
                periods.push(this.$formatter.dateFormat(month.date, 'MMM YYYY'));
                appointments.push(month.calls > 0 ? Math.round(100 * month.appointments / month.calls) : 0);
                income.push(month.calls > 0 ? Math.round(100 * month.income / month.calls) : 0);
            });
            this.options.xAxis.data = periods;
            this.options.series[0].data = appointments;
            this.options.series[1].data = income;
        },
    }
}
</script>
