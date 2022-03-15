import {boolFormat} from '@/services/format';
import CONSTANTS from '@/constants';

const reportAddRows = (workbook, data, fields) => {
    let worksheet = workbook.worksheets[0];

    data.forEach((row) => {
        let outputRow = {};
        fields.forEach(field => {
            let name = field.name;
            let value = _.get(row, name);
            
            if (name === 'type') {
                value = boolFormat(value === CONSTANTS.PAYMENT.TYPES.EXPENSE);
            } else if (name === 'payed_amount') {
                value = Number(value);
            } else if (field.formatter != undefined) {
                value = field.formatter(value);
            }
            outputRow[name] = value; 
        });
        worksheet.addRow(outputRow);
    });
}

export {
    reportAddRows,
}