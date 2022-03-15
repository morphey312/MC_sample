import Excel from 'exceljs';
import FileSaver from 'file-saver';

export default {
    methods: {
        exportExcel(title = null, fields = null) {
            this.loading = true;
            let fileName = this.getFileName(title);
            let promise = Promise.resolve();
            let book = this.getReportWorkBook(fields);

            let getDataRows = async () => {
                let rows = []
                let response = await this.model.fetch(['act_prices']).then((response) => {
                    rows = response.response.data.act_prices
                });
                this.fileGenerator.addRows(book, rows);

                return book.xlsx.writeBuffer().then((buffer) => {
                    FileSaver.saveAs(new Blob([buffer]), `${fileName}.xlsx`);
                    this.loading = false;
                    return promise;
                }).catch((err) => {
                    console.error(err);
                    this.$error(__('Не удалось сохранить акт'));
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
