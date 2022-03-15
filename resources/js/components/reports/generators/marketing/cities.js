import Excel from 'exceljs';

function toColumnName(number) {
    var colName = '',
        dividend = Math.floor(Math.abs(number)),
        rest;

    while (dividend > 0) {
        rest = (dividend - 1) % 26;
        colName = String.fromCharCode(65 + rest) + colName;
        dividend = parseInt((dividend - rest) / 26);
    }
    return colName;
}

export default (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Данные'), {
        views: [
            {state: 'frozen', xSplit: 1, ySplit: 2}
        ]
    });

    let byLocation = {};
    let specializations = [];

    data.forEach((row) => {
        let location;
        let spec;

        if (byLocation[row.location] === undefined) {
            location = byLocation[row.location] = {
                location: row.location,
                calls: 0,
                appointments: 0,
                income: 0,
                treatments: 0,
                bySpec: {},
            };
        } else {
            location = byLocation[row.location];
        }

        if (location.bySpec[row.specialization_id] === undefined) {
            spec = location.bySpec[row.specialization_id] = {
                id: row.specialization_id,
                name: row.specialization,
                calls: 0,
                appointments: 0,
                income: 0,
                treatments: 0,
            };
        } else {
            spec = location.bySpec[row.specialization_id];
        }

        if (specializations[row.specialization_id] === undefined) {
            specializations[row.specialization_id] = {name: row.specialization, key: row.specialization_id};
        }

        if (row.is_call === 1) {
            location.calls++;
            spec.calls++;
        }
        if (row.is_appointment === 1) {
            location.appointments++;
            spec.appointments++;
        }
        if (row.is_income === 1) {
            location.income++;
            spec.income++;
        }
        if (row.is_treatment === 1) {
            location.treatments++;
            spec.treatments++;
        }
    });


    let tempColumns = [
        {
            header: '', key: 'city_village', width: 30, height: 15,
            style: {alignment: {vertical: 'middle', horizontal: 'center'}},
            border: {
                top: {style: 'double', color: {argb: 'FF000000'}},
                left: {style: 'double', color: {argb: 'FF000000'}},
                bottom: {style: 'double', color: {argb: 'FF000000'}},
                right: {style: 'double', color: {argb: 'FF000000'}}
            },
        }
    ];

    specializations.forEach((item, index) => {
        tempColumns.push({
            header: item.name, key: 'spec_' + item.key + '_calls',
            style: {alignment: {vertical: 'middle', horizontal: 'center'}},
            border: {
                top: {style: 'double', color: {argb: 'FF00FF00'}},
                left: {style: 'double', color: {argb: 'FF00FF00'}},
                bottom: {style: 'double', color: {argb: 'FF00FF00'}},
                right: {style: 'double', color: {argb: 'FF00FF00'}}
            },
        });
        tempColumns.push({header: '', key: 'spec_' + item.key + '_signed_up'});
        tempColumns.push({header: '', key: 'spec_' + item.key + '_showed_up'});
        tempColumns.push({header: '', key: 'spec_' + item.key + '_treatments'});
    });


    tempColumns.push({
        header: __('Итого'), key: 'overall_calls', style: {alignment: {vertical: 'middle', horizontal: 'center'}},
    });
    tempColumns.push({header: '', key: 'overall_signed_up'});
    tempColumns.push({header: '', key: 'overall_showed_up'});
    tempColumns.push({header: '', key: 'overall_treatments'});

    worksheet.columns = tempColumns;


    //Это я мержу колонки для блока ИТОГО
    let i = 2;
    specializations.forEach((item, index) => {
        worksheet.mergeCells(toColumnName(i) + '1' + ':' + toColumnName(i + 3) + '1');
        i += 4;
    });

    worksheet.mergeCells(toColumnName(i) + '1' + ':' + toColumnName(i + 3) + '1'); // это для последней колонки итого

    let tempSpecAttrRow = {};

    specializations.forEach((item, index) => {
        tempSpecAttrRow['spec_' + item.key + '_calls'] = __('зв');
        tempSpecAttrRow['spec_' + item.key + '_signed_up'] = __('зап');
        tempSpecAttrRow['spec_' + item.key + '_showed_up'] = __('прих');
        tempSpecAttrRow['spec_' + item.key + '_treatments'] = __('леч');
    });

    tempSpecAttrRow['overall_calls'] = __('зв');
    tempSpecAttrRow['overall_signed_up'] = __('зап');
    tempSpecAttrRow['overall_showed_up'] = __('прих');
    tempSpecAttrRow['overall_treatments'] = __('леч');

    worksheet.addRow(tempSpecAttrRow);

    //INIT Overall object //переделать потом
    let overall = {};

    specializations.forEach((item, index) => {
        overall['spec_' + item.key + '_calls'] = 0;
        overall['spec_' + item.key + '_signed_up'] = 0;
        overall['spec_' + item.key + '_showed_up'] = 0;
        overall['spec_' + item.key + '_treatments'] = 0;
    });

    //инициализация для строки ИТОГО для блока ИТОГО в конце по иксу
    overall['overall_calls'] = 0;
    overall['overall_signed_up'] = 0;
    overall['overall_showed_up'] = 0;
    overall['overall_treatments'] = 0;

    Object.keys(byLocation).forEach(key => {
        let location = byLocation[key];

        let dataObj = {};

        specializations.forEach((item, index) => {
            if (location.bySpec[item.key]) {
                dataObj['spec_' + item.key + '_calls'] = location.bySpec[item.key].calls;
                dataObj['spec_' + item.key + '_signed_up'] = location.bySpec[item.key].appointments;
                dataObj['spec_' + item.key + '_showed_up'] = location.bySpec[item.key].income;
                dataObj['spec_' + item.key + '_treatments'] = location.bySpec[item.key].treatments;

                overall['spec_' + item.key + '_calls'] += location.bySpec[item.key].calls;
                overall['spec_' + item.key + '_signed_up'] += location.bySpec[item.key].appointments;
                overall['spec_' + item.key + '_showed_up'] += location.bySpec[item.key].income;
                overall['spec_' + item.key + '_treatments'] += location.bySpec[item.key].treatments;


            } else {
                dataObj['spec_' + item.key + '_calls'] = 0;
                dataObj['spec_' + item.key + '_signed_up'] = 0;
                dataObj['spec_' + item.key + '_showed_up'] = 0;
                dataObj['spec_' + item.key + '_treatments'] = 0;
            }

        });

        //вот эта лапша ещё раз для ИТОГО
        dataObj['overall_calls'] = location.calls;
        dataObj['overall_signed_up'] = location.appointments;
        dataObj['overall_showed_up'] = location.income;
        dataObj['overall_treatments'] = location.treatments;

        overall['overall_calls'] += location.calls;
        overall['overall_signed_up'] += location.appointments;
        overall['overall_showed_up'] += location.income;
        overall['overall_treatments'] += location.treatments;

        worksheet.addRow({
            city_village: key,
            ...dataObj
        });

    });

    //Последний ряд ИТОГО
    let overallRow = worksheet.addRow({
        city_village: __('Итого'),
        ...overall
    });
    
    overallRow.font = {
        name: 'Calibri',
        color: {argb: 'FFC14242'},
        family: 2,
        size: 11
    };


    worksheet.eachColumnKey((column, index) => {
        if (column.key.indexOf('_calls') >= 0) {
            column.width = 10;
            column.border = {
                left: {style: 'double', color: {argb: 'FF000000'}},
            };

        } else if (column.key.indexOf('_treatments') >= 0) {
            column.width = 10;
            column.border = {
                right: {style: 'double', color: {argb: 'FF000000'}},
            };
        }
    });


    let ii = 2;
    specializations.forEach((item, index) => {
        worksheet.getCell(toColumnName(ii) + '1').border = {
            top: {style: 'double', color: {argb: '00000000'}},
            bottom: {style: 'double', color: {argb: '00000000'}},
            left: {style: 'double', color: {argb: '00000000'}},
            right: {style: 'double', color: {argb: '00000000'}},
        };

        ii += 4;
    });
    //и ещё разочек бордеры для блока итого по иксу

    worksheet.getCell(toColumnName(ii) + '1').border = {
        top: {style: 'double', color: {argb: '00000000'}},
        bottom: {style: 'double', color: {argb: '00000000'}},
        left: {style: 'double', color: {argb: '00000000'}},
        right: {style: 'double', color: {argb: '00000000'}},
    };

    //Добордериваем ячейки ИТОГО
    overallRow.eachCell((cell, index) => {
        cell.border = {
            top: {style: 'double', color: {argb: 'FF000000'}},
            bottom: {style: 'double', color: {argb: 'FF000000'}},
        };

        if (cell._column.key.indexOf('_calls') >= 0) {
            cell.border.left = {style: 'double', color: {argb: 'FF000000'}};

        } else if (cell._column.key.indexOf('_treatments') >= 0) {
            cell.border.right = {style: 'double', color: {argb: 'FF000000'}};
        }
    });

    return Promise.resolve(workbook);
}
