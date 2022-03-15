import Excel from 'exceljs';

export default (data, type = 'table') => {
    if (type == 'table') {
        return getReportData(data);
    }
    if (type == 'export') {
        return getExportData(data);
    }
}
const getExportData = (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Пропущенные звонки'));

    worksheet.columns = [
        { header: __('Дата'), key: 'started_at' },
        { header: __('Кол-во входящих звонков'), key: 'countCalls' },
        { header: __('Кол-во пропущенных звонков'), key: 'countMissed' },
        { header: __('% пропущенных звонков'), key: 'percentMissed' },
    ];

    data.forEach(row => {

        worksheet.addRow({
            started_at: row.started_at,
            countCalls: row.countCalls,
            countMissed: row.countMissed,
            percentMissed: row.percentMissed,
        });
    });

    return workbook;
}
const getReportData = (data) => {
    let byDate = {};

    data.forEach((row) => {
        let rowDate;
        if (byDate[row.started_at] === undefined) {
            byDate[row.started_at] = {
                started_at: row.started_at,
                countCalls: 1,
                countMissed: row.status === 'abandoned' ? 1 : 0,
                percentMissed: 0,
            }
        } else {
            rowDate = byDate[row.started_at];
            rowDate.countCalls += 1;
            rowDate.countMissed += row.status === 'abandoned' ? 1 : 0;
            rowDate.percentMissed = Math.round(rowDate.countMissed * 10000 / rowDate.countCalls) / 100;
        }
    });
    return {byDate};
}
