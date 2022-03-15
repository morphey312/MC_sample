import CONSTANTS from '@/constants';
import {numberFormat} from '@/services/format';
import * as IncomeBuilder from '@/services/report/income-builder';

const FILL_COLORS = {};
FILL_COLORS['default'] = 'default';
FILL_COLORS[__('АНАЛИЗЫ')] = 'green';
FILL_COLORS[__('Анестезия')] = 'red';
FILL_COLORS[__('ГАСТРО')] = 'red';
FILL_COLORS[__('ДЕРМ')] = 'grey';
FILL_COLORS[__('ПРО')] = 'orange';
FILL_COLORS[__('ТРИХ')] = 'orange';
FILL_COLORS[__('УЗИ')] = 'blue';
FILL_COLORS[__('УРО')] = 'blue';
FILL_COLORS[__('УРО(жен)')] = 'green';
FILL_COLORS[__('ЭНД')] = 'green';
FILL_COLORS[__('Аванс')] = 'light-blue';
FILL_COLORS[__('Мед')] = 'light-blue';

const WORKSPACE = __('Кабинет');
const PREPAY = __('Аванс');

const getSpecializationList = (data = []) => {
    let result = {};
    data.forEach(row => {
        let spec = row.appointment_specialization ? __(row.appointment_specialization) : null;
        let specId = row.appointment_specialization_id;

        if (!IncomeBuilder.isSubsidiary(row) && IncomeBuilder.CARD_SPEC_EXCEPTIONS.indexOf(spec) !== -1) {
            spec = row.appointment_card_specialization ? __(row.appointment_card_specialization) : null;
            specId = row.appointment_card_specialization_id;
        }

        if (result[spec] == undefined) {
            result[spec] = specId;
        }
    });
    return result;
}

const getSpecializationTotals = (data = {}, valueField = 'payed_amount') => {
    let result = {};
    for (let spec in data) {
        let income = 0;
        let expense = 0;
        for (let doctor in data[spec].doctors) {
            data[spec].doctors[doctor].forEach(payment => {
                if (payment.type === CONSTANTS.PAYMENT.TYPES.INCOME) {
                    income += Number(payment[valueField]);
                } else if (payment.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
                    expense += Number(payment[valueField]);
                }
            });
        }
        result[spec] = {
            income: Number(income),
            expense: Number(expense),
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

const getColumns = (data) => {
    let columns = {};
    let doctorIds = [];
    console.log(data)
    Object.keys(data).sort().forEach(spec => {
        let doctors = Object.keys(data[spec].doctors);
        doctors.forEach(doctor => {
            if (doctor !== 'undefined') {
                if (doctor === WORKSPACE) {
                    return;
                }
                if (!columns[spec]) {
                    columns[spec] = {};
                }
                if (doctor === PREPAY) {
                    columns[spec][doctor] = PREPAY;
                } else {
                    columns[spec][doctor] = data[spec].doctors[doctor][0].doctor_name;
                    doctorIds.push(data[spec].doctors[doctor][0].doctor_id);
                }
            }
        });
    });
    return {columns, doctorIds};
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

const getDayPayments = (payments = [], day) => {
    return payments.filter(payment => {
        return new Date(payment.created_at).getDate() == day;
    });
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

const getTreatmentPercent = (totalPatients,  treatments = 0) => {
    return (totalPatients == 0) ? 0 : (treatments / totalPatients) * 100;
}

const getAvarageCheck = (total, first, repeated) => {
    let patientCount = Number(first) + Number(repeated);
    return (patientCount === 0) ? total : total / patientCount;
}

const addMissingSpecializationList = (specializations, appointments = []) => {
    appointments.forEach(row => {
        let spec = row.specialization_ ? __(row.specialization_name) : null;

        if (!specializations[spec]) {
            specializations[spec] = row.card_specialization_id;
        }
    });
    return specializations;
}

export default (payments, appointments, valueField = 'payed_amount') => {
    let results = IncomeBuilder.prepareData(payments);
    results = IncomeBuilder.addMissingAppointmentColumns(results, appointments);
    let specializations = getSpecializationList(payments);
    specializations = addMissingSpecializationList(specializations, appointments);
    let specializationTotals = getSpecializationTotals(results, valueField);
    let resultKeys = Object.keys(results);
    let tableData = [];
    let days = getDays();

    // Seed and add day payments rows
    days.forEach(day => {
        let row = {rowTitle: day};
        let summary = 0;
        for (let spec in results) {
            if (results[spec]) {
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
            }
        }

        row.summary = Number(summary);
        tableData.push(row);
    });

    //Total rows
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

    resultKeys.sort().forEach(spec => {
        if (results[spec]) {
            if (Object.keys(results[spec].doctors).length > 1) {
                let firstDoctor = true;

                for (let doctor in results[spec].doctors) {
                    let rowKey = `${spec}${doctor}`;
                    let payments = results[spec].doctors[doctor];
                    let columnAppointments = IncomeBuilder.filterAppointments(appointments, doctor, spec);
                    let firstPatients = IncomeBuilder.getPatientVisitType(columnAppointments);
                    let repeatedPatients = IncomeBuilder.getPatientVisitType(columnAppointments, 0);
                    let treatmentsTotal = IncomeBuilder.getTreatmentTotal(columnAppointments);
                    let incomePaymentsTotal = getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.INCOME, valueField);
                    let rowReturns = getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.EXPENSE, valueField);
                    let incomePaymentCount = payments.filter(item => item.type == CONSTANTS.PAYMENT.TYPES.INCOME).length;

                    //Add total row values
                    totalIncomes += Number(incomePaymentsTotal);
                    totalReturns += Number(rowReturns);
                    totalTreatments += Number(treatmentsTotal);

                    incomeTotalRow[rowKey] = numberFormat(Number(incomePaymentsTotal - rowReturns));
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

                        specTotalIncomeRow[rowKey] = numberFormat(Number(specializationTotals[spec].income - specializationTotals[spec].expense));
                        specTotalIsFirstRow[rowKey] = isFirstCount;
                        specTotalRepeatedRow[rowKey] = repeatedCount;
                        specTotalTreatmentRow[rowKey] = IncomeBuilder.getTreatmentTotal(specAppointments);
                    }
                    firstDoctor = false;
                }
            } else {
                for (let doctor in results[spec].doctors) {
                    let rowKey = `${spec}${doctor}`;
                    let payments = results[spec].doctors[doctor];
                    let columnAppointments = IncomeBuilder.filterAppointments(appointments, doctor, spec);
                    let firstPatients = IncomeBuilder.getPatientVisitType(columnAppointments);
                    let repeatedPatients = IncomeBuilder.getPatientVisitType(columnAppointments, 0);
                    let treatmentsTotal = IncomeBuilder.getTreatmentTotal(columnAppointments);
                    let incomePaymentsTotal = getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.INCOME, valueField);
                    let rowReturns = getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.EXPENSE, valueField);
                    let incomePaymentCount = payments.filter(item => item.type == CONSTANTS.PAYMENT.TYPES.INCOME).length;

                    //Add total row values
                    totalIncomes += Number(incomePaymentsTotal);
                    totalReturns += Number(rowReturns);
                    totalTreatments += Number(treatmentsTotal);

                    specTotal += Number(specializationTotals[spec].income);

                    incomeTotalRow[rowKey] = numberFormat(Number(incomePaymentsTotal - rowReturns));
                    returnsRow[rowKey] = Number(getPaymentsTotal(payments, CONSTANTS.PAYMENT.TYPES.EXPENSE, valueField));
                    isFirstRow[rowKey] = firstPatients;
                    isRepeatedRow[rowKey] = repeatedPatients;
                    treatmentRow[rowKey] = treatmentsTotal;
                    isFirstPercentRow[rowKey] = numberFormat(getPatientPercent(appointments, specializations[spec], firstPatients)) + '%';
                    treatmentPercentRow[rowKey] = numberFormat(getTreatmentPercent(firstPatients, treatmentsTotal)) + '%';
                    averageCheckRow[rowKey] = numberFormat((incomePaymentCount != 0) ? getAvarageCheck(incomePaymentsTotal, firstPatients, repeatedPatients) : 0);
                    specTotalIncomeRow[rowKey] = numberFormat(Number(specializationTotals[spec].income - specializationTotals[spec].expense));

                    let specAppointments = appointments.filter(row => (row.specialization_name ? __(row.specialization_name) : null) === spec);
                    let isFirstCount = specAppointments.filter(row => row.is_first === 1).length;
                    let repeatedCount = specAppointments.filter(row => row.is_first !== 1).length;
                    totalIsFirst += Number(isFirstCount);
                    totalRepeated += Number(repeatedCount);
                    specTotalIsFirstRow[rowKey] = isFirstCount;
                    specTotalRepeatedRow[rowKey] = repeatedCount;
                    specTotalTreatmentRow[rowKey] = IncomeBuilder.getTreatmentTotal(specAppointments);
                }
            }
        }
    });
    returnsRow.summary = numberFormat(Number(totalReturns));
    incomeTotalRow.summary = numberFormat(Number(totalIncomes - totalReturns));
    isFirstRow.summary = totalIsFirst;
    isRepeatedRow.summary = totalRepeated;
    treatmentRow.summary = totalTreatments;
    specTotalIncomeRow.summary = numberFormat(Number(specTotal - totalReturns));
    specTotalIsFirstRow.summary = totalIsFirst;
    specTotalRepeatedRow.summary = totalRepeated;
    specTotalTreatmentRow.summary = totalTreatments;
    let totalPatients = (totalIsFirst + totalRepeated);
    isFirstPercentRow.summary = numberFormat((totalIsFirst / totalPatients) * 100) + '%';
    treatmentPercentRow.summary = numberFormat((totalTreatments / totalIsFirst) * 100) + '%';
    averageCheckRow.summary = numberFormat(getAvarageCheck(incomeTotalRow.summary, totalIsFirst, totalRepeated));

    // Add returns row
    tableData.push(returnsRow);
    // Add income totals row
    tableData.push(incomeTotalRow);
    // Add patients is first row
    tableData.push(isFirstRow);
    // Add patients repeated row
    tableData.push(isRepeatedRow);
    // Add treatments row
    tableData.push(treatmentRow);
    // Add patients is first percent row
    tableData.push(isFirstPercentRow);
    // Add patients courses percent row
    tableData.push(treatmentPercentRow);
    // Add average check row
    tableData.push(averageCheckRow);
    // Add specialization total income row
    tableData.push(specTotalIncomeRow);
    // Add specialization is first total row
    tableData.push(specTotalIsFirstRow);
    // Add specialization repeated total row
    tableData.push(specTotalRepeatedRow);
    // Add specialization total treatments row
    tableData.push(specTotalTreatmentRow);

    let {columns, doctorIds} = getColumns(results);

    return {
        columns,
        doctorIds,
        tableData,
        colors: FILL_COLORS,
    };
}
