import Excel from 'exceljs';
import {dateFormat} from '@/services/format';

const FONT_BOLD = {
    bold: true,
    size: 10,
    font: 'Calibri',
};
const WIDTH_WIDE = 25;
const WIDTH_MID = 13;
const WIDTH_THIN = 10;
const CALL_RESULT_ID = 40;
const ALIGNMENT = {
    vertical: 'middle',
    horizontal: 'center',
    wrapText: true,
};
const BORDER_LIGHT_STYLES = {style: 'thin', color: {argb: 'FF000000'}};
const BORDER_MEDIUM_STYLES = {style: 'medium', color: {argb: 'FF000000'}};

const countDebitors = (list) => {
    let success = 0;
    let fail = 0;

    list.forEach(row => {
        if (row.is_call) {
            if (row.result === CALL_RESULT_ID) {
                success++;
            } else {
                fail++;
            }
        } else {
            success++;
        }
    });
    return {success, fail};
}

const countRefused = (list) => {
    let success = 0;
    let fail = 0;
    list.forEach(row => {
        if (row.refused) {
            if (row.is_call) {
                if (row.result === CALL_RESULT_ID) {
                    success++;
                } else {
                    fail++;
                }
            } else {
                success++;
            }
        }
    });
    return {success, fail};
}

const countNonProfile = (list) => {
    let success = 0;
    let fail = 0;
    list.forEach(row => {
        if (row.is_non_profile) {
            if (row.is_call) {
                if (row.result === CALL_RESULT_ID) {
                    success++;
                } else {
                    fail++;
                }
            } else {
                success++;
            }
        }
    });
    return {success, fail};
}

const countOther = (list) => {
    let success = 0;
    let fail = 0;
    list.forEach(row => {
        if (!row.is_non_profile && !row.refused) {
            if (row.is_call) {
                if (row.result === CALL_RESULT_ID) {
                    success++;
                } else {
                    fail++;
                }
            } else {
                success++;
            }
        }
    });
    return {success, fail};
}

const sumPayments = (list) => {
    return list.reduce((sum, row) => {
        return sum + Number(row.amount);
    }, 0);
}

const getPeriod = (filters) => {
    return dateFormat(filters.date_start, 'DD.MM.YYYY') + ' - ' + dateFormat(filters.date_end, 'DD.MM.YYYY');
}

const seedQuantitiveSheet = (workbook, data) => {
    let worksheet = workbook.addWorksheet(__('КоличественныйДетальный'), {
        views: [
            {state: 'frozen', ySplit: 1}
        ]
    });
    worksheet.columns = [
        { header: __('ФИО'), key: 'employee', width: WIDTH_WIDE},
        { header: __('Клиника'), key: 'clinic', width: WIDTH_THIN},
        { header: __('№ карты'), key: 'card', width: WIDTH_THIN},
        { header: __('Специализация'), key: 'specialization', width: WIDTH_WIDE},
        { header: __('Должник'), key: 'debitor', width: WIDTH_THIN},
        { header: __('Невзявший'), key: 'refused', width: WIDTH_THIN},
        { header: __('Непроф'), key: 'non_profile', width: WIDTH_THIN},
        { header: __('Другое'), key: 'other', width: WIDTH_THIN},
        { header: __('Звонок успешный'), key: 'success', width: WIDTH_THIN},
    ];
    let titleRow = worksheet.getRow(1);
    titleRow.alignment = ALIGNMENT;
    titleRow.font = FONT_BOLD;
    titleRow.height = 20;

    data.forEach(item => {
        let row = {
            employee: item.operator_name,
            clinic: item.clinic_name,
            card: item.card_number,
            specialization: item.specialization_name,
        };

        if (item.is_call) {
            row.success = (CALL_RESULT_ID == item.result) ? 1 : 0;
        } else {
            row.success = 1;
        }

        if (item.is_debitor) {
            row.debitor = 1;
            row.refused = 0;
            row.non_profile = 0;
            row.other = 0;
        } else if (item.refused) {
            row.debitor = 0;
            row.refused = 1;
            row.non_profile = 0;
            row.other = 0;
        } else if (item.is_non_profile) {
            row.debitor = 0;
            row.refused = 0;
            row.non_profile = 1;
            row.other = 0;
        } else {
            row.debitor = 0;
            row.refused = 0;
            row.non_profile = 0;
            row.other = 1;
        }

        worksheet.addRow(row);
    });
}

const seedQuantitiveSummarySheet = (workbook, data, filters) => {
    let worksheet = workbook.addWorksheet(__('КоличествоПациентов'), {
        views: [
            {state: 'frozen', xSplit: 1}
        ]
    });

    let collectors = _.groupBy(data, 'operator_id');
    let specializations = _.groupBy(data, 'specialization_name');
    let collectorKeys = Object.keys(collectors);

    let columns = [
        { header: getPeriod(filters), key: 'specialization', width: WIDTH_WIDE},
    ];

    collectorKeys.forEach(id => {
        let header = collectors[id][0].operator_name;
        columns.push({header, key: 'debt_success_' + id, width: WIDTH_MID});
        columns.push({header, key: 'debt_fail_' + id, width: WIDTH_MID});
        columns.push({header, key: 'refused_success_' + id, width: WIDTH_MID});
        columns.push({header, key: 'refused_fail_' + id, width: WIDTH_MID});
        columns.push({header, key: 'non_profile_success_' + id, width: WIDTH_MID});
        columns.push({header, key: 'non_profile_fail_' + id, width: WIDTH_MID});
        columns.push({header, key: 'other_success_' + id, width: WIDTH_MID});
        columns.push({header, key: 'other_fail_' + id, width: WIDTH_MID});
    });

    worksheet.columns = columns;

    let collectorLetters = {};
    let columnGroup = {};
    let groupColumns = {};

    collectorKeys.forEach(id => {
        //Get operator columns letters
        if (!collectorLetters[id]) {
            collectorLetters[id] = {
                debt_success_: worksheet.getColumn('debt_success_' + id).letter,
                debt_fail_: worksheet.getColumn('debt_fail_' + id).letter,
                refused_success_: worksheet.getColumn('refused_success_' + id).letter,
                refused_fail_: worksheet.getColumn('refused_fail_' + id).letter,
                non_profile_success_: worksheet.getColumn('non_profile_success_' + id).letter,
                non_profile_fail_: worksheet.getColumn('non_profile_fail_' + id).letter,
                other_success_: worksheet.getColumn('other_success_' + id).letter,
                other_fail_: worksheet.getColumn('other_fail_' + id).letter,
            }
        }

        //Seed column group row
        columnGroup['debt_success_' + id] = __('Должники');
        columnGroup['debt_fail_' + id] = __('Должники');
        columnGroup['refused_success_' + id] = __('Невзявшие лечение');
        columnGroup['refused_fail_' + id] = __('Невзявшие лечение');
        columnGroup['non_profile_success_' + id] = __('Непрофильные');
        columnGroup['non_profile_fail_' + id] = __('Непрофильные');
        columnGroup['other_success_' + id] = __('прочие категории');
        columnGroup['other_fail_' + id] = __('прочие категории');

        //Seed group columns
        groupColumns['debt_success_' + id] = __('успешные');
        groupColumns['debt_fail_' + id] = __('безрезультативные');
        groupColumns['refused_success_' + id] = __('успешные');
        groupColumns['refused_fail_' + id] = __('безрезультативные');
        groupColumns['non_profile_success_' + id] = __('успешные');
        groupColumns['non_profile_fail_' + id] = __('безрезультативные');
        groupColumns['other_success_' + id] = __('успешные');
        groupColumns['other_fail_' + id] = __('безрезультативные');
    });

    worksheet.addRow(columnGroup);
    groupColumns = worksheet.addRow(groupColumns);
    groupColumns.height = 25;

    //Merge grouped cells
    worksheet.eachRow((row, rowNumber) => {
        if (rowNumber == 1) {
            collectorKeys.forEach(id => {
                worksheet.mergeCells(collectorLetters[id].debt_success_ + rowNumber + ':' + collectorLetters[id].other_fail_ + rowNumber);
            });
            row.font = FONT_BOLD;
        }

        if (rowNumber == 2) {
            collectorKeys.forEach(id => {
                worksheet.mergeCells(collectorLetters[id].debt_success_ + rowNumber + ':' + collectorLetters[id].debt_fail_ + rowNumber);
                worksheet.mergeCells(collectorLetters[id].refused_success_ + rowNumber + ':' + collectorLetters[id].refused_fail_ + rowNumber);
                worksheet.mergeCells(collectorLetters[id].non_profile_success_ + rowNumber + ':' + collectorLetters[id].non_profile_fail_ + rowNumber);
                worksheet.mergeCells(collectorLetters[id].other_success_ + rowNumber + ':' + collectorLetters[id].other_fail_ + rowNumber);
            });
        }

        if (rowNumber <= 3) {
            row.alignment = ALIGNMENT;
        }
    });

    //Seed data
    Object.keys(specializations).forEach(spec => {
        let row = {};
        row.specialization = spec;
        collectorKeys.forEach(id => {
            let operatorList = specializations[spec].filter(item => item.operator_id == id);
            let debitors = countDebitors(operatorList.filter(item => item.is_debitor));
            let notDebitors = operatorList. filter(item => !item.is_debitor);
            let refused = countRefused(notDebitors);
            let nonProfile = countNonProfile(notDebitors);
            let other = countOther(notDebitors);

            row['debt_success_' + id] = debitors.success;
            row['debt_fail_' + id] = debitors.fail;
            row['refused_success_' + id] = refused.success;
            row['refused_fail_' + id] = refused.fail;
            row['non_profile_success_' + id] = nonProfile.success;
            row['non_profile_fail_' + id] = nonProfile.fail;
            row['other_success_' + id] = other.success;
            row['other_fail_' + id] = other.fail;
        });
        worksheet.addRow(row);
    });

    worksheet.eachColumnKey((column, index) => {
        if (column.key.indexOf('other_fail_') !== -1) {
            column.border = {
                right: BORDER_MEDIUM_STYLES,
            };
        } else {
            column.border = {
                right: BORDER_LIGHT_STYLES,
            };
        }
    });
}
const seedPaymentsSheet = (workbook, data) => {
    let worksheet = workbook.addWorksheet(__('КачественныйДетальный'), {
        views: [
            {state: 'frozen', ySplit: 1}
        ]
    });

    worksheet.columns = [
        { header: __('ФИО'), key: 'employee', width: WIDTH_WIDE},
        { header: __('Клиника'), key: 'clinic', width: WIDTH_THIN},
        { header: __('№ карты'), key: 'card', width: WIDTH_THIN},
        { header: __('Специализация'), key: 'specialization', width: WIDTH_WIDE},
        { header: __('Платеж'), key: 'payment', width: WIDTH_THIN},
        { header: __('Должник'), key: 'debitor', width: WIDTH_THIN},
        { header: __('Невзявший'), key: 'refused', width: WIDTH_THIN},
        { header: __('Другое'), key: 'other', width: WIDTH_THIN},
    ];

    let titleRow = worksheet.getRow(1);
    titleRow.alignment = ALIGNMENT;
    titleRow.height = 20;
    titleRow.font = FONT_BOLD;

    data.forEach(item => {
        let row = {
            employee: item.operator_name,
            clinic: item.clinic_name,
            card: item.card_number,
            specialization: item.specialization_name,
            payment: Number(item.amount),
        };
        if (row.specialization === null){
            row.specialization = 'Вне записи'
        }
        if (item.is_debitor) {
            row.debitor = 1;
            row.refused = 0;
            row.other = 0;
        } else if (item.refused) {
            row.debitor = 0;
            row.refused = 1;
            row.other = 0;
        } else if (item.is_non_profile) {
            row.debitor = 0;
            row.refused = 0;
            row.other = 0;
        } else {
            row.debitor = 0;
            row.refused = 0;
            row.other = 1;
        }

        worksheet.addRow(row);
    });
}

const seedPaymentsSummarySheet = (workbook, data, filters) => {
    let worksheet = workbook.addWorksheet(__('СуммыВозврата'), {
        views: [
            {state: 'frozen', xSplit: 1}
        ]
    });

    let collectors = _.groupBy(data, 'operator_id');
    let specializations = _.groupBy(data, 'specialization_name');
    let collectorKeys = Object.keys(collectors);

    let columns = [
        { header: getPeriod(filters), key: 'specialization', width: WIDTH_WIDE},
    ];

    collectorKeys.forEach(id => {
        let header = collectors[id][0].operator_name;
        columns.push({header, key: 'debt_patients_' + id, width: WIDTH_MID});
        columns.push({header, key: 'debt_payed_' + id, width: WIDTH_MID});
        columns.push({header, key: 'refused_patients_' + id, width: WIDTH_MID});
        columns.push({header, key: 'refused_payed_' + id, width: WIDTH_MID});
        columns.push({header, key: 'other_patients_' + id, width: WIDTH_MID});
        columns.push({header, key: 'other_payed_' + id, width: WIDTH_MID});
    });

    worksheet.columns = columns;

    let collectorLetters = {};
    let columnGroup = {};
    let groupColumns = {};

    collectorKeys.forEach(id => {
        //Get operator columns letters
        if (!collectorLetters[id]) {
            collectorLetters[id] = {
                debt_patients_: worksheet.getColumn('debt_patients_' + id).letter,
                debt_payed_: worksheet.getColumn('debt_payed_' + id).letter,
                refused_patients_: worksheet.getColumn('refused_patients_' + id).letter,
                refused_payed_: worksheet.getColumn('refused_payed_' + id).letter,
                other_patients_: worksheet.getColumn('other_patients_' + id).letter,
                other_payed_: worksheet.getColumn('other_payed_' + id).letter,
            }
        }

        //Seed column group row
        columnGroup['debt_patients_' + id] = __('Должники');
        columnGroup['debt_payed_' + id] = __('Должники');
        columnGroup['refused_patients_' + id] = __('Невзявшие лечение');
        columnGroup['refused_payed_' + id] = __('Невзявшие лечение');
        columnGroup['other_patients_' + id] = __('прочие категории');
        columnGroup['other_payed_' + id] = __('прочие категории');

        //Seed group columns
        groupColumns['debt_patients_' + id] = __('количество пациентов');
        groupColumns['debt_payed_' + id] = __('сумма');
        groupColumns['refused_patients_' + id] = __('количество пациентов');
        groupColumns['refused_payed_' + id] = __('сумма');
        groupColumns['other_patients_' + id] = __('количество пациентов');
        groupColumns['other_payed_' + id] = __('сумма');
    });

    worksheet.addRow(columnGroup);
    groupColumns = worksheet.addRow(groupColumns);
    groupColumns.height = 25;

    //Merge grouped cells
    worksheet.eachRow((row, rowNumber) => {
        if (rowNumber == 1) {
            collectorKeys.forEach(id => {
                worksheet.mergeCells(collectorLetters[id].debt_patients_ + rowNumber + ':' + collectorLetters[id].other_payed_ + rowNumber);
            });
            row.font = FONT_BOLD;
        }

        if (rowNumber == 2) {
            collectorKeys.forEach(id => {
                worksheet.mergeCells(collectorLetters[id].debt_patients_ + rowNumber + ':' + collectorLetters[id].debt_payed_ + rowNumber);
                worksheet.mergeCells(collectorLetters[id].refused_patients_ + rowNumber + ':' + collectorLetters[id].refused_payed_ + rowNumber);
                worksheet.mergeCells(collectorLetters[id].other_patients_ + rowNumber + ':' + collectorLetters[id].other_payed_ + rowNumber);
            });
        }

        if (rowNumber <= 3) {
            row.alignment = ALIGNMENT;
        }
    });

    //Seed data
    Object.keys(specializations).forEach(spec => {
        let row = {};
        row.specialization = spec;
        if (row.specialization === 'null'){
            row.specialization = 'Вне записи'
        }
        collectorKeys.forEach(id => {
            let operatorList = specializations[spec].filter(item => item.operator_id == id);
            let debitors = operatorList.filter(item => item.is_debitor);
            let notDebitors = operatorList.filter(item => !item.is_debitor && item.refused);
            let others = operatorList.filter(item => !item.is_debitor && !item.refused);

            row['debt_patients_' + id] = debitors.length;
            row['debt_payed_' + id] = sumPayments(debitors);
            row['refused_patients_' + id] = notDebitors.length;
            row['refused_payed_' + id] = sumPayments(notDebitors);
            row['other_patients_' + id] = others.length;
            row['other_payed_' + id] = sumPayments(others);
        });
        worksheet.addRow(row);
    });

    worksheet.eachColumnKey((column, index) => {
        if (column.key.indexOf('other_payed_') !== -1) {
            column.border = {
                right: BORDER_MEDIUM_STYLES,
            };
        } else {
            column.border = {
                right: BORDER_LIGHT_STYLES,
            };
        }
    });
}

export default (data, filters) => {
    let workbook = new Excel.Workbook();
    let collectorsData = _.sortBy(data.collectors, ['operator_name', 'specialization_name']);
    data.payments = _.orderBy(data.payments, 'date', 'desc');
    let paymentsData = _.sortBy(_.uniqBy(data.payments, 'id'), ['operator_name', 'specialization_name']);

    //Seed calls and appointments sheet
    seedQuantitiveSheet(workbook, collectorsData);
    // //Seed calls appointments summary sheet
    seedQuantitiveSummarySheet(workbook, collectorsData, filters);
    //Seed payments sheet
    seedPaymentsSheet(workbook, paymentsData);
    //Seed payments summary sheet
    seedPaymentsSummarySheet(workbook, paymentsData, filters);

    return Promise.resolve(workbook);
}
