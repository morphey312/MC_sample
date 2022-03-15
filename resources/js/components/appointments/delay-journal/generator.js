let total = 0;

const reportAddRows = (workbook, data, fields) => {
    let worksheet = workbook.worksheets[0];

    data.forEach((row) => {
        let outputRow = {};
        fields.forEach(field => {
            let name = field.name;
            let value = _.get(row, name);

            if (field.formatter != undefined) {
                value = field.formatter(value);
            }
            outputRow[name] = value; 
        });
        total += row.duration;
        worksheet.addRow(outputRow);
    });
}

const clearTotal = () => {
    total = 0;
}

const getTotal = () => {
    return total;
}

export {
    reportAddRows,
    clearTotal,
    getTotal,
}