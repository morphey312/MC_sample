import {boolFormat} from '@/services/format';

const reportAddRows = (workbook, data, fields) => {
    let boolValues = ['patient.mailing_analysis'];
    let numberValues = ['price.cost', 'discount', 'cost'];
    let worksheet = workbook.worksheets[0];

    data.forEach((row) => {
        let outputRow = {};
        fields.forEach(field => {
            let name = field.name;

            if (name === 'attachment_data') {
                return;
            }

            let value = _.get(row, name);

            if (boolValues.indexOf(name) !== -1) {
                value = boolFormat(value);
            } else if (numberValues.indexOf(name) !== -1) {
                value = value ? Number(value) : 0;
            } else if (field.formatter != undefined) {
                value = field.formatter(value);
            } else if (name === 'analysis_name') {
                value = _.get(row, 'analysis.name');
            }
            outputRow[name] = value;
        });
        worksheet.addRow(outputRow);
    });
}

export {
    reportAddRows,
}