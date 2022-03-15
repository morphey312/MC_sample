import {boolFormat} from '@/services/format';

const reportAddRows = (workbook, data, fields) => {
    let boolValues = [
        'medicine.is_free', 
        'medicine'
    ];
    let numberValues = [
        'medicine.issued_quantity', 
        'medicine.quantity',
        'medicine.self_cost', 
        'medicine.cost',
        'medicine.base_cost',
        'issued'
    ];
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
                value = boolFormat(!value);
            } else if (numberValues.indexOf(name) !== -1) {
                value = value ? Number(value) : 0;
            } else if (field.formatter != undefined) {
                value = field.formatter(value);
            }

            if (name === 'to_issue') {
                value = getToIssue(row);
            }

            outputRow[name] = value;
        });
        worksheet.addRow(outputRow);
    });
}

function getToIssue(row) {
    return Number((row.medicine.quantity - row.medicine.issued_quantity).toFixed(3));
}

export {
    reportAddRows,
}
