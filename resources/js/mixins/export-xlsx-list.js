import Excel from 'exceljs';
import FileSaver from 'file-saver';

export default {
    methods: {
        exportExcel(title = null, fields = null, afterLoad = null) {
            this.loading = true;
            let fileName = this.getFileName(title);
            let promise = Promise.resolve();
            let table = this.getManageTable();
            fields = fields ? fields : table.normalizedFields;
            let filters = _.onlyFilled(this.filters);
            let scopes = table.combinedScopes;
            let sort = table.sortOrder;
            let totalPages = table.$refs.pagination.last_page;
            let limit = table.$refs.pagination.pageSize;
            let book = this.getReportWorkBook(fields);

            let getDataRows = async () => {
                for (let page = 1; page <= totalPages; page++) {
                    let response = await this.reportRepository.fetch(filters, sort, scopes, page, limit);
                    let rows = response.rows;
                    this.fileGenerator.reportAddRows(book, rows, fields, filters);
                }

                if (afterLoad && (typeof afterLoad === 'function')) {
                    afterLoad(book, fields);
                }

                return book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), `${fileName}.xlsx`);
                    this.loading = false;
                    return promise;
                }).catch((err) => {
                    console.error(err);
                    this.$error(__('Не удалось сохранить отчет'));
                });
            }
            getDataRows();
            return promise;
        },
        getReportWorkBook(fields = [], columnOptions = {}) {
            let workbook = new Excel.Workbook();
            let worksheet = workbook.addWorksheet(__('Данные'));

            worksheet.columns = fields.filter(field => {
                return field.name != '__checkbox';
            }).map(field => {
                return {
                    header: field.title,
                    key: field.name,
                    width: Number.isInteger(field.width) ? field.width : 15,
                    ...columnOptions,
                };
            });

            worksheet.views = [
                {state: 'frozen', ySplit: 1}
            ];

            worksheet.getRow(1).font = {
                bold: true,
                size: 10,
            };
            return workbook;
        },
        getFileName(title) {
            return typeof title === 'string' ? title : this.getDocumentTitle();
        },
        getDocumentTitle() {
            let parts = window.document.title.split('::');
            let partsCount = parts.length;
            if (partsCount != 0) {
                return parts[partsCount - 1].replace(/\s/g, '');
            }
            return __('Отчет');
        },
    }
}
