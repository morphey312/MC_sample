import Excel from 'exceljs';

const exportPlain = (rows, columns) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Данные'));
    
    worksheet.addTable({
        name: __('Данные'),
        ref: 'A1',
        headerRow: true,
        totalsRow: false,
        style: {
            theme: null,
        },
        columns,
        rows,
    });
    return Promise.resolve(workbook);
}

export {
    exportPlain
}