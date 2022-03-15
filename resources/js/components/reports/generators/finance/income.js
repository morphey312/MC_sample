import Excel from 'exceljs';
import moment from 'moment';
import CONSTANTS from '@/constants';
import {numberFormat} from '@/services/format';

const FILL_COLORS = {};

FILL_COLORS['CHECK UP'] = 'f38dee';
FILL_COLORS[__('АНАЛИЗЫ')] = '00ff00';
FILL_COLORS[__('Анестезия')] = 'c00000';
FILL_COLORS[__('ВАК')] = 'f38dee';
FILL_COLORS[__('ГАСТРО')] = 'ff0000';
FILL_COLORS[__('ГИН')] = 'ff99cc';
FILL_COLORS[__('ДЕРМ')] = 'ccccff';
FILL_COLORS[__('ИНФ')] = 'f38dee';
FILL_COLORS[__('КАРДИО')] = 'f38dee';
FILL_COLORS[__('ЛОР')] = 'f38dee';
FILL_COLORS[__('МАМ')] = 'f38dee';
FILL_COLORS[__('МАН')] = 'f38dee';
FILL_COLORS[__('НЕВРО')] = 'f38dee';
FILL_COLORS[__('НЕФР')] = 'f38dee';
FILL_COLORS[__('ОФТ')] = 'f38dee';
FILL_COLORS[__('Перевод')] = 'f38dee';
FILL_COLORS[__('ПРО')] = 'ffcc99';
FILL_COLORS[__('ПУЛЬМ')] = 'f38dee';
FILL_COLORS[__('Проц')] = 'f38dee';
FILL_COLORS[__('РЕН')] = 'f38dee';
FILL_COLORS[__('ТЕР')] = 'f38dee';
FILL_COLORS[__('ТРАВМ')] = 'f38dee';
FILL_COLORS[__('ТРИХ')] = 'ffc000';
FILL_COLORS[__('УЗИ')] = '00b0f0';
FILL_COLORS[__('УРО')] = 'ccffff';
FILL_COLORS[__('УРО(жен)')] = '92d050';
FILL_COLORS[__('ФД')] = 'f38dee';
FILL_COLORS[__('ФИЗИО')] = 'f38dee';
FILL_COLORS[__('ФИЗИО пл')] = 'f38dee';
FILL_COLORS[__('ХИР')] = 'f38dee';
FILL_COLORS[__('ЭКГ')] = 'f38dee';
FILL_COLORS[__('ЭНД')] = '00b050';
FILL_COLORS[__('ЭНК')] = 'f38dee';
FILL_COLORS[__('Аванс')] = 'ccffff';
FILL_COLORS[__('Мед')] = 'ccffff';

const DEFAULT_FILL = 'ccffff';
const BORDER_STYLE = {style:'medium', color: {argb:'000'}};
const ALIGNMENT = {vertical: 'middle', horizontal: 'center'};
const FONT_BOLD = {
    bold: true,
    size: 10,
    font: 'Calibri',
};
const WORKSPACE = __('Кабинет');
const ANALYSES = __('АНАЛИЗЫ');
const DIAGNOSTIC = __('ФД');
const ENDOSCOPY = __('ЭНД');
const RENTGEN = __('РЕН');
const UZI = __('УЗИ');
const FIZIO_PAYED = __('ФИЗИО пл');
const MANIPULATION = __('МАН');
const ANESTHESIA = __('Анестезия');
const KT = __('КТ');
const MASSAGE = __('МАСС');
const SUBSIDIARY_SPECIALIZATIONS = [ANALYSES, UZI, FIZIO_PAYED, ENDOSCOPY, DIAGNOSTIC, RENTGEN, MANIPULATION, KT];
const CARD_SPEC_EXCEPTIONS = [ENDOSCOPY, UZI, DIAGNOSTIC, RENTGEN, ANESTHESIA, KT, MASSAGE];

const isSubsidiary = (row) => {
    let specialization = row.appointment_specialization ? __(row.appointment_specialization) : null;
    return row.is_subsidiary &&
        SUBSIDIARY_SPECIALIZATIONS.indexOf(specialization) !== -1 &&
        row.appointment_card_specialization_id != row.appointment_specialization_id;
}

const getSpecializationList = (data = []) => {
    let result = {};
    data.forEach(row => {
        let spec = row.appointment_specialization ? __(row.appointment_specialization) : null;
        let specId = row.appointment_specialization_id;

        if (!isSubsidiary(row) && CARD_SPEC_EXCEPTIONS.indexOf(spec) !== -1) {
            spec = row.appointment_card_specialization ? __(row.appointment_card_specialization) : null;
            specId = row.appointment_card_specialization_id;
        }

        if (result[spec] == undefined) {
            result[spec] = specId;
        }
    });
    return result;
}

const getSpecializationTotals = (data = {}) => {
    let result = {};
    for (let spec in data) {
        let income = 0;
        let expense = 0;
        for (let doctor in data[spec].doctors) {
            data[spec].doctors[doctor].forEach(payment => {
                if (payment.type === CONSTANTS.PAYMENT.TYPES.INCOME) {
                    income += Number(payment.payed_amount);
                } else if (payment.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
                    expense += Number(payment.payed_amount);
                }
            });
        }
        result[spec] = {
            income: Number(income),
            expense: Number(expense),
            fill: FILL_COLORS[spec],
        }
    }
    return result;
}

const getDays = () => {
    let days = [];
    let start = 1;
    let end = 31;
    do {
        days.push(start);
        start++;
    } while (start <= end)
    return days;
}

const prepareData = (data) => {
    let results = {};
    data.forEach(row => {
        let spec;
        let appointmentSpecialization = row.appointment_specialization ? __(row.appointment_specialization) : null;

        if (row.appointment_specialization_id == row.appointment_card_specialization_id ||
            CARD_SPEC_EXCEPTIONS.indexOf(appointmentSpecialization) !== -1 ||
            isSubsidiary(row)) {
            spec = row.appointment_specialization ? __(row.appointment_specialization) : (row.is_deposit ? __('Аванс') : __('Мед'));
        } else {
            spec = row.appointment_card_specialization ? __(row.appointment_card_specialization) : null;
        }

        let doctorId = row.doctor_id ? row.doctor_id : (row.is_deposit ? __('Аванс') : '');

        if (results[spec] == undefined) {
            results[spec] = {doctors: {}}
            results[spec].doctors[doctorId] = [row];
        } else {
            if (results[spec].doctors[doctorId] == undefined) {
                results[spec].doctors[doctorId] = [row];
            } else {
                results[spec].doctors[doctorId].push(row);
            }
        }
    });
    return results;
}

const getColumns = (data) => {
    let commonStyles = {
        alignment: { vertical: 'middle', horizontal: 'center' },
    }
    let columns = [{header: 'P', key: 'rowTitle', width: 25, style: commonStyles}];

    Object.keys(data).sort().forEach(spec => {
        let doctors = Object.keys(data[spec].doctors);

        if (doctors.length > 1) {
            doctors.forEach(doctor => {
                if (doctor === WORKSPACE) {
                    return;
                }
                columns.push({header: spec, key: `${spec}${doctor}`, width: 15, style: commonStyles });
            })
        } else {
            if (doctors[0] === WORKSPACE) {
                 return;
            }
            columns.push({ header: spec, key: spec, width: 15, style: commonStyles});
        }
    });

    columns.push({header: 'T', key: 'summary', width: 20, style: {
            ...commonStyles,
            ...{
                fill: {
                    type: 'pattern',
                    pattern:'solid',
                    fgColor: {
                        argb: 'ffff99'
                    }
                }
            }
        }
    });
    return columns;
}

const getPaymentsTotal = (rows, type, valueField = 'payed_amount') => {
    let result = rows.reduce((total, row) => {
        if (row.type === type) {
            total += Number(row[valueField]);
        }
        return total;
    }, 0);
    return Number(numberFormat(result));
}

const getNameAbbr = (list = {}, id = null, defaultValue = '') => {
    let full_name = list[id] || defaultValue;

    if (_.isVoid(full_name)) {
        return '';
    }
    let parts = full_name.split(' ');
    return parts.reduce((output, part) => {
        return output + (part.length != 0 ? part[0].toUpperCase() : '');
    }, '');
}

const getDayPayments = (payments = [], day) => {
    return payments.filter(payment => {
        return moment(payment.created_at).date() == day;
    });
}

const filterAppointments = (appointments, doctorId, specName) => {
    specName = specName ? __(specName) : null;
    return appointments.filter(appointment => {
        let appointmentSpecialization = appointment.specialization_name ? __(appointment.specialization_name) : null;
        return appointment.doctor_id == doctorId &&
                appointmentSpecialization == __(specName) &&
                appointment.doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE;
    });
}

const getPatientVisitType = (appointments = [], isFirst = 1) => {
    return appointments.reduce((total, appointment) => {
        if (appointment.is_first == isFirst) {
            total++;
        }
        return total;
    }, 0);
}

const getPatientPercent = (appointments = [], specializationId, specIsFirst) => {
    let specializationTotal =  appointments.reduce((total, appointment) => {
        if ((appointment.specialization_id == specializationId) && (appointment.is_first != false)) {
            total++;
        }
        return total;
    }, 0);

    if (specIsFirst == 0) {
        return 0;
    }
    return (specIsFirst / specializationTotal) * 100;
}

const getTreatmentTotal = (appointments = []) => {
    return appointments.reduce((total, appointment) => {
        if (appointment.is_first_in_treatment_course === true) {
            total++;
        }
        return total;
    }, 0);
}

const getTreatmentPercent = (totalPatients,  treatments = 0) => {
    return (totalPatients == 0) ? 0 : (treatments / totalPatients) * 100;
}

const getFillColor = (list, key) => {
    return (list[key] !== undefined) ? list[key].fill : DEFAULT_FILL;
}

const getAvarageCheck = (total, first, repeated) => {
    let patientCount = Number(first) + Number(repeated);
    return (patientCount === 0) ? total : total / patientCount;
}

const getSundays = (date) => {
    let monthStart = moment(date).startOf('month');
    let monthEnd = moment(date).endOf('month');
    let sundays = [];
    while(monthStart <= monthEnd) {
        if (monthStart.day() === 0) {
            sundays.push(monthStart.date());
        }
        monthStart.add(1, 'days');
    }
    return sundays;
}

const addMissingAppointmentColumns = (results, appointments) => {
    appointments.forEach(row => {
        let spec = row.specialization_name ? __(row.specialization_name) : null;
        let isWorkspace = row.doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE;
        let doctorId = isWorkspace ? WORKSPACE : row.doctor_id;
        let doctor_name = isWorkspace ? WORKSPACE : row.doctor_name;

        if (!results[spec]) {
            results[spec] = {doctors: {}}
            results[spec].doctors[doctorId] = [{doctor_name}];
        } else {
            if (!results[spec].doctors[doctorId]) {
                results[spec].doctors[doctorId] = [{doctor_name}];
            }
        }
    });
    return results;
}

const addMissingSpecializationList = (specializations, appointments = []) => {
    appointments.forEach(row => {
        let spec = row.specialization_name ? __(row.specialization_name) : null;

        if (!specializations[spec]) {
            specializations[spec] = row.card_specialization_id;
        }
    });
    return specializations;
}

const seedWorksheet = (workbook, tabName, days, periodData, period) => {
    let sundays = getSundays(periodData[period].dateEnd);
    let data = periodData[period].data;
    let appointments = periodData[period].appointments;
    let specializations = getSpecializationList(data);
    let employees  = periodData.employees;
    let valueField  = periodData.valueField;

    //Add missing specializations from appointments
    specializations = addMissingSpecializationList(specializations, appointments);

    let worksheet = workbook.addWorksheet(tabName, {
        views: [
            {state: 'frozen', ySplit: 2, xSplit: 1},
        ],
    });

    let results = prepareData(data);
    //Add missing specializations and doctors from appointments
    results = addMissingAppointmentColumns(results, appointments);
    let resultKeys = Object.keys(results);

    worksheet.columns = getColumns(results);
    let specializationTotals = getSpecializationTotals(results);

    //Merge specialization cells and get specialization columns letters
    let specLetters = {};

    resultKeys.forEach(spec => {
        let doctorKeys = Object.keys(results[spec].doctors);

        if (doctorKeys.length > 1) {
            doctorKeys = doctorKeys.filter(doctor => doctor != WORKSPACE);
            let specStart = worksheet.getColumn(spec + doctorKeys[0]).letter;
            let specEnd = worksheet.getColumn(spec + doctorKeys[doctorKeys.length - 1]).letter;
            worksheet.mergeCells(specStart + 1 + ':' + specEnd + 1);
            //Add last specialization letters for style
            specLetters[spec] = {end: specEnd};
        } else {
            //Add last specialization letters for style
            if (doctorKeys.length != 0 && doctorKeys[0] != WORKSPACE) {
                specLetters[spec] = {end:  worksheet.getColumn(spec).letter};
            }
        }
    });

    //Total rows
    let doctorRow = {rowTitle: ''};
    let returnsRow = {rowTitle: 'MR'};
    let incomeTotalRow = {rowTitle: 'T'};
    let isFirstRow = {rowTitle: __('П')};
    let isRepeatedRow = {rowTitle: __('В')};
    let treatmentRow = {rowTitle: __('Л')};
    let isFirstPercentRow = {rowTitle: __('%П')};
    let treatmentPercentRow  = {rowTitle: __('%Л')};
    let averageCheckRow  = {rowTitle: __('Средний чек')};
    let specTotalIncomeRow  = {rowTitle: __('Общий доход по отделению')};
    let specTotalIsFirstRow  = {rowTitle: __('Первичные по отделению')};
    let specTotalRepeatedRow  = {rowTitle: __('Вторичные по отделению')};
    let specTotalTreatmentRow  = {rowTitle: __('Лечения по отделению')};

    /**
     * Seed
     * doctor names row,
     * returns row,
     * incomeTotal row,
     * patient is first row,
     * patient is repeated row,
     * treatements row,
     * patient is first percent row,
     * treatment percent row,
     * average check row,
     */
    let totalReturns = 0;
    let totalIncomes = 0;
    let totalIsFirst = 0;
    let totalRepeated = 0;
    let specTotal = 0;
    let totalTreatments = 0;

    resultKeys.forEach(spec => {
        if (results[spec]) {
            if (Object.keys(results[spec].doctors).length > 1) {
                let firstDoctor = true;
                for (let doctor in results[spec].doctors) {
                    let rowKey = `${spec}${doctor}`;
                    let payments = results[spec].doctors[doctor];
                    let columnAppointments = filterAppointments(appointments, doctor, spec);
                    let firstPatients = getPatientVisitType(columnAppointments);
                    let repeatedPatients = getPatientVisitType(columnAppointments, 0);
                    let treatmentsTotal = getTreatmentTotal(columnAppointments);
                    let incomePaymentsTotal = getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.INCOME, valueField);
                    let rowReturns = getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.EXPENSE, valueField);
                    let incomePaymentCount = payments.filter(item => item.type == CONSTANTS.PAYMENT.TYPES.INCOME).length;

                    //Add total row values
                    totalIncomes += Number(incomePaymentsTotal);
                    totalReturns += Number(rowReturns);
                    totalTreatments += Number(treatmentsTotal);

                    doctorRow[rowKey] = getNameAbbr(employees, doctor, payments[0].doctor_name);
                    incomeTotalRow[rowKey] = Number(incomePaymentsTotal - rowReturns);
                    returnsRow[rowKey] = Number(rowReturns);
                    isFirstRow[rowKey] = firstPatients;
                    isRepeatedRow[rowKey] = repeatedPatients;
                    treatmentRow[rowKey] = treatmentsTotal;
                    isFirstPercentRow[rowKey] = numberFormat(getPatientPercent(appointments, specializations[spec], firstPatients)) + '%';
                    treatmentPercentRow[rowKey] = numberFormat(getTreatmentPercent(firstPatients, treatmentsTotal)) + '%';
                    averageCheckRow[rowKey] = numberFormat((incomePaymentCount != 0) ? getAvarageCheck(incomePaymentsTotal, firstPatients, repeatedPatients) : 0);

                    if (firstDoctor === true) {
                        let specAppointments = appointments.filter(row => (row.specialization_name ? __(row.specialization_name) : null) === spec);
                        let isFirstCount = specAppointments.filter(row => row.is_first === 1).length;
                        let repeatedCount = specAppointments.filter(row => row.is_first !== 1).length;
                        totalIsFirst += Number(isFirstCount);
                        totalRepeated += Number(repeatedCount);
                        specTotal += Number(specializationTotals[spec].income);

                        specTotalIncomeRow[rowKey] = Number(specializationTotals[spec].income - specializationTotals[spec].expense);
                        specTotalIsFirstRow[rowKey] = isFirstCount;
                        specTotalRepeatedRow[rowKey] = repeatedCount;
                        specTotalTreatmentRow[rowKey] = getTreatmentTotal(specAppointments);
                    }
                    firstDoctor = false;
                }
            } else {
                for (let doctor in results[spec].doctors) {
                    let payments = results[spec].doctors[doctor];
                    let columnAppointments = filterAppointments(appointments, doctor, spec);
                    let firstPatients = getPatientVisitType(columnAppointments);
                    let repeatedPatients = getPatientVisitType(columnAppointments, 0);
                    let treatmentsTotal = getTreatmentTotal(columnAppointments);
                    let incomePaymentsTotal = getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.INCOME, valueField);
                    let rowReturns = getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.EXPENSE, valueField);
                    let incomePaymentCount = payments.filter(item => item.type == CONSTANTS.PAYMENT.TYPES.INCOME).length;

                    //Add total row values
                    totalIncomes += Number(incomePaymentsTotal);
                    totalReturns += Number(rowReturns);

                    specTotal += Number(specializationTotals[spec].income);
                    totalTreatments += Number(treatmentsTotal);

                    doctorRow[spec] = getNameAbbr(employees, doctor, payments[0].doctor_name);
                    incomeTotalRow[spec] = Number(incomePaymentsTotal - rowReturns);
                    returnsRow[spec] = Number(getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.EXPENSE, valueField));
                    isFirstRow[spec] = firstPatients;
                    isRepeatedRow[spec] = repeatedPatients;
                    treatmentRow[spec] = treatmentsTotal;
                    isFirstPercentRow[spec] = numberFormat(getPatientPercent(appointments, specializations[spec], firstPatients)) + '%';
                    treatmentPercentRow[spec] = numberFormat(getTreatmentPercent(firstPatients, treatmentsTotal)) + '%';
                    averageCheckRow[spec] = numberFormat((incomePaymentCount != 0) ? getAvarageCheck(incomePaymentsTotal, firstPatients, repeatedPatients) : 0);
                    specTotalIncomeRow[spec] = Number(specializationTotals[spec].income - specializationTotals[spec].expense);

                    let specAppointments = appointments.filter(row => (row.specialization_name ? __(row.specialization_name) : null) === spec);
                    let isFirstCount = specAppointments.filter(row => row.is_first === 1).length;
                    let repeatedCount = specAppointments.filter(row => row.is_first !== 1).length;
                    totalIsFirst += Number(isFirstCount);
                    totalRepeated += Number(repeatedCount);
                    specTotalIsFirstRow[spec] = isFirstCount;
                    specTotalRepeatedRow[spec] = repeatedCount;
                    specTotalTreatmentRow[spec] = getTreatmentTotal(specAppointments);
                }
            }
        }
    });
    // Add doctor names row as second row in document
    worksheet.addRow(doctorRow);

    //Set column total values
    doctorRow.summary = '';
    returnsRow.summary = Number(totalReturns);
    incomeTotalRow.summary = numberFormat(Number(totalIncomes - totalReturns));
    isFirstRow.summary = totalIsFirst;
    isRepeatedRow.summary = totalRepeated;
    treatmentRow.summary = totalTreatments;
    specTotalIncomeRow.summary = Number(specTotal - totalReturns);
    specTotalIsFirstRow.summary = totalIsFirst;
    specTotalRepeatedRow.summary = totalRepeated;
    specTotalTreatmentRow.summary = totalTreatments;
    let totalPatients = (totalIsFirst + totalRepeated);
    isFirstPercentRow.summary = numberFormat((totalIsFirst / totalPatients) * 100) + '%';
    treatmentPercentRow.summary = numberFormat((totalTreatments / totalIsFirst) * 100) + '%';
    averageCheckRow.summary = numberFormat(getAvarageCheck(incomeTotalRow.summary, totalIsFirst, totalRepeated));

    // Seed and add day payments rows
    days.forEach(day => {
        let row = {rowTitle: day};
        let summary = 0;
        for (let spec in results) {
            if (results[spec]) {
                if (Object.keys(results[spec].doctors).length > 1) {
                    for (let doctor in results[spec].doctors) {
                        let payments = results[spec].doctors[doctor];
                        let dayPayments = getDayPayments(payments, day);

                        if (dayPayments.length != 0) {
                            let total = getPaymentsTotal(dayPayments, CONSTANTS.PAYMENT.TYPES.INCOME, valueField);
                            let expense = getPaymentsTotal(dayPayments, CONSTANTS.PAYMENT.TYPES.EXPENSE, valueField);
                            summary += (Number(total) - Number(expense));
                            row[`${spec}${doctor}`] = Number(total);
                        } else {
                            row[`${spec}${doctor}`] = '';
                        }
                    }
                } else {
                    for (let doctor in results[spec].doctors) {
                        let payments = results[spec].doctors[doctor];
                        let dayPayments = getDayPayments(payments, day);

                        if (dayPayments.length != 0) {
                            let total = getPaymentsTotal(dayPayments, CONSTANTS.PAYMENT.TYPES.INCOME, valueField);
                            let expense = getPaymentsTotal(dayPayments, CONSTANTS.PAYMENT.TYPES.EXPENSE, valueField);
                            summary += (Number(total) - Number(expense));
                            row[spec] = Number(total);
                        } else {
                            row[spec] = '';
                        }
                    }
                }
            }
        }

        row.summary = Number(summary);
        row = worksheet.addRow(row);

        //Customize sundays rows styles
        if (sundays.indexOf(day) !== -1) {
            row.border = {
                top: BORDER_STYLE,
                bottom: BORDER_STYLE,
            };
            row.fill = {
                type: 'pattern',
                pattern:'solid',
                fgColor: {
                    argb: 'ffff99',
                },
            };
        }
    });

    // Add returns row
    worksheet.addRow(returnsRow);
    // Add income totals row
    worksheet.addRow(incomeTotalRow);
    // Add patients is first row
    worksheet.addRow(isFirstRow);
    // Add patients repeated row
    worksheet.addRow(isRepeatedRow);
    // Add treatments row
    worksheet.addRow(treatmentRow);
    // Add patients is first percent row
    worksheet.addRow(isFirstPercentRow);
    // Add patients courses percent row
    worksheet.addRow(treatmentPercentRow);
    // Add average check row
    worksheet.addRow(averageCheckRow);
    // Add specialization total income row
    worksheet.addRow(specTotalIncomeRow);
    // Add specialization is first total row
    worksheet.addRow(specTotalIsFirstRow);
    // Add specialization repeated total row
    worksheet.addRow(specTotalRepeatedRow);
    // Add specialization total treatments row
    worksheet.addRow(specTotalTreatmentRow);

    //Customize first two rows styles
    let firstRow = worksheet.getRow(1);
    let firstTwoRows = [firstRow, worksheet.getRow(2)];
    firstTwoRows.forEach(row => {
        let cells = row._cells.slice(1);
        cells.forEach(item => {
            let cell = worksheet.getCell(item._address);
            cell.fill = {
                type: 'pattern',
                pattern:'solid',
                fgColor: {
                    argb: getFillColor(specializationTotals, cell._column.header)
                }
            };
        })
    });


    firstRow.alignment = ALIGNMENT;
    firstRow.font = FONT_BOLD;
    firstRow.border = {
        bottom: BORDER_STYLE,
        right: BORDER_STYLE,
        left: BORDER_STYLE,
    };

    //Customize styles for totals rows
    [42, 43, 44, 45].forEach(num => {
        let row = worksheet.getRow(num);
        row.height = 30;
    });

    //Customize total income row styles
    let rowTotal = worksheet.getRow(35);
    rowTotal._cells.forEach(item => {
        let cell = worksheet.getCell(item._address);
        cell.border = {
            top: BORDER_STYLE,
            bottom: BORDER_STYLE,
        };
    });

    //Customize first column styles
    worksheet.eachRow((row, rowNumber) => {
        let firstCell = worksheet.getCell(row._cells[0]._address);
        firstCell.border = {
            right: BORDER_STYLE,
        };
        let lastCell = worksheet.getCell(row._cells[row._cells.length - 1]._address);
        lastCell.border = {
            left: BORDER_STYLE,
            bottom: BORDER_STYLE,
            top: BORDER_STYLE,
        };

        if (rowNumber > 1) {
            Object.keys(specLetters).forEach(spec => {
                let specLastCell = worksheet.getCell(specLetters[spec].end + rowNumber);
                specLastCell.border = {
                    ...specLastCell.border,
                    right: BORDER_STYLE,
                }
            });
        }
    });
}

const getPeriodTitle = (date) => {
    return moment(date).format('MMMM YYYY');
}

export default (data) => {
    let days = getDays();
    let workbook = new Excel.Workbook();
    let sheetTabs = [
        {
            title: getPeriodTitle(data.prevMonth.dateEnd),
            key: 'prevMonth',
        },
        {
            title: getPeriodTitle(data.current.dateEnd),
            key: 'current',
        },
    ];

    sheetTabs.forEach(tabName => {
        seedWorksheet(workbook, tabName.title, days, data, tabName.key);
    });

    return Promise.resolve(workbook);
}
