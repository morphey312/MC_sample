import {boolFormat} from "@/services/format";

const reportAddRows = (workbook, data, fields) => {
    let boolValues = ['is_first', 'is_deleted', 'has_base_service', 'insurance_policy_id',
        'patient.has_service_debt', 'created_by_patient', 'discount_card_id', 'patient.is_patient'];
    let worksheet = workbook.worksheets[0];

    data.forEach((row) => {
        let outputRow = {};
        fields.forEach(field => {
            let name = field.name;
            let value = _.get(row, name);

            if (boolValues.indexOf(name) != -1) {
                value = boolFormat(value);
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
