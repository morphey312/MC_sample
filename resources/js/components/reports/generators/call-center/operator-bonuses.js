import Excel from 'exceljs';
import CONSTANTS from '@/constants';

const NIGHT_POSITION_ID = 78;
const RATE_MIN = 99;
const RATE_MID = 110;
const WEIGHTS = {
    IS_FIRST: 0.2,
    INCOMES: 0.3,
    POST_WRAP: 0.1,
    REPEATED: 0.1,
    EVALUATION: 0.15,
    MISTAKES: 0.15,
}
const FILL_GREY = {
    type: 'pattern',
    pattern:'solid',
    fgColor: {
        argb: 'ededed',
    }
};
const ALIGNMENT = {vertical: 'middle', horizontal: 'center'};
const BORDER_LIGHT_STYLES = {style: 'thin', color: {argb: 'FF000000'}};
const BORDER_AROUND = {
    top: BORDER_LIGHT_STYLES,
    left: BORDER_LIGHT_STYLES,
    bottom: BORDER_LIGHT_STYLES,
    right: BORDER_LIGHT_STYLES
};
const PERCENT_FORMAT = '0%';
const FONT_BOLD = {
    bold: true,
    size: 10,
    font: 'Calibri',
};

const clearSpaces = (clinics) => {
    return clinics.map(clinic => {
        clinic.clinic_name = clinic.clinic_name.replace(/\s/g, '_');
        return clinic;
    })
}

const getOperator = (operator_id, list) => {
    return list.find(operator => operator.id == operator_id);
}

const getOperatorName = (operator_id, list) => {
    let operator = getOperator(operator_id, list);
    return operator ? operator.full_name : operator_id;
}

const getUniqueOperators = (list, operators) => {
    let result = {};
    list.forEach(item => {
        if (!result[item.operator_id]) {
            result[item.operator_id] = getOperatorName(item.operator_id, operators);
        }
    });
    return result;
}

const findOperatorBySearchField = (list, id, search = 'for_appointments') => {
    return list.find(item => item.operator_id == id && item[search] == 1);
}

const findOperatorValue = (list, id, search = 'for_appointments', field = 'total_appointments') => {
    let operator = findOperatorBySearchField(list, id, search);

    if (operator && (search == 'for_appointments' || search == 'for_incomes')) {
        // get total calls as sum of operator appointments and operator calls
        let operatorCalls = findOperatorBySearchField(list, id, 'for_calls');
        let total = operatorCalls ? operatorCalls.total_calls : 0;

        if (search == 'for_appointments') {
            total += operator.total_appointments;
        } else if (search == 'for_incomes') {
            // if search for_incomes - get appointments
            let operatorAppointments = findOperatorBySearchField(list, id);
            total += operatorAppointments ? operatorAppointments.total_appointments : 0;
        }
        return (operator && total > 0) ? (operator[field] / total) : 0;
    }
    
    return operator ? operator[field] : 0;
}

const getRate = (operator, clinic, kpi, superviser) => {
    let employeeClinic = operator.employee_clinics.find(item => item.clinic_id == clinic.clinic_id);
    if (employeeClinic) {
        if (employeeClinic.status === CONSTANTS.EMPLOYEE.STATUSES.PROBATION) {
            return clinic.rate_minimum;
        }
        if (superviser && superviser.id == employeeClinic.position_id) {
            return clinic.rate_maximum;
        }
    }
    return getClinicRate(clinic, kpi);
}

const getClinicRate = (clinic, result) => {
    if (result) {
        let kpi = result * 100;

        if (kpi < RATE_MIN) {
            return clinic.rate_minimum;
        } 

        if (kpi < RATE_MID) {
            return clinic.rate_medium;
        }
        return clinic.rate_maximum;
    }
    return 0;
}

const getOperatorTotalRepeated = (id, list) => {
    return list.reduce((total, row) => {
        if (row.operator_id == id && row.for_repeated == 1) {
            total += row.total_appointments;
        }
        return total;
    }, 0);
}

const addClinicSheet = (workbook, totals, clinic, data) => {
    let operators = data.operators;
    let worksheet = workbook.addWorksheet(clinic.clinic_name, {
        views: [
            {state: 'frozen', xSplit: 2, ySplit: 2}
        ]
    });
    let columns = [
        {header: '', key: 'indicators', width: 30, height: 15,},
        {header: '', key: 'weight'},
    ];
    
    let uniqueOperators = getUniqueOperators(totals, operators);
    let operatorKeys = Object.keys(uniqueOperators);
    let totalsList = {};

    operatorKeys.forEach(id => {
        columns.push({header: uniqueOperators[id], key: 'norm_' + id});
        columns.push({header: uniqueOperators[id], key: 'fact_' + id});
        columns.push({header: uniqueOperators[id], key: 'index_' + id, style: {numFmt: PERCENT_FORMAT}, width: 12});

        //Get total repeated in all clinics for each operator
        if (!totalsList[id]) {
            totalsList[id] = getOperatorTotalRepeated(id, data.totals);
        }
    });
    
    worksheet.columns = columns;

    //Subheader row
    let rowSubHeader = {indicators: __('Ключевые показатели'), weight: __('Вес')};
    operatorKeys.forEach(id => {
        rowSubHeader['norm_' + id] = __('Норма');
        rowSubHeader['fact_' + id] = __('Факт');
        rowSubHeader['index_' + id] = __('Индекс kpi');
    });
    rowSubHeader = worksheet.addRow(rowSubHeader);
    rowSubHeader.fill = FILL_GREY;
    rowSubHeader.font = FONT_BOLD;

    // Data rows
    let rowIsFirst = {indicators: __('% первичных записей'), weight: WEIGHTS.IS_FIRST};
    let rowIncomes = {indicators: __('% первичных приходов'), weight: WEIGHTS.INCOMES};
    let rowWrapups = {indicators: __('Постобработка (в сек.)'), weight: WEIGHTS.POST_WRAP};
    let rowRepeated = {indicators: __('Повторные записи'), weight: WEIGHTS.REPEATED};
    let rowEvaluation = {indicators: __('Оценочный лист'), weight: WEIGHTS.EVALUATION};
    let rowMistakes = {indicators: __('Ошибки'), weight: WEIGHTS.MISTAKES};
    let rowSummary = {indicators: __('Итоговый KPI'), weight: ''};

    operatorKeys.forEach(id => {
        let operator = getOperator(id, operators);
        let is_night = false;

        // Verify if operator works at night for norm use
        if (operator && operator.employee_clinics) {
            let employeeClinic = operator.employee_clinics.find(item => {
                return item.clinic_id === clinic.clinic_id;
            });

            if (employeeClinic && employeeClinic.position_id === NIGHT_POSITION_ID) {
                is_night = true;
            }
        }

        //first appointments
        rowIsFirst['norm_' + id] = clinic.appointment_norm;
        rowIsFirst['fact_' + id] = findOperatorValue(totals, id);
        
        //first incomes
        rowIncomes['norm_' + id] = clinic.income_norm;
        rowIncomes['fact_' + id] = findOperatorValue(totals, id, 'for_incomes', 'total_incomes');

        //repeated appointments
        rowRepeated['norm_' + id] =  is_night ? clinic.night_repeated_patient : clinic.day_repeated_patient;
        rowRepeated['fact_' + id] = totalsList[id];

        //operator wrapups
        rowWrapups['norm_' + id] = is_night ? clinic.night_post_call : clinic.day_post_call;
        rowWrapups['fact_' + id] = operator ? Math.round(operator.wrapups_average) : 0;

        //operator evaluation
        rowEvaluation['norm_' + id] = clinic.evaluation_norm;
        rowEvaluation['fact_' + id] = (operator && operator.operator_bonus) ? Number(operator.operator_bonus.evaluation) : 0;

        //operator mistakes
        let clinicData = (operator && operator.operator_bonus && operator.operator_bonus.clinics) 
                        ? operator.operator_bonus.clinics.find(item => item.clinic_id == clinic.clinic_id) 
                        : false;

        rowMistakes['norm_' + id] = clinic.mistakes_norm;
        rowMistakes['fact_' + id] = clinicData ? Number(clinicData.mistakes) : 0;

        // summary row for style purpose
        rowSummary['norm_' + id] = '';
    });

    rowIsFirst = worksheet.addRow(rowIsFirst);
    rowIncomes = worksheet.addRow(rowIncomes);
    rowWrapups = worksheet.addRow(rowWrapups);
    rowRepeated = worksheet.addRow(rowRepeated);
    rowEvaluation = worksheet.addRow(rowEvaluation);
    rowMistakes = worksheet.addRow(rowMistakes);
    rowSummary = worksheet.addRow(rowSummary);

    //Get operator columns letters
    let operatorLetters = {};

    operatorKeys.forEach(id => {
        if (!operatorLetters[id]) {
            operatorLetters[id] = {
                norm: worksheet.getColumn('norm_' + id).letter,
                fact: worksheet.getColumn('fact_' + id).letter,
                index: worksheet.getColumn('index_' + id).letter,
            }
        }
    });

    let weightColumnLetter = worksheet.getColumn('weight').letter;
    
    //Style cells, merge subheader operator name cells, seed kpi index cells
    worksheet.eachRow((row, rowNumber) => {
        if (rowNumber == 1) {
            row.eachCell(cell => {
                if (cell._column.key.indexOf('weight') !== -1) {
                    cell.fill = FILL_GREY;
                    cell.border = BORDER_AROUND;
                }
            });

            // Merge subheader operator name cells
            operatorKeys.forEach(id => {
                worksheet.mergeCells(operatorLetters[id].norm + rowNumber + ':' + operatorLetters[id].index + rowNumber);
            })
            return;
        }
        
        //Add styles for weight cells and each operator norm cells
        row.eachCell(cell => {
            if (cell._column.key.indexOf('indicators') !== -1 || 
                cell._column.key.indexOf('weight') !== -1 || 
                cell._column.key.indexOf('norm_') !== -1) {
                cell.fill = FILL_GREY;
                cell.border = BORDER_AROUND;
            }
        });

        //Seed index cells
        if (rowNumber > 2 && rowNumber < 9) {
            operatorKeys.forEach(id => {
                let weightAddress = weightColumnLetter + rowNumber;
                let normAddress = operatorLetters[id].norm + rowNumber;
                let factAddress = operatorLetters[id].fact + rowNumber;
                let normCell = worksheet.getCell(normAddress);
                let factCell = worksheet.getCell(factAddress);
                let weight = worksheet.getCell(weightAddress).value;

                //Add percent format for first two rows norm and fact cells
                if (rowNumber == 3 || rowNumber == 4) {
                    factCell.numFmt = PERCENT_FORMAT;
                    normCell.value = (normCell.value / 100);
                    normCell.numFmt = PERCENT_FORMAT;
                }

                //Use different formula for postwrap and mistakes rows
                if (rowNumber == 5 || rowNumber == 8) {
                    let formula = 'IF(' + factAddress + '>0, ' + weightAddress + '*' + normAddress + '/' + factAddress + ', 0)';
                    let result = (factCell.value > 0) ? (weight * normCell.value / factCell.value) : 0;
                    worksheet.getCell(operatorLetters[id].index + rowNumber).value = {formula, result};
                } else {
                    let formula = 'IF(' + normAddress + '>0, ' + weightAddress + '*' + factAddress + '/' + normAddress + ', 0)';
                    let result = (normCell.value > 0) ? (weight * factCell.value / normCell.value) : 0;
                    worksheet.getCell(operatorLetters[id].index + rowNumber).value = {formula, result};
                }
                
            });
        }
    });

    //Operators name row alignment
    worksheet.getRow(1).alignment = ALIGNMENT;

    //Seed and style summary row
    let summaryRow = worksheet.lastRow;
    summaryRow.font = FONT_BOLD;
    operatorKeys.forEach(id => {
        let formula = `SUM(${operatorLetters[id].index + '3'}:${operatorLetters[id].index + '8'})`;
        let i = 3;
        let result = 0;
        let letter = operatorLetters[id].index;

        for (i; i < 9; i++) {
            let cell = worksheet.getCell(letter + i);
            result += cell.result;
        }
        worksheet.getCell(operatorLetters[id].index + '9').value = {formula, result};
    });
    return {operatorLetters, uniqueOperators};
}

const seedSummarySheet = (workbook, worksheet, data, operatorsData) => {
    let columns = [
        {header: __('ФИО'), key: 'full_name', width: 31, height: 15,},
    ];

    //Adding columns for each clinics
    data.clinics.forEach(clinic => {
        columns.push({header: clinic.clinic_name, key: 'kpi' + clinic.clinic_id, width: 11, style: {numFmt: PERCENT_FORMAT} });
        columns.push({header: clinic.clinic_name, key: 'rate' + clinic.clinic_id });
        columns.push({header: clinic.clinic_name, key: 'incomes' + clinic.clinic_id });
        columns.push({header: clinic.clinic_name, key: 'bonus' + clinic.clinic_id, width: 11, style: {border: BORDER_AROUND, fill: FILL_GREY}});
    });

    worksheet.columns = columns;

    //Adding subheader row
    let rowSubheader = {}

    data.clinics.forEach(clinic => {
        rowSubheader['kpi' + clinic.clinic_id] = 'kpi';
        rowSubheader['rate' + clinic.clinic_id] = __('ставка');
        rowSubheader['incomes' + clinic.clinic_id] = __('приход');
        rowSubheader['bonus' + clinic.clinic_id] = __('бонус');

        let kpiLetter = worksheet.getColumn('kpi' + clinic.clinic_id).letter;
        let bonusLetter = worksheet.getColumn('bonus' + clinic.clinic_id).letter;

        //merge cells for first clinic row
        worksheet.mergeCells(`${kpiLetter}1:${bonusLetter}1`);
        worksheet.getCell(bonusLetter+'1').border = {right: BORDER_LIGHT_STYLES}; 
    });
    rowSubheader = worksheet.addRow(rowSubheader);

    //Clinic names row styles
    worksheet.getRow(1).alignment = ALIGNMENT;
    worksheet.getRow(1).font = {...FONT_BOLD, size: 11};

    let superviser = data.positions.find(p => p.is_superviser == true);
    //Init operator rows
    data.operators.forEach(operator => {
        let operatorRow = {full_name: operator.full_name};
        data.clinics.forEach(clinic => {
            let referenceWorksheetName = clinic.clinic_name;
            let clinicData = operatorsData[clinic.clinic_id];
            let formula = '';
            let kpi;
            
            if (clinicData && clinicData.operatorLetters[operator.id]) {
                let kpiLetter = clinicData.operatorLetters[operator.id].index + '9';
                formula = `${referenceWorksheetName}!${kpiLetter}`;
                kpi = workbook.getWorksheet(referenceWorksheetName).getCell(kpiLetter).result;
            }
            
            let incomes = clinicData ? findOperatorBySearchField(clinicData.totals, operator.id, 'for_incomes', 'total_incomes') : 0;
            let rate = getRate(operator, clinic, kpi, superviser);

            operatorRow['kpi' + clinic.clinic_id] = (formula.length != 0) ? {formula, result: kpi} : '';
            operatorRow['rate' + clinic.clinic_id] = rate;
            operatorRow['incomes' + clinic.clinic_id] = incomes ? incomes.total_incomes : 0;
            operatorRow['bonus' + clinic.clinic_id] = '';
        });
        operatorRow = worksheet.addRow(operatorRow);
    });

    // Get clinic columns letters
    let clinicLetters = {};

    data.clinics.forEach(clinic => {
        if (!clinicLetters[clinic.clinic_id]) {
            clinicLetters[clinic.clinic_id] = {
                kpi: worksheet.getColumn('kpi' + clinic.clinic_id).letter,
                rate: worksheet.getColumn('rate' + clinic.clinic_id).letter,
                incomes: worksheet.getColumn('incomes' + clinic.clinic_id).letter,
                bonus: worksheet.getColumn('bonus' + clinic.clinic_id).letter,
            }
        }
    });

    // Seed formulas and initial result for rate and bonus columns
    worksheet.eachRow((row, rowNumber) => {
        if (rowNumber < 3) {
            return;
        }

        data.clinics.forEach(clinic => {
            let kpiAddress = clinicLetters[clinic.clinic_id].kpi + rowNumber;
            let rateAddress = clinicLetters[clinic.clinic_id].rate + rowNumber;
            let incomeAddress = clinicLetters[clinic.clinic_id].incomes + rowNumber;
            
            //Formula and initial result for rate column
            let rateFormula = `IF(${kpiAddress} < ${RATE_MIN} / 100, ${clinic.rate_minimum}, IF(${kpiAddress} < ${RATE_MID} / 100, ${clinic.rate_medium}, ${clinic.rate_maximum}))`;
            let rateResult = worksheet.getCell(rateAddress).value;
            
            //Formula and initial result for bonus column
            let formula = rateAddress + '*' + incomeAddress;
            let result = rateResult * worksheet.getCell(incomeAddress).value;

            worksheet.getCell(clinicLetters[clinic.clinic_id].rate + rowNumber).value = {formula: rateFormula, result: rateResult};
            worksheet.getCell(clinicLetters[clinic.clinic_id].bonus + rowNumber).value = {formula, result};
        });
    })
}

export default (data) => {
    let workbook = new Excel.Workbook();
    data.clinics = clearSpaces(_.uniqBy(data.clinics, 'clinic_id'));
    let totals = _.groupBy(data.totals, 'clinic_id');
    let operatorsData = {};
    
    let worksheet = workbook.addWorksheet(__('Сводная'), {
        views: [
            {state: 'frozen', xSplit: 1, ySplit: 2}
        ]
    });
    
    data.clinics.forEach(clinic => {
        let clinicTotals = totals[clinic.clinic_id];
        if (clinicTotals) {
            operatorsData[clinic.clinic_id] = addClinicSheet(workbook, totals[clinic.clinic_id], clinic, data);
            operatorsData[clinic.clinic_id].totals = totals[clinic.clinic_id];
        }
    });
    
    seedSummarySheet(workbook, worksheet, data, operatorsData);
    return Promise.resolve(workbook);
}