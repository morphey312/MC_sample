<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :button-submit-text="__('Експорт')" 
        @changed="changed"
        v-loading="loading"
        :element-loading-text="__('Генерация отчета...')"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="price_agreement_act_type"
                    property="type"
                    :label="__('Тип тарифа')" />
                <form-select
                    :entity="filter"
                    :options="laboratories"
                    :disabled="disabledLaboratory"
                    :clearable="true"
                    :multiple="true"
                    property="laboratory"
                    :label="__('Лаборатория')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')" />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    property="specialization"
                    :disabled="disabledSpecialization"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    :label="__('Специализация')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="price_start_at.from"
                            :clearable="true" />
                        <form-date
                            :entity="filter"
                            property="price_start_at.to"
                            :clearable="true" />
                    </div>
                </form-row>
                 <form-row
                    name="for_has_debt"
                    label="&nbsp;">
                    <form-checkbox
                        :entity="filter"
                        property="same_name"
                        :label="__('Название услуги = названию для чека')" />
                </form-row>
            </el-col>
            <el-col :span="6">
                  <form-row
                    name="for_has_debt"
                    label="&nbsp;">
                    <form-checkbox
                        :entity="filter"
                        property="empty_prices"
                        :label="__('Нулевые тарифы')" />
                </form-row>
                 
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import ServiceRepository from '@/repositories/service';
import AnalysisRepository from '@/repositories/analysis';
import CONSTANTS from '@/constants';
import * as analysisGenerator from './generators/analysis';
import * as servicesGenerator from './generators/services';
import AnalysesMixin from '../mixins/analyses';
import ServiceMixin from '../mixins/service';
import SpecializationRepository from '@/repositories/specialization';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import Excel from 'exceljs';
import FileSaver from 'file-saver';

export default {
    mixins: [
        FilterMixin,
        ServiceMixin,
        AnalysesMixin,
        ExportXLSXMixin
    ],
    data() {
        return {
            specializations:  new SpecializationRepository({
                accessLimit: this.$isAccessLimited('price-agreement-acts'),
            }),
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('price-agreement-acts')
            }),
            laboratories: new LaboratoryRepository(),
            reportRepository: null,
            servicesFields: [
                {title: __('НАЗВАНИЕ УСЛУГИ'), name: 'service_name'},
                {title: __('ЦЕНА'), name: 'price', width: 10},
                {title: __('ДАТА НАЧАЛА ДЕЙСТВИЯ ТАРИФА'), name: 'date_from', width: 10},
                {title: __('ДАТА ИЗМЕНЕНИЯ'), name: 'date', width: 15}
            ],
            analysesFields: [
                {title: __('НАЗВАНИЕ АНАЛИЗА'), name: 'analysis_name'},
                {title: __('ЦЕНА'), name: 'price', width: 10},
                {title: __('ДАТА НАЧАЛА ДЕЙСТВИЯ ТАРИФА'), name: 'date_from', width: 15},
                {title: __('ДАТА ИЗМЕНЕНИЯ'), name: 'date', width: 15},
                {title: __('КОД ЛАБОРАТОРИИ'), name: 'laboratory_code', width: 35},
                {title: __('КОД КЛИНИКИ'), name: 'code', width: 35},
                {title: __('КОЛ-ВО ДНЕЙ ДЛЯ ВЫПОЛНЕНИЯ АНАЛИЗА'), name: 'duration', width: 10},
            ],
            loading: false,
        };
    },
    computed: {
        disabledLaboratory() {
            if (this.filter.type && this.filter.type === CONSTANTS.PRICE_AGREEMENT_ACT.TYPE.ANALYSIS) {
                return false;
            }
            return true;
        },
         disabledSpecialization() {
            if (this.filter.type && this.filter.type === CONSTANTS.PRICE_AGREEMENT_ACT.TYPE.SERVICES) {
                return false;
            }
            return true;
        }
    },
     watch: {
        ['filter.clinic'](value) {
            this.specializations.setFilters({
                clinic: value,
            }, true);
            
            this.laboratories.setFilters({
                clinics: value,
            }, true);
        },
    },
    methods: {
        changed(filters) {
            this.filters = filters;
            if (_.isVoid(this.filter.type)) {
                this.$warning(__('Укажите тип тарифа!'));
                return;
            }
            if (_.isVoid(this.filter.price_start_at.from) || 
                _.isVoid(this.filter.price_start_at.to)) {
                this.$warning(__('Укажите период изменений'));
                return;
            }
            this.reportRepository = this.filter.type === CONSTANTS.PRICE_AGREEMENT_ACT.TYPE.ANALYSIS? new AnalysisRepository() : new ServiceRepository();
            this.fileGenerator = this.filter.type === CONSTANTS.PRICE_AGREEMENT_ACT.TYPE.ANALYSIS ? analysisGenerator : servicesGenerator;
            let exportFields = this.filter.type === CONSTANTS.PRICE_AGREEMENT_ACT.TYPE.ANALYSIS ? this.analysesFields : this.servicesFields;
            this.loading = true;
            this.exportExcel(__('Измененные прайсы'), exportFields);
         
        },
     
        exportExcel(title = null, fields = null) {
            let fileName = title;
            let promise = Promise.resolve();
            let book = new Excel.Workbook();

            let getDataRows = async () => {
                let rows = [];
                let response = await this.reportRepository.fetchPriceList(this.filter, null, null, 1, 1000).then((response) => {
                    rows = response.rows;
                });
                if (rows.length === 0) {
                    this.$info(__('За этот период изменений не было'));
                    this.loading = false;
                    return;
                }
                if (this.filter.type === CONSTANTS.PRICE_AGREEMENT_ACT.TYPE.SERVICES) {
                    let sheets = _.groupBy(rows, 'specialization_short_name');
                    for (let specialization in sheets) {
                        this.getReportWorkBook(book, fields, specialization);
                        this.fileGenerator.addRows(book, sheets[specialization], specialization);
                    }
                } else {
                     this.getReportWorkBook(book, fields, __('Анализы'));
                      this.fileGenerator.addRows(book, rows);
                }
           
                return book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), `${fileName}.xlsx`);
                    this.loading = false;
                    return promise;
                }).catch((err) => {
                    console.error(err);
                    this.$error(__('Не удалось сохранить файл'));
                });
            }
            getDataRows();
            return promise;
        },
        getReportWorkBook(workbook, fields = [], sheetName) {
            let worksheet = workbook.addWorksheet(sheetName);
            worksheet.columns = fields.map(field => {
                return {
                    header: field.title,
                    key: field.name,
                    width: Number.isInteger(field.width) ? field.width : 15,
                };
            });

            worksheet.views = [
                {state: 'frozen', ySplit: 1}
            ];

            worksheet.getRow(1).font = {
                bold: true,
                size: 10,
            };
        },
        initFilter(fromState = {}) {
            this.filter = {
                clinic: null,
                price_start_at: {
                    from: null,
                    to: null,
                },
                specialization: [],
                type: null,
                laboratory: false,
                ...fromState,
            };
        },
    },
};

</script>
