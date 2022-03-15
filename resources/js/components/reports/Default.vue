<template>
    <page
        :title="__('Отчеты для') + ' ' + name "
        v-loading="queueSize !== 0"
        :element-loading-text="__('Генерация отчетов...')"
        type="flex">
        <report-filter
            ref="filter"
            :filters="filters"
            :permission="permission" />
        <section class="grey-cap pt-0">
            <manage-table
                ref="table"
                :fields="fields"
                :repository="repository"
                :show-table-settings="false"
                @selection-changed="selectionChanged">
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
const Excel = require('exceljs');

import CONSTANT from '@/constants';
import ReportFilter from '@/components/reports/Filter.vue';
import ProxyRepository from '@/repositories/proxy-repository';
import FileSaver from 'file-saver';

export default {
    components: {
        ReportFilter,
    },
    data(){
        return {
            rows: [],
            name: 'default',
            permission: null,
            filters: {
                clinic: [],
                date_start: this.$moment().format('YYYY-MM-DD'),
                date_end: this.$moment().format('YYYY-MM-DD'),
            },
            excel: new Excel.Workbook(),
            hasSelection: false,
            queueSize: 0,
            fields: [
                {
                    name: '__checkbox',
                    width: '22px',
                },
                {
                    name: 'name',
                    title: __('Список отчетов'),
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: this.rows
            })),
        };
    },
    methods: {
        exportExcel() {
            let selection = this.getSelectedRows();
            if (selection.length !== 0) {
                let promise = Promise.resolve();
                selection.forEach((report) => {
                    this.queueSize++;
                    promise.then(() => {
                        let filters = _.onlyFilled(this.filters);
                        if (this.$isAccessLimited(this.permission) && filters.clinic.length === 0) {
                            filters.clinic = this.$store.state.user.clinics;
                        }

                        if (report.all_clinics == true) {
                            return report.repository.fetch(filters).then((data) => {
                                return report.generator(data, filters).then((book) => {
                                    return book.xlsx.writeBuffer().then((buffer) => {
                                        FileSaver.saveAs(new Blob([buffer]), report.fileName)
                                    }).catch((err) => {
                                        console.error(err);
                                        this.$error(__('Не удалось сохранить отчет'));
                                    });
                                });
                            }).finally(() => {
                                this.queueSize--;
                            });
                        } else {
                            let promise = Promise.resolve();
                            let data = [];

                            let getDataRows = async () => {
                                let clinicSize = filters.clinic.length;
                                for (let i = 0; i < clinicSize; i++) {
                                    let response = await  report.repository.fetch({...filters, clinic: [filters.clinic[i]]});
                                    data = this.appendResponse(data, response);
                                }
                                return report.generator(data, filters).then((book) => {
                                    return book.xlsx.writeBuffer().then((buffer) => {
                                        FileSaver.saveAs(new Blob([buffer]), report.fileName)
                                        return promise;
                                    }).catch((err) => {
                                        console.error(err);
                                        this.$error(__('Не удалось сохранить отчет'));
                                    });
                                }).finally(() => {
                                    this.queueSize--;
                                });
                            }
                            getDataRows();
                            return promise;
                        }
                    });
                });
            }
        },
        appendResponse(data, response) {
            if (Array.isArray(response)) {
                return [...data, ...response];
            }

            Object.keys(response).forEach(key => {
                if (data[key] !== undefined) {
                    data[key] = [...data[key], ...response[key]];
                } else {
                    data[key] = [...response[key]];
                }
            });
            return data;
        },
        selectionChanged() {
            this.hasSelection = this.getVuetable().selectedTo.length !== 0;
        },
        getVuetable() {
            return this.$refs.table.$refs.vuetable;
        },
        getSelection() {
            return this.getVuetable().selectedTo;
        },
        getSelectedRows() {
            let selection = this.getSelection();
            return this.rows.filter((row) => selection.indexOf(row.id) !== -1);
        },
    },
};
</script>