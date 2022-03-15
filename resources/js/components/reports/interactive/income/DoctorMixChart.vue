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
                    <form-select
                        :entity="filters"
                        :options="specializations"
                        :multiple="true"
                        :clearable="true"
                        property="specialization"
                        :label="__('Специализация записи')" />
                </el-col>
                <el-col :span="6">
                    <form-select
                        ref="doctorOptions"
                        :entity="filters"
                        :options="doctors"
                        :clearable="true"
                        :filterable="true"
                        :multiple="true"
                        property="doctor"
                        :label="__('Врач')" />
                    <form-select
                        :entity="filters"
                        options="currency"
                        property="currency"
                        :label="__('Валюта')" />
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
                    @click="getData()"
                    type="primary">
                    {{ __('Показать') }}
                </el-button>
            </div>
        </section>
        <section class="pt-0">
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
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

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
            filters:  {
                clinic: [],
                specialization: [],
                doctor: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                currency: CONSTANTS.CURRENCY.UAH,
            },
            doctors: new EmployeeRepository({
                filters: this.getDoctorFilters(),
            }),
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
                    data: [__('Первичные'), __('Повторные'), __('Оборот, грн')],
                    top: 30,
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
                                interval: 0,
                                color: '#000000',
                                fontSize: 9
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
                            formatter: '{value}' + __(', грн')
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
                        name: __('Оборот, грн'),
                        type: 'line',
                        yAxisIndex: 1,
                        data: []
                    }
                ]
            }
        }
    },
    watch: {
        ['filters.clinic']() {
            this.doctors.setFilters(this.getDoctorFilters());
        },
        ['filters.specialization']() {
            this.doctors.setFilters(this.getDoctorFilters());
        },
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
        getDoctorFilters() {
            return _.onlyFilled({
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                ...(this.filters ? {
                    clinic: this.filters.clinic,
                    specialization: this.filters.specialization,
                } : {}),
            });
        },
        getData(updates) {
            if (this.filterIsEmpty()) {
                return this.$error(__('Выберите фильтры для поиска'));
            }
            let filters = this.prepareFilters();
            this.fetchReportData(filters);
        },
        fetchReportData(filters) {
            this.loading = true;
            this.getPayments(filters).then(payments => {
                this.getAppointments(filters).then(appointments => {
                    this.prepareData(payments.aggr_group, appointments.aggr_group).then(() => {
                        this.loading = false;
                    });
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
        getPayments(filters) {
            return this.elasticSearchClient.getAggregations(this.paymentIndex, {
                size: filters.clinic.length,
                query: {
                    bool: {
                        filter: this.getRequestPaymentFilter(filters)
                    }
                },
                aggs: {
                    aggr_group: {
                        terms: {
                            size: this.getDoctorSize(),
                            field: "doctor_id"
                        },
                        aggs: {
                            payed_amount: {
                                sum: {
                                    script: {
                                        lang: "painless",
                                        source: this.getPaymentAggrScript(),
                                    }
                                }
                            }
                        }
                    }
                }
            })
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        getAppointments(filters) {
            return this.elasticSearchClient.getAggregations(this.appointmentIndex, {
                size: 0,
                query: {
                    bool: {
                        filter: this.getRequestAppointmentFilter(filters)
                    }
                },
                aggs: {
                    aggr_group: {
                        terms: {
                            size: this.getDoctorSize(),
                            field: "doctor_id"
                        },
                        aggs: {
                            is_first: {
                                sum: {
                                    field: 'is_first'
                                }
                            },
                            repeated: {
                                sum: {
                                    script: {
                                        lang: "painless",
                                        source: this.getRepeatedAggrScript(),
                                    }
                                }
                            }
                        }
                    }
                }
            })
            .then(response => {
                return Promise.resolve(response);
            }).catch(e => {
                console.error(e);
                this.$error();
            });
        },
        prepareData(payments, appointments) {
            return this.doctors.fetchList().then(employees => {
                let chartKeys = _.uniqBy([...payments, ...appointments]
                    .map(row => {
                        let doctor = employees.find(employee => employee.id === row.key);
                        if (doctor) {
                            return {
                                id: row.key,
                                name: doctor.value
                            };
                        }
                        return null;
                    })
                    .filter(row => row != null), 'id');

                chartKeys = _.orderBy(chartKeys, (row) => row.name);
                this.options.xAxis[0].data = this.mapChartLabels(chartKeys);
                this.mapChartPayments(payments, chartKeys);
                this.mapChartAppointments(appointments, chartKeys);
                return Promise.resolve();
            })
        },
        mapChartLabels(chartKeys) {
            return chartKeys.map((row, index) => {
                let parts = row.name.split(' ');
                let label = parts[0] + (parts[1] ? (' ' + parts[1][0]) : '') + (parts[2] ? ('' + parts[2][0]) : '');
                return this.getAxisLabel(index, label);
            });
        },
        mapChartPayments(payments, chartKeys) {
            let results = [];
            chartKeys.forEach(row => {
                let doctorIndex = payments.findIndex(item => item.key == row.id);
                if (doctorIndex !== -1) {
                    results.push(Number(Math.ceil(payments[doctorIndex].payed_amount.value)));
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
            chartKeys.forEach(row => {
                let doctorIndex = appointments.findIndex(item => item.key == row.id);
                if (doctorIndex != -1) {
                    isFirst.push(appointments[doctorIndex].is_first.value);
                    repeated.push(appointments[doctorIndex].repeated.value);
                } else {
                    isFirst.push(0);
                    repeated.push(0);
                }
            });
            this.options.series[0].data = isFirst;
            this.options.series[1].data = repeated;
            this.options.yAxis[0].max = _.max([...isFirst, ...repeated]);
        },
        getRequestPaymentFilter(filters) {
            let filter = this.getBasePaymentFilter(filters);
            if (filters.specialization && filters.specialization.length != 0) {
                filter.push({terms: {
                        appointment_specialization_id: filters.specialization
                    }
                });
            }
            if (filters.doctor && filters.doctor.length != 0) {
                filter.push({
                    terms: {
                        doctor_id: filters.doctor
                    }
                });
            }
            return filter;
        },
        getRequestAppointmentFilter(filters) {
            let filter = this.getBaseAppointmentFilter(filters);
            if (filters.specialization && filters.specialization.length != 0) {
                filter.push({terms: {
                        specialization_id: filters.specialization
                    }
                });
            }
            if (filters.doctor && filters.doctor.length != 0) {
                filter.push({
                    terms: {
                        doctor_id: filters.doctor
                    }
                });
            }
            filter.push({
                term: {
                    doctor_type: CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE
                }
            });
            return filter;
        },
        getDoctorSize() {
            if (this.filters.doctor && this.filters.doctor.length != 0) {
                return this.filters.doctor.length;
            }
            if (this.$refs.doctorOptions && this.$refs.doctorOptions.optionsToDisplay) {
                return this.$refs.doctorOptions.optionsToDisplay.length;
            }
            return 1000;
        },
    },
}
</script>
