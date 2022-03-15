import Excel from 'exceljs';


const ALIGNMENT = {vertical: 'middle', horizontal: 'center', wrapText: true,};
const FONT_BOLD = {
    bold: true,
    size: 10,
    font: 'Calibri',
};

const FILL_BLUE = {
    type: 'pattern',
    pattern:'solid',
    fgColor: {
        argb: '3283C7',
    }
};

const FILL_GREEN = {
    type: 'pattern',
    pattern:'solid',
    fgColor: {
        argb: 'B7B430',
    }
};

function toColumnName(number) {
    let colName = '',
        dividend = Math.floor(Math.abs(number)),
        rest;

    while (dividend > 0) {
        rest = (dividend - 1) % 26;
        colName = String.fromCharCode(65 + rest) + colName;
        dividend = parseInt((dividend - rest) / 26);
    }
    return colName;
}

function getRowName(row, informationSources, mediaTypes) {
    const source = _.find(informationSources, ['id', parseInt(row.source, 10)])

    if (row.is_total_by_media_type) {
        const mediaType = _.find(mediaTypes, ['id', row.media_type])
        return mediaType ? `${mediaType.value} итого` : null;
    }

    if (row.is_total) {
        return __('Итого');
    }

    if (row.is_predicts) {
        return __('Прогноз');
    }

    if (row.is_percent_by_predict) {
        return ''
    }

    if (row.is_total_for_month_ago || row.is_total_for_year_ago) {
        return `${row.date_start.split('-').reverse().join('.')} - ${row.date_end.split('-').reverse().join('.')}`
    }

    return source ? source.value : null;
}

export default (data, specialization, informationSources, mediaTypes) => {
    let workbook = new Excel.Workbook();
    const worksheet = workbook.addWorksheet("Данные", {
        views: [
            {state: 'frozen', ySplit: 1,},
        ],
    });
    let columns =
        [
            {header: __('Вид рекламы'), key:'location', width: 30,height: 15,
                style: {alignment: {vertical: 'middle', horizontal: 'center'}},
                border: {
                    top: {style: 'double', color: {argb: 'FF000000'}},
                    left: {style: 'double', color: {argb: 'FF000000'}},
                    bottom: {style: 'double', color: {argb: 'FF000000'}},
                    right: {style: 'double', color: {argb: 'FF000000'}}}
            },
        ];

    let rowNumber = 1;
    specialization.forEach((item)=> {
        columns.push(
            {
                header: item.short_name, key: 'spec_' + item.id + '_calls',
                style: {alignment: {vertical: 'middle', horizontal: 'center'}},
                border: {
                    top: {style: 'double', color: {argb: 'FF00FF00'}},
                    left: {style: 'double', color: {argb: 'FF00FF00'}},
                    bottom: {style: 'double', color: {argb: 'FF00FF00'}},
                    right: {style: 'double', color: {argb: 'FF00FF00'}}
                }
            }
        )
        columns.push({header: '', key: 'spec_' + item.id + '_signed_up',style: {alignment: {vertical: 'middle', horizontal: 'center'}}});
        columns.push({header: '', key: 'spec_' + item.id + '_showed_up',style: {alignment: {vertical: 'middle', horizontal: 'center'}},});
        columns.push({header: '', key: 'spec_' + item.id + '_treatments',style: {alignment: {vertical: 'middle', horizontal: 'center'}},});
        columns.push({header: '', key: 'spec_' + item.id + '_payments',style: {alignment: {vertical: 'middle', horizontal: 'center'}},});
    })
    columns.push({
        header: __('Итого'), key: 'overall_calls', style: {alignment: {vertical: 'middle', horizontal: 'center'}},
    });
    columns.push({header: '', key: 'overall_signed_up',style: {alignment: {vertical: 'middle', horizontal: 'center'}}});
    columns.push({header: '', key: 'overall_showed_up',style: {alignment: {vertical: 'middle', horizontal: 'center'}}});
    columns.push({header: '', key: 'overall_treatments',style: {alignment: {vertical: 'middle', horizontal: 'center'}}});
    columns.push({header: '', key: 'overall_payments',style: {alignment: {vertical: 'middle', horizontal: 'center'}}});

    columns.push({
        header: __('Прогноз звонки'), key: 'predicts_calls', style: {alignment: {vertical: 'middle', horizontal: 'center'}},
    });
    columns.push({
        header: __('Прогноз лечения'), key: 'predicts_treatments', style: {alignment: {vertical: 'middle', horizontal: 'center'}},
    });
    columns.push({
        header: __('% по видам рекламы'), key: 'percent_by_media_type', style: {alignment: {vertical: 'middle', horizontal: 'center'}},
    });

    worksheet.columns = columns;

    let i = 2;
    specialization.forEach((item, index) => {
        worksheet.mergeCells(toColumnName(i) + '1' + ':' + toColumnName(i + 4) + '1');
        i += 5;
    });

    worksheet.mergeCells(toColumnName(i) + '1' + ':' + toColumnName(i + 4) + '1')

    let tempSpecAttrRow = {};

    specialization.forEach((item, index) => {
        tempSpecAttrRow['spec_' + item.id + '_calls'] = __('зв');
        tempSpecAttrRow['spec_' + item.id + '_signed_up'] = __('зап');
        tempSpecAttrRow['spec_' + item.id + '_showed_up'] = __('прих');
        tempSpecAttrRow['spec_' + item.id + '_treatments'] = __('леч');
        tempSpecAttrRow['spec_' + item.id + '_payments'] = __('об');
    });
    tempSpecAttrRow['overall_calls'] = __('зв');
    tempSpecAttrRow['overall_signed_up'] = __('зап');
    tempSpecAttrRow['overall_showed_up'] = __('прих');
    tempSpecAttrRow['overall_treatments'] = __('леч');
    tempSpecAttrRow['overall_payments'] = __('об');
    worksheet.addRow(tempSpecAttrRow);
    let titleRow = worksheet.getRow(1);
    titleRow.alignment = ALIGNMENT;
    titleRow.height = 40;
    titleRow.font = FONT_BOLD;
    titleRow.fill = FILL_GREEN;

    let headerFirstRow = worksheet.getCell('A1');
    headerFirstRow.fill = FILL_BLUE;
    headerFirstRow.font = {color: {argb: 'ffffff'}};

    Object.keys(data).forEach(key => {
        let location = data[key];
        let dataObj = {};

        specialization.forEach((item, index) => {
            dataObj['spec_' + item.id + '_calls'] = location['calls_'+item.id] ? location['calls_'+item.id] : 0
            dataObj['spec_' + item.id + '_signed_up'] = location['appointments_'+item.id] ? location['appointments_'+item.id] : 0
            dataObj['spec_' + item.id + '_showed_up'] = location['incomes_'+item.id] ? location['incomes_'+item.id] : 0
            dataObj['spec_' + item.id + '_treatments'] = location['treatments_'+item.id] ?  location['treatments_'+item.id] : 0
            dataObj['spec_' + item.id + '_payments'] = location['payments_'+item.id] ?  location['payments_'+item.id] : 0
            dataObj["overall_calls"] = location['total_calls'];
            dataObj['overall_signed_up'] = location['total_appointments'];
            dataObj['overall_showed_up'] = location['total_incomes'];
            dataObj['overall_treatments'] = location['total_treatments'];
            dataObj['overall_payments'] = location['total_payments'];
            dataObj['predicts_calls'] = location['predict_calls'] || 0;
            dataObj['predicts_treatments'] = location['predict_treatments'] || 0;
            dataObj['percent_by_media_type'] = location['predict_percent'] || 0;
        });
        worksheet.addRow({
            location: getRowName(location, informationSources, mediaTypes),
            ...dataObj
        });

    });

    worksheet.eachColumnKey((column, index) => {
        if (column.key.indexOf('_calls') >= 0) {
            column.width = 10;
            column.border = {
                left: {style: 'double', color: {argb: 'FF000000'}},
            };

        } else if (column.key.indexOf('_payments') >= 0) {
            column.width = 10;
            column.border = {
                right: {style: 'double', color: {argb: 'FF000000'}},
            };
        }
    });

    worksheet.eachRow(function (row, rowNumber) {
        if (row.values[1] && row.values[1].includes('итого')) {
            row.font = {color: {argb: '3283C7'}};
            row.border = {
                top: {style: 'double', color: {argb: 'FF000000'}},
                bottom: {style: 'double', color: {argb: 'FF000000'}},
            }
        }

        if (worksheet._rows.length - rowNumber === 1 || worksheet._rows.length - rowNumber === 3 || worksheet._rows.length - rowNumber === 6) {
            row.font = {color: {argb: 'C84D4D'}};
        }
    });

    return Promise.resolve(workbook);
}
