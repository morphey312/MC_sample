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
                </el-col>
                <el-col :span="6">
                    <form-row
                        name="dates"
                        :label="__('Период')">
                        <div class="form-input-group">
                            <form-date
                                :entity="filters"
                                property="date_start" />
                            <form-date
                                :entity="filters"
                                property="date_end" />
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
        <section class="p-0" v-if="!loading && tableData.length != 0">
            <data-table
                :table-data="tableData"
                :clinics-to-display="clinicsToDisplay" >
                <div class="p-10" slot="buttons">
                    <el-button
                        :disabled="tableData.length === 0"
                        @click="exportExcel">
                        {{ __('Экспорт в Excel') }}
                    </el-button>
                </div>
            </data-table>
        </section>
    </div>
</template>
<script>
import ElasticReportMixin from '@/components/reports/interactive/doctor-income-plan/mixins/elastic';
import DataTable from './RefundsReportTable.vue';
import * as fileGenerator from './generator';
import FileSaver from 'file-saver';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ElasticReportMixin,
    ],
    components: {
        DataTable,
    },
    data() {
        return {
            filters: {
                clinic: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
            },
            tableData: [],
            clinicsToDisplay: [],
        }
    },
    methods: {
        getData() {
            let filters = this.prepareFilters();
            this.loading = true;
            this.getRefunds(filters)
                .then((result) => {
                    let map = new Map();
                    result.forEach((row) => {
                        let key = [
                            row.created_at,
                            row.clinic_id,
                            row.doctor_id,
                            (row.appointment_specialization_id || row.appointment_card_specialization_id),
                        ].join('-');
                        if (map.has(key)) {
                            map.get(key).amount += Number(row.payed_amount);
                        } else {
                            map.set(key, {
                                clinic: (_.findById(this.clinics, row.clinic_id) || {}).value,
                                specialization: row.appointment_specialization 
                                    || row.appointment_card_specialization,
                                doctor: row.doctor_name,
                                amount: Number(row.payed_amount),
                                date: row.created_at,
                            });
                        }
                    });
                    this.tableData = _.sortBy([
                        ...map.values()
                    ], [
                        'date', 
                        'clinic',
                        'doctor',
                    ]);
                })
                .catch((e) => {
                    console.error(e);
                    this.$error(__('Не удалось загрузить данные'));
                })
                .finally(() => {
                    this.loading = false;
                })
        },
        getRefunds(filters) {
            return this.elasticSearchClient.getHits(this.paymentIndex, {
                query: {
                    bool: {
                        filter: [
                            {
                                exists: { field : 'doctor_id'},
                            },
                            {
                                range: {
                                    created_at: {
                                        gte: filters.date_start,
                                        lte: filters.date_end,
                                    },
                                },
                            }, {
                                terms: { clinic_id: filters.clinic },
                            }, {
                                term: { is_deleted: false },
                            }, {
                                term: { is_deposit: false },
                            }, {
                                term: { is_prepayment: false },
                            }, {
                                term: { is_technical: false },
                            }, {
                                term: { type: CONSTANTS.PAYMENT.TYPES.EXPENSE },
                            }
                        ],
                    }
                },
            });
        },
        exportExcel() {
            let columns = [
                {name: __('Клиника')},
                {name: __('Специализация')},
                {name: __('ФИО')},
                {name: __('Сумма возвратов')},
                {name: __('Дата возврата')},
            ];
            let rows = this.tableData.map((row) => [
                row.clinic,
                row.specialization,
                row.doctor,
                row.amount,
                row.date,
            ]);
            fileGenerator.exportPlain(rows, columns).then((book) => {
                book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), __('Возвраты') + '.xlsx');
                }).catch((err) => {
                    console.error(err);
                    this.$error(__('Не удалось сохранить отчет'));
                });
            });
        },
    }
}
</script>
