import Excel from 'exceljs';
import handbook from '@/services/handbook';
import {dateFormat} from '@/services/format';
import moment from 'moment';

const PREV_MONTH = 'prev_month';
const PREV_YEAR = 'prev_year';
const BORDER_LIGHT_STYLES = {style: 'medium', color: {argb: 'FF000000'}};
const BORDER_DARK_STYLES = {style: 'medium', color: {argb: '00000000'}};
const BORDER_MEDIUM_STYLES = {style: 'medium', color: {argb: 'FF00FF00'}};
const ALIGNMENT = {vertical: 'middle', horizontal: 'center'};
const OVERALL_TOTALS = {
    overall_calls: 0,
    overall_signed_up: 0,
    overall_showed_up: 0,
    overall_treatments: 0,
};
const MOMENT_MONTH = 'months';
const MOMENT_YEAR = 'years';
const SKIP_SUB_COLUMN = __('НЕ ПРОФ');
const PERCENT_FORMAT = '0.00%';
const FONT = {
    name: 'Calibri',
    family: 2,
    size: 11
};

const toColumnName = (number) => {
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

const getPrevPeriodTitle = (filters, period = MOMENT_MONTH, subtract = 1) => {
    let start = moment(filters.date_start).subtract(subtract, period);
    let end = moment(filters.date_end).subtract(subtract, period);
    if (period = MOMENT_MONTH) {
        end = end.endOf('month');
    }
    return dateFormat(start) + ' - ' + dateFormat(end);
}

const getSkipColumnLetters = (worksheet, specializations) => {
    let specId = Object.keys(specializations).find(spec => specializations[spec].name == SKIP_SUB_COLUMN);
    let addresses = [];
    
    if (specId) {
        let keys = [
            `spec_${specId}_calls`,
            `spec_${specId}_signed_up`,
            `spec_${specId}_showed_up`,
            `spec_${specId}_treatments`,
        ];
        
        worksheet._columns.forEach(column => {
            if (keys.indexOf(column._key) !== -1) {
                addresses.push(column.letter);
            }
        });
    }
    return addresses;
}

const getMonthDaysCount = (date) => {
    return moment(date).daysInMonth();
}

const getDiffInDays = (start, end) => {
    return moment(end).diff(start, 'days') + 1;
}

const getCellAddresses = (specializations, targetRow, cellAddressesRow) => {
    specializations.forEach((prop, index) => {
        if (cellAddressesRow[prop] === undefined) {
            cellAddressesRow[prop] = {};
        }

        targetRow.eachCell((cell, index) => {
            let calls = 'spec_' + prop + '_calls';
            let signed = 'spec_' + prop + '_signed_up';
            let shows = 'spec_' + prop + '_showed_up';
            let treatments = 'spec_' + prop + '_treatments';

            if (cell._column.key == calls) {
                cellAddressesRow[prop][calls] = cell._address;
            } else if (cell._column.key == signed) {
                cellAddressesRow[prop][signed] = cell._address;
            } else if (cell._column.key == shows) {
                cellAddressesRow[prop][shows] = cell._address;
            } else if (cell._column.key == treatments) {
                cellAddressesRow[prop][treatments] = cell._address;
            }

            if (cell._column.key == 'overall_calls') {
                cellAddressesRow.overall.overall_calls = cell._address;
            } else if (cell._column.key == 'overall_signed_up') {
                cellAddressesRow.overall.overall_signed_up = cell._address;
            } else if (cell._column.key == 'overall_showed_up') {
                cellAddressesRow.overall.overall_showed_up = cell._address;
            } else if (cell._column.key == 'overall_treatments') {
                cellAddressesRow.overall.overall_treatments = cell._address;
            }
        });
    });
    return cellAddressesRow;
}

const getPrevPeriodRatio = (worksheet, prevPeriodCellAddresses, prognosisCellAddresses, resultRow = {}) => {
    Object.keys(prevPeriodCellAddresses).forEach((key, index) => {
        let prognosis = prognosisCellAddresses[key];
        let prevPeriod = prevPeriodCellAddresses[key];
        let callsKey;
        let signedKey;
        let showedKey;
        let treatmentKey;
        
        if (key != 'overall') {
            callsKey = 'spec_' + key + '_calls';
            signedKey = 'spec_' + key + '_signed_up';
            showedKey = 'spec_' + key + '_showed_up';
            treatmentKey = 'spec_' + key + '_treatments';
        } else {
            callsKey = 'overall_calls';
            signedKey = 'overall_signed_up';
            showedKey = 'overall_showed_up';
            treatmentKey = 'overall_treatments';
        }

        let callsPrognosis = worksheet.getCell(prognosis[callsKey]).value.result;
        let signedPrognosis = worksheet.getCell(prognosis[signedKey]).value.result;
        let showsPrognosis = worksheet.getCell(prognosis[showedKey]).value.result;
        let treatmentsPrognosis = worksheet.getCell(prognosis[treatmentKey]).value.result;
        
        let callsPrev = worksheet.getCell(prevPeriod[callsKey]).value;
        let signedPrev = worksheet.getCell(prevPeriod[signedKey]).value;
        let showsPrev = worksheet.getCell(prevPeriod[showedKey]).value;
        let treatmentsPrev = worksheet.getCell(prevPeriod[treatmentKey]).value;

        resultRow[callsKey] = {formula: (prognosis[callsKey] + '/' + prevPeriod[callsKey]), result: (callsPrev == 0 ? 0 : callsPrognosis / callsPrev) };
        resultRow[signedKey] = {formula: (prognosis[signedKey] + '/' + prevPeriod[signedKey]), result: (signedPrev == 0 ? 0 : signedPrognosis / signedPrev) };
        resultRow[showedKey] = {formula: (prognosis[showedKey] + '/' + prevPeriod[showedKey]), result: (showsPrev == 0 ? 0 : showsPrognosis / showsPrev)};
        resultRow[treatmentKey] = {formula: (prognosis[treatmentKey] + '/' + prevPeriod[treatmentKey]), result: (treatmentsPrev == 0 ? 0 : treatmentsPrognosis / treatmentsPrev)};
    });
    return resultRow;
}

export default (data, filters) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Данные'), {
        views: [
            {state: 'frozen', xSplit: 1, ySplit: 2}
        ]
    });

    let media_types = handbook.getOptions('media_type');

    let byType = {};
    let specializations = {};

    data.forEach((row) => {
        let type;
        let source;

        if (byType[row.media_type] === undefined) {
            type = byType[row.media_type] = {
                media_type: row.media_type,
                media_name: media_types.find(x => x.id === Number(row.media_type)),
                calls: 0,
                appointments: 0,
                income: 0,
                treatments: 0,
                bySource: {},
            };
        } else {
            type = byType[row.media_type];
        }

        if (type.bySource[row.source_id] === undefined) {
            source = type.bySource[row.source_id] = {
                source_id: row.source_id,
                source_name: row.source_name,
                specialization_id: row.specialization_id,
                specialization: row.specialization,
                calls: row.calls,
                appointments: row.appointments,
                income: row.income,
                treatments: row.treatments,
                bySpec: {}
            };
        } else {
            source = type.bySource[row.source_id];
            source.calls += row.calls;
            source.appointments += row.appointments;
            source.income += row.income;
            source.treatments += row.treatments;
        }

        if (source.bySpec[row.specialization_id] === undefined) {
            source.bySpec[row.specialization_id] = {
                source_id: row.source_id,
                source_name: row.source_name,
                specialization_id: row.specialization_id,
                specialization: row.specialization,
                calls: row.calls,
                appointments: row.appointments,
                income: row.income,
                treatments: row.treatments,
            };
        } else {
            source.bySpec[row.specialization_id].calls += row.calls;
            source.bySpec[row.specialization_id].appointments += row.appointments;
            source.bySpec[row.specialization_id].income += row.income;
            source.bySpec[row.specialization_id].treatments += row.treatments;
        }

        if (specializations[row.specialization_id] === undefined) {
            specializations[row.specialization_id] = {name: row.specialization, key: row.specialization_id};
        }
    });

    let specializationProps = Object.keys(specializations);

    let tempColumns = [
        {
            header: '', key: 'source', width: 30, height: 15,
            style: {alignment: ALIGNMENT},
            border: {
                top: BORDER_LIGHT_STYLES,
                left: BORDER_LIGHT_STYLES,
                bottom: BORDER_LIGHT_STYLES,
                right: BORDER_LIGHT_STYLES
            },
        }
    ];

    specializationProps.forEach((prop, index) => {
        let item = specializations[prop];
        tempColumns.push({
            header: item.name, key: 'spec_' + item.key + '_calls',
            style: {alignment: ALIGNMENT},
            border: {
                top: BORDER_MEDIUM_STYLES,
                left: BORDER_MEDIUM_STYLES,
                bottom: BORDER_MEDIUM_STYLES,
                right: BORDER_MEDIUM_STYLES
            },
        });
        tempColumns.push({header: '', key: 'spec_' + item.key + '_signed_up'});
        tempColumns.push({header: '', key: 'spec_' + item.key + '_showed_up'});
        tempColumns.push({header: '', key: 'spec_' + item.key + '_treatments'});
    });


    tempColumns.push({
        header: __('Итого'), key: 'overall_calls', style: {alignment: ALIGNMENT},
    });
    tempColumns.push({header: '', key: 'overall_signed_up'});
    tempColumns.push({header: '', key: 'overall_showed_up'});
    tempColumns.push({header: '', key: 'overall_treatments'});
    tempColumns.push({header: '', key: 'blank_column_right'});
    tempColumns.push({header: __('прогноз зв'), key: 'prognosis_calls'});
    tempColumns.push({header: __('прогноз леч'), key: 'prognosis_treatments'});
    tempColumns.push({header: '', key: 'prognosis_blank_column'});
    tempColumns.push({header: '', key: 'prognosis_percent_column', style: {numFmt: PERCENT_FORMAT}});

    worksheet.columns = tempColumns;
    
    

    //Это я мержу колонки для блока ИТОГО
    let i = 2;
    specializationProps.forEach((prop, index) => {
        let item = specializations[prop];
        worksheet.mergeCells(toColumnName(i) + '1' + ':' + toColumnName(i + 3) + '1');
        i += 4;
    });

    worksheet.mergeCells(toColumnName(i) + '1' + ':' + toColumnName(i + 3) + '1'); // это для последней колонки итого
    
    let tempSpecAttrRow = {};

    specializationProps.forEach((prop, index) => {
        let item = specializations[prop];

        tempSpecAttrRow['spec_' + item.key + '_calls'] = __('зв');
        if (item.name != SKIP_SUB_COLUMN) {
            tempSpecAttrRow['spec_' + item.key + '_signed_up'] = __('зап');
            tempSpecAttrRow['spec_' + item.key + '_showed_up'] = __('прих');
            tempSpecAttrRow['spec_' + item.key + '_treatments'] = __('леч');
        }
    });

    tempSpecAttrRow['overall_calls'] = __('зв');
    tempSpecAttrRow['overall_signed_up'] = __('зап');
    tempSpecAttrRow['overall_showed_up'] = __('прих');
    tempSpecAttrRow['overall_treatments'] = __('леч');

    tempSpecAttrRow['prognosis_calls'] = getDiffInDays(filters.date_start, filters.date_end);
    tempSpecAttrRow['prognosis_treatments'] = getMonthDaysCount(filters.date_start);
    

    worksheet.addRow(tempSpecAttrRow);

    //INIT Overall object //переделать потом
    let overall = {...OVERALL_TOTALS};

    specializationProps.forEach((prop, index) => {
        let item = specializations[prop];
        overall['spec_' + item.key + '_calls'] = 0;
        overall['spec_' + item.key + '_signed_up'] = 0;
        overall['spec_' + item.key + '_showed_up'] = 0;
        overall['spec_' + item.key + '_treatments'] = 0;
    });

    //инициализация для строки ИТОГО для блока ИТОГО в конце по иксу

    let typeOverallRows = [];

    Object.keys(byType).forEach(key => {
        let type = byType[key];
        let dataObj = {};
        let typeOverall = [];
        let typeOverallRow = [];
        
        
        if (type.media_name) {
            specializationProps.forEach((prop, index) => {
                let item = specializations[prop];
                typeOverall['spec_' + item.key + '_calls'] = 0;
                typeOverall['spec_' + item.key + '_signed_up'] = 0;
                typeOverall['spec_' + item.key + '_showed_up'] = 0;
                typeOverall['spec_' + item.key + '_treatments'] = 0;
            });
            // Вот это обнуляем каждый раз при итерации Типа медиа ( газетка, интернет, и.т.д) для подсчёта в блоке итого
            typeOverall['overall_calls'] = 0;
            typeOverall['overall_signed_up'] = 0;
            typeOverall['overall_showed_up'] = 0;
            typeOverall['overall_treatments'] = 0;
            
            _.forEach(type.bySource, source => {
                //Обнуляем  при каждом новом источнике для подсчёта в блок итого по иксу
                dataObj['overall_calls'] = 0;
                dataObj['overall_signed_up'] = 0;
                dataObj['overall_showed_up'] = 0;
                dataObj['overall_treatments'] = 0;

                specializationProps.forEach((prop, index) => {
                    let item = specializations[prop];

                    if (source.bySpec[item.key]) {
                        let calls = source.bySpec[item.key].calls;
                        let appointments = source.bySpec[item.key].appointments;
                        let income = source.bySpec[item.key].income;
                        let treatments = source.bySpec[item.key].treatments;
                        
                        dataObj['spec_' + item.key + '_calls'] = calls;

                        if (item.name != SKIP_SUB_COLUMN) {
                            dataObj['spec_' + item.key + '_signed_up'] = appointments;
                            dataObj['spec_' + item.key + '_showed_up'] = income;
                            dataObj['spec_' + item.key + '_treatments'] = treatments;
                            
                            typeOverall['spec_' + item.key + '_signed_up'] += appointments;
                            typeOverall['spec_' + item.key + '_showed_up'] += income;
                            typeOverall['spec_' + item.key + '_treatments'] += treatments;

                            overall['spec_' + item.key + '_signed_up'] += appointments;
                            overall['spec_' + item.key + '_showed_up'] += income;
                            overall['spec_' + item.key + '_treatments'] += treatments;

                            overall['overall_signed_up'] += appointments;
                            overall['overall_showed_up'] += income;
                            overall['overall_treatments'] += treatments;

                            typeOverall['overall_signed_up'] += appointments;
                            typeOverall['overall_showed_up'] += income;
                            typeOverall['overall_treatments'] += treatments;

                            dataObj['overall_signed_up'] += appointments;
                            dataObj['overall_showed_up'] += income;
                            dataObj['overall_treatments'] += treatments;
                        }

                        typeOverall['spec_' + item.key + '_calls'] += calls;
                        overall['spec_' + item.key + '_calls'] += calls;
                        overall['overall_calls'] += calls;
                        typeOverall['overall_calls'] += calls;
                        dataObj['overall_calls'] += calls;

                    } else {
                        dataObj['spec_' + item.key + '_calls'] = 0;
                        if (item.name != SKIP_SUB_COLUMN) {
                            dataObj['spec_' + item.key + '_signed_up'] = 0;
                            dataObj['spec_' + item.key + '_showed_up'] = 0;
                            dataObj['spec_' + item.key + '_treatments'] = 0;
                        }
                    }
                });

                worksheet.addRow({
                    source: source.source_name,
                    ...dataObj
                });
            });

            //заполняем стилизируем и пихаем в массив для достилизации в конце файла
            typeOverallRow = worksheet.addRow({
                source: __('Итого') + ' ' + type.media_name.value,
                ...typeOverall,
            });

            typeOverallRow.font = {...FONT, color: {argb: 'FF0070C0'}};

            typeOverallRows.push(typeOverallRow);
        }
    });

    //Последний ряд ИТОГО
    let overallRow = worksheet.addRow({
        source: __('Итого'),
        ...overall
    });

    overallRow.font = {...FONT, color: {argb: 'FFC14242'}};
    
    worksheet.eachColumnKey((column, index) => {
        if (column.key.indexOf('_calls') >= 0) {
            column.width = 10;
            column.border = {
                left: BORDER_LIGHT_STYLES,
            };

        } else if (column.key.indexOf('_treatments') >= 0) {
            column.width = 10;
            column.border = {
                right: BORDER_LIGHT_STYLES,
            };
        }
    });

    let ii = 2;
    specializationProps.forEach((prop, index) => {
        let item = specializations[prop];
        worksheet.getCell(toColumnName(ii) + '1').border = {
            top: BORDER_DARK_STYLES,
            bottom: BORDER_DARK_STYLES,
            left: BORDER_DARK_STYLES,
            right: BORDER_DARK_STYLES,
        };

        ii += 4;
    });

    //и ещё разочек бордеры для блока итого по иксу
    worksheet.getCell(toColumnName(ii) + '1').border = {
        top: BORDER_DARK_STYLES,
        bottom: BORDER_DARK_STYLES,
        left: BORDER_DARK_STYLES,
        right: BORDER_DARK_STYLES,
    };

    //Добордериваем ячейки ИТОГО
    let overAllCallsCellAddress;
    let overAllCallsCellValue;
    
    overallRow.eachCell((cell, index) => {
        let key = cell._column.key;
        if (key == 'overall_calls') {
            overAllCallsCellAddress = cell.address;
            overAllCallsCellValue = cell.value;
        }

        cell.border = {
            top: BORDER_LIGHT_STYLES,
            bottom: BORDER_LIGHT_STYLES,
        };

        if (key.indexOf('_calls') !== -1) {
            cell.border.left = BORDER_LIGHT_STYLES;
        } else if (key.indexOf('_treatments') !== -1) {
            cell.border.right = BORDER_LIGHT_STYLES;
        }
    });

    let percentCellAddress = [];

    typeOverallRows.forEach((row) => {
        row.eachCell((cell, index) => {
            if (cell._column.key == 'overall_calls') {
                percentCellAddress.push(cell.address);
            }
            
            cell.border = {
                top: BORDER_LIGHT_STYLES,
                bottom: BORDER_LIGHT_STYLES,
            };

            if (cell._column.key.indexOf('_calls') >= 0) {
                cell.border.left = BORDER_LIGHT_STYLES;

            } else if (cell._column.key.indexOf('_treatments') >= 0) {
                cell.border.right = BORDER_LIGHT_STYLES;
            }
        });
    });

    /**
     * Seed column with formulas
     */

    //Get prognosis days past and days period letters
    let prognosisPastLetter = worksheet.getColumn('prognosis_calls').letter;
    let prognosisDaysLetter = worksheet.getColumn('prognosis_treatments').letter;
    let prognosisPercentLetter = worksheet.getColumn('prognosis_percent_column').letter;

    //Get overall columns letters
    let callsLetter = worksheet.getColumn('overall_calls').letter;
    let treatmentsLetter = worksheet.getColumn('overall_treatments').letter;

    let pastDaysCell =  prognosisPastLetter + '2';
    let monthDaysCell =  prognosisDaysLetter + '2';
    let pastDaysCellValue =  worksheet.getCell(pastDaysCell).value;
    let monthDaysCellValue =  worksheet.getCell(monthDaysCell).value;

    worksheet.eachRow((row, rowNumber) => {
        if (rowNumber < 3) {
            return;
        }

        //Get initial results
        let rowCallsCellAddress = (callsLetter + rowNumber);
        let rowCalls = worksheet.getCell(rowCallsCellAddress).value;
        let rowTreatments = worksheet.getCell(treatmentsLetter + rowNumber).value;
        let initialCalls = rowCalls / pastDaysCellValue * monthDaysCellValue;
        let initialTreatments = rowTreatments / pastDaysCellValue * monthDaysCellValue;

        //Set formulas and initial results
        let callsFormula = (rowCallsCellAddress) + '/' + pastDaysCell + '*' + monthDaysCell;
        let treatmentsFormula = (treatmentsLetter + rowNumber) + '/' + pastDaysCell + '*' + monthDaysCell;
        
        worksheet.getCell(prognosisPastLetter + rowNumber).value = {formula: callsFormula, result: initialCalls};
        worksheet.getCell(prognosisDaysLetter + rowNumber).value = {formula: treatmentsFormula, result: initialTreatments};

        if (percentCellAddress.indexOf(rowCallsCellAddress) !== -1) {
            let percentFormula = rowCallsCellAddress + '/' + overAllCallsCellAddress;
            let percentResult = rowCalls / overAllCallsCellValue;
            worksheet.getCell(prognosisPercentLetter + rowNumber).value = {formula: percentFormula, result: percentResult };
        }
    });

    /**
     * Table bottom percent row
     */
    
    //Get addresses for  table bottom percent row calculation
    let totalRowCellAddresses = getCellAddresses(specializationProps, overallRow, {overall: {}});
    let tableBottomPercentRow = {};
    let tableBottomPrognosisRow = {source: __('Прогноз')};

    Object.keys(totalRowCellAddresses).forEach((key, index) => {
        let data = totalRowCellAddresses[key];
        let callsKey;
        let signedKey;
        let showedKey;
        let treatmentKey;
        
        if (key != 'overall') {
            callsKey = 'spec_' + key + '_calls';
            signedKey = 'spec_' + key + '_signed_up';
            showedKey = 'spec_' + key + '_showed_up';
            treatmentKey = 'spec_' + key + '_treatments';
        } else {
            callsKey = 'overall_calls';
            signedKey = 'overall_signed_up';
            showedKey = 'overall_showed_up';
            treatmentKey = 'overall_treatments';
        }

        let calls = worksheet.getCell(data[callsKey]).value;
        let signed = worksheet.getCell(data[signedKey]).value;
        let shows = worksheet.getCell(data[showedKey]).value;
        let treatments = worksheet.getCell(data[treatmentKey]).value;

        tableBottomPercentRow[signedKey] = {formula: (data[signedKey] + '/' + data[callsKey]), result: (calls == 0 ? 0 : signed / calls) };
        tableBottomPercentRow[showedKey] = {formula: (data[showedKey] + '/' + data[callsKey]), result: (calls == 0 ? 0 : shows / calls)};
        tableBottomPercentRow[treatmentKey] = {formula: (data[treatmentKey] + '/' + data[showedKey]), result: (shows == 0 ? 0 : treatments / shows)};

        tableBottomPrognosisRow[callsKey] = {formula: (data[callsKey] + '/' + pastDaysCell + '*' + monthDaysCell), result: (calls / pastDaysCellValue * monthDaysCellValue)}
        tableBottomPrognosisRow[signedKey] = {formula: (data[signedKey] + '/' + pastDaysCell + '*' + monthDaysCell), result: (signed / pastDaysCellValue * monthDaysCellValue)}
        tableBottomPrognosisRow[showedKey] = {formula: (data[showedKey] + '/' + pastDaysCell + '*' + monthDaysCell), result: (shows / pastDaysCellValue * monthDaysCellValue)}
        tableBottomPrognosisRow[treatmentKey] = {formula: (data[treatmentKey] + '/' + pastDaysCell + '*' + monthDaysCell), result: (treatments / pastDaysCellValue * monthDaysCellValue)}
    });
    
    tableBottomPercentRow = worksheet.addRow(tableBottomPercentRow);
    tableBottomPercentRow.eachCell((cell, index) => {
        cell.numFmt = PERCENT_FORMAT;
    });

    //Empty row for dividing tables
    worksheet.addRow({});
    tableBottomPrognosisRow = worksheet.addRow(tableBottomPrognosisRow);
    worksheet.addRow({});

    /**
     * Adding prev period rows
     */ 
    let prevMonthData = byType[PREV_MONTH] ? byType[PREV_MONTH].bySource[0].bySpec : null;
    let prevYearData = byType[PREV_YEAR] ? byType[PREV_YEAR].bySource[0].bySpec : null;
    let prevMonthRow = {source: getPrevPeriodTitle(filters), ...OVERALL_TOTALS};
    let prevYearRow = {source: getPrevPeriodTitle(filters, MOMENT_YEAR), ...OVERALL_TOTALS};

    specializationProps.forEach((prop, index) => {
        let item = specializations[prop];

        if (prevMonthData && prevMonthData[item.key] != undefined) {
            prevMonthRow['spec_' + item.key + '_calls'] = prevMonthData[item.key].calls;
            prevMonthRow['spec_' + item.key + '_signed_up'] = prevMonthData[item.key].appointments;
            prevMonthRow['spec_' + item.key + '_showed_up'] = prevMonthData[item.key].income;
            prevMonthRow['spec_' + item.key + '_treatments'] = prevMonthData[item.key].treatments;

            prevMonthRow['overall_calls'] += prevMonthData[item.key].calls;
            prevMonthRow['overall_signed_up'] += prevMonthData[item.key].appointments;
            prevMonthRow['overall_showed_up'] += prevMonthData[item.key].income;
            prevMonthRow['overall_treatments'] += prevMonthData[item.key].treatments;
        } else {
            prevMonthRow['spec_' + item.key + '_calls'] = 0;
            prevMonthRow['spec_' + item.key + '_signed_up'] = 0;
            prevMonthRow['spec_' + item.key + '_showed_up'] = 0;
            prevMonthRow['spec_' + item.key + '_treatments'] = 0;
        }
        
        if (prevYearData && prevYearData[item.key] != undefined) {
            prevYearRow['spec_' + item.key + '_calls'] = prevYearData[item.key].calls;
            prevYearRow['spec_' + item.key + '_signed_up'] = prevYearData[item.key].appointments;
            prevYearRow['spec_' + item.key + '_showed_up'] = prevYearData[item.key].income;
            prevYearRow['spec_' + item.key + '_treatments'] = prevYearData[item.key].treatments;

            prevYearRow['overall_calls'] += prevYearData[item.key].calls;
            prevYearRow['overall_signed_up'] += prevYearData[item.key].appointments;
            prevYearRow['overall_showed_up'] += prevYearData[item.key].income;
            prevYearRow['overall_treatments'] += prevYearData[item.key].treatments;
        } else {
            prevYearRow['spec_' + item.key + '_calls'] = 0;
            prevYearRow['spec_' + item.key + '_signed_up'] = 0;
            prevYearRow['spec_' + item.key + '_showed_up'] = 0;
            prevYearRow['spec_' + item.key + '_treatments'] = 0;
        }    
    });
    
    prevMonthRow = worksheet.addRow(prevMonthRow);    
    prevMonthRow.eachCell((cell, index) => {
        cell.border = {
            top: BORDER_LIGHT_STYLES,
            bottom: BORDER_LIGHT_STYLES,
            left: BORDER_LIGHT_STYLES,
            right: BORDER_LIGHT_STYLES,
        };
    });
    prevMonthRow.font = {...FONT, color: {argb: 'FFC14242'}};

    let prognosisRowCellAddresses = getCellAddresses(specializationProps, tableBottomPrognosisRow, {overall: {}});
    let prevMonthCellAddresses = getCellAddresses(specializationProps, prevMonthRow, {overall: {}});
    let prevMonthRatioRow = getPrevPeriodRatio(worksheet, prevMonthCellAddresses, prognosisRowCellAddresses, {});

    prevMonthRatioRow = worksheet.addRow(prevMonthRatioRow);
    prevMonthRatioRow.eachCell((cell, index) => {
        cell.numFmt = PERCENT_FORMAT;
    });

    prevYearRow = worksheet.addRow(prevYearRow);
    prevYearRow.eachCell((cell, index) => {
        cell.border = {
            top: BORDER_LIGHT_STYLES,
            bottom: BORDER_LIGHT_STYLES,
            left: BORDER_LIGHT_STYLES,
            right: BORDER_LIGHT_STYLES,
        };
    });
    prevYearRow.font = {...FONT, color: {argb: 'FFC14242'}};

    let prevYearCellAddresses = getCellAddresses(specializationProps, prevYearRow, {overall: {}});
    let prevYearRatioRow = getPrevPeriodRatio(worksheet, prevYearCellAddresses, prognosisRowCellAddresses, {});

    prevYearRatioRow = worksheet.addRow(prevYearRatioRow);
    prevYearRatioRow.eachCell((cell, index) => {
        cell.numFmt = PERCENT_FORMAT;
    });

    // Merge sells for skipping all subcolumns (non profile) specialization
    let skipColumnLetters = getSkipColumnLetters(worksheet, specializations);
    let skipLettersLength = skipColumnLetters.length;
    if (skipLettersLength != 0) {
        worksheet.eachRow((row, rowNumber) => {
            if (rowNumber == 1) {
                return;
            }
            worksheet.mergeCells(skipColumnLetters[0] + rowNumber + ':' + skipColumnLetters[skipLettersLength - 1] + rowNumber);
        });
    }
    return Promise.resolve(workbook);
}
