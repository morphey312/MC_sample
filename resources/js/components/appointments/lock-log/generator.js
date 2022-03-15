
const reportAddRows = (workbook, data, fields) => {
    let worksheet = workbook.worksheets[0];

    data.forEach((row) => {
        let outputRow = {};
        fields.forEach(field => {
            let name = field.name;
            let value = _.get(row, name);
            if (field.formatter != undefined) {
                if(name == 'updated_at' && row.status == 1){
                    value = '';
                }else{
                    value = field.formatter(value);
                }
            }
            outputRow[name] = value;
        });
        worksheet.addRow(outputRow);
    });
}

export {
    reportAddRows,
}
