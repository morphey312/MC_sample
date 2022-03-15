<template>
    <page
        :title="__('Отчет - Оборот по специализации')"
        v-loading="loading"
        :element-loading-text="__('Генерация отчета')"
        type="flex">
        <section class="grey pb-0">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-select
                        :entity="filters"
                        :options="specializations"
                        :multiple="true"
                        property="specialization"
                        :label="__('Специализация')"
                    />
                </el-col>
                <el-col :span="6">
                    <form-row
                        name="dates"
                        :label="__('Дата')">
                        <div class="form-input-group">
                            <form-date
                                :entity="filters"
                                property="date_start"
                                :clearable="true"
                            />
                            <form-date
                                :entity="filters"
                                property="date_end"
                                :clearable="true"
                            />
                        </div>
                    </form-row>
                </el-col>
            </el-row>
        </section>
        <section class="grey-cap pt-0">
            <manage-table
                ref="table"
                :fields="fields"
                :repository="repository">
                <div
                    class="buttons"
                    slot="footer-top">
                    <el-button
                        @click="exportExcel">
                        {{ __('Экспорт в Excel') }}
                    </el-button>
                </div>
            </manage-table>
        </section>
    </page>
</template>
<script>
import DefaultReport from './Default.vue';
import DoctorSpecializationReportRepository from '@/repositories/reports/finance/doctor-specialization';
import SpecializationRepository from '@/repositories/specialization';
import reportGenerator from './generators/finance/doctor-specialization';
import ReportFilter from './Filter.vue';
import FileSaver from 'file-saver';
import CONSTANTS from '@/constants';

export default {
    extends: DefaultReport,
    components: {
        ReportFilter,
    },
    data() {
        return {
            loading: false,
            permission: 'doctor-specialization',
            fields: [
                {
                    name: 'name',
                    title: __('Список отчетов'),
                },
            ],
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('finance-reports.doctor-specialization-clinic')
            }),
            filters: {
                specialization: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
                is_deleted: false,
            },
            rows: [
                {
                    id: 1,
                    visible: this.$can('finance-reports.doctor-specialization') || this.$can('finance-reports.doctor-specialization-clinic'),
                    name: __('Оборот по специализации'),
                    fileName: __('Отчет - Оборот по специализации.xlsx'),
                    repository: new DoctorSpecializationReportRepository(),
                    generator: reportGenerator,
                },
            ].filter(i => i.visible === true),
        }
    },
    methods: {
        exportExcel() {
            if (this.filters.specialization.length == 0) {
                return this.$error(__('Выберите хотя бы одну клинику'));
            }

            if (_.isVoid(this.filters.date_start) || _.isVoid(this.filters.date_end)) {
                return this.$error(__('Выберите период'));
            }

            let reportRepo = new DoctorSpecializationReportRepository();
            let filters = _.onlyFilled(this.filters);

            this.loading = true;
            return reportRepo.fetch(filters).then((data) => {
                return reportGenerator(data, filters).then((book) => {
                    return book.xlsx.writeBuffer().then((buffer) => {
                        this.getFileName().then((fileName) => {
                            FileSaver.saveAs(new Blob([buffer]), fileName)
                        });
                    }).catch((err) => {
                        console.error(err);
                        this.$error(__('Не удалось сохранить отчет'));
                    });
                });
            }).finally(() => {
                this.loading = false;
            });
        },
        getFileName() {
            return this.getSpecializationNames().then(list => {
                return Promise.resolve(list.join('_') + ".xlsx");
            }) ;
        },
        getSpecializations() {
            return this.specializations.fetchList();
        },
        getSpecializationNames() {
            return this.getSpecializations().then(list => {
                let specializations = list.filter(item => this.filters.specialization.indexOf(item.id) !== -1)
                                        .map(item => item.short_name);
                return Promise.resolve(specializations);
            })
        },
    },
}
</script>
