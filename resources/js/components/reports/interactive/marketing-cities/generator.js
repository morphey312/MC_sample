import Excel from 'exceljs';


const ALIGNMENT = {vertical: 'middle', horizontal: 'center', wrapText: true,};
const FONT_BOLD = {
    bold: true,
    size: 10,
    font: 'Calibri',
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


export default (data, specialization) => {
    let workbook = new Excel.Workbook();
    const worksheet = workbook.addWorksheet("Данные", {
        views: [
            {state: 'frozen', ySplit: 1,},
        ],
    });
    let columns =
         [
             {header: __('Город'), key:'location', width: 30,height: 15,
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
    })
    columns.push({
        header: __('Итого'), key: 'overall_calls', style: {alignment: {vertical: 'middle', horizontal: 'center'}},
    });
    columns.push({header: '', key: 'overall_signed_up',style: {alignment: {vertical: 'middle', horizontal: 'center'}}});
    columns.push({header: '', key: 'overall_showed_up',style: {alignment: {vertical: 'middle', horizontal: 'center'}}});
    columns.push({header: '', key: 'overall_treatments',style: {alignment: {vertical: 'middle', horizontal: 'center'}}});

    worksheet.columns = columns;

    let i = 2;
    specialization.forEach((item, index) => {
        worksheet.mergeCells(toColumnName(i) + '1' + ':' + toColumnName(i + 3) + '1');
        i += 4;
    });

    worksheet.mergeCells(toColumnName(i) + '1' + ':' + toColumnName(i + 3) + '1')

    let tempSpecAttrRow = {};

    specialization.forEach((item, index) => {
        tempSpecAttrRow['spec_' + item.id + '_calls'] = __('зв');
        tempSpecAttrRow['spec_' + item.id + '_signed_up'] = __('зап');
        tempSpecAttrRow['spec_' + item.id + '_showed_up'] = __('прих');
        tempSpecAttrRow['spec_' + item.id + '_treatments'] = __('леч');
    });
    tempSpecAttrRow['overall_calls'] = __('зв');
    tempSpecAttrRow['overall_signed_up'] = __('зап');
    tempSpecAttrRow['overall_showed_up'] = __('прих');
    tempSpecAttrRow['overall_treatments'] = __('леч');
    worksheet.addRow(tempSpecAttrRow);
    let titleRow = worksheet.getRow(1);
    titleRow.alignment = ALIGNMENT;
    titleRow.height = 40;
    titleRow.font = FONT_BOLD;

    Object.keys(data).forEach(key => {

        let location = data[key];

        let dataObj = {};
        specialization.forEach((item, index) => {
            dataObj['spec_' + item.id + '_calls'] = location['calls_'+item.id] ? location['calls_'+item.id] : 0
            dataObj['spec_' + item.id + '_signed_up'] = location['appointments_'+item.id] ? location['appointments_'+item.id] : 0
            dataObj['spec_' + item.id + '_showed_up'] = location['incomes_'+item.id] ? location['incomes_'+item.id] : 0
            dataObj['spec_' + item.id + '_treatments'] = location['treatments_'+item.id] ?  location['treatments_'+item.id] : 0
            dataObj["overall_calls"] = location['total_calls'];
            dataObj['overall_signed_up'] = location['total_appointments'];
            dataObj['overall_showed_up'] = location['total_incomes'];
            dataObj['overall_treatments'] = location['total_treatments'];
        });
        worksheet.addRow({
            location: location['is_total'] ? __('Итого') : location['patient_location'],
            ...dataObj
        });

    });

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

    return Promise.resolve(workbook);
}
