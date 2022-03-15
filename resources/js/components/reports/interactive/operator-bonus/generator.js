import {numberFormat} from '@/services/format';
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

const getClinicRate = (clinic, result) => {
    if (result) {
        if (result < RATE_MIN) {
            return clinic.rate_minimum;
        }

        if (result < RATE_MID) {
            return clinic.rate_medium;
        }
        return clinic.rate_maximum;
    }
    return 0;
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

const getOperatorTotalRepeated = (id, list) => {
    return list.reduce((total, row) => {
        if (row.operator_id == id && row.for_repeated == 1) {
            total += row.total_appointments;
        }
        return total;
    }, 0);
}

const roundVal = (value) => {
    return Math.round(value * 100);
}

const addClinicSheet = (totals, clinic, data) => {
    let results = [];
    let operators = data.operators;
    let uniqueOperators = getUniqueOperators(totals, operators);
    let operatorKeys = Object.keys(uniqueOperators);
    let totalsList = {};

    operatorKeys.forEach(id => {
        //Get total repeated in all clinics for each operator
        if (!totalsList[id]) {
            totalsList[id] = getOperatorTotalRepeated(id, data.totals);
        }
    });

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
        rowIsFirst['fact_' + id] = roundVal(findOperatorValue(totals, id));
        rowIsFirst['index_' + id] = clinic.appointment_norm > 0
            ? roundVal(WEIGHTS.IS_FIRST * rowIsFirst['fact_' + id] / clinic.appointment_norm)
            : 0,

        //first incomes
        rowIncomes['norm_' + id] = clinic.income_norm;
        rowIncomes['fact_' + id] = roundVal(findOperatorValue(totals, id, 'for_incomes', 'total_incomes'));
        rowIncomes['index_' + id] = clinic.income_norm > 0
            ? roundVal(WEIGHTS.INCOMES * rowIncomes['fact_' + id] / clinic.income_norm)
            : 0;

        //repeated appointments
        rowRepeated['norm_' + id] = is_night ? clinic.night_repeated_patient : clinic.day_repeated_patient;
        rowRepeated['fact_' + id] = totalsList[id];
        rowRepeated['index_' + id] = rowRepeated['norm_' + id] > 0
            ? roundVal(WEIGHTS.REPEATED * totalsList[id] / rowRepeated['norm_' + id])
            : 0;

        //operator wrapups
        rowWrapups['norm_' + id] = is_night ? clinic.night_post_call : clinic.day_post_call;
        rowWrapups['fact_' + id] = operator ? Math.round(operator.wrapups_average) : 0;
        rowWrapups['index_' + id] = rowWrapups['fact_' + id] > 0
            ? roundVal(WEIGHTS.POST_WRAP * rowWrapups['norm_' + id] / rowWrapups['fact_' + id])
            : 0;

        //operator evaluation
        rowEvaluation['norm_' + id] = clinic.evaluation_norm;
        rowEvaluation['fact_' + id] = (operator && operator.operator_bonus) ? Number(operator.operator_bonus.evaluation) : 0;
        rowEvaluation['index_' + id] = clinic.evaluation_norm > 0
            ? roundVal(WEIGHTS.EVALUATION * rowEvaluation['fact_' + id] / clinic.evaluation_norm)
            : 0;

        //operator mistakes
        let clinicData = (operator && operator.operator_bonus && operator.operator_bonus.clinics)
                        ? operator.operator_bonus.clinics.find(item => item.clinic_id == clinic.clinic_id)
                        : false;

        rowMistakes['norm_' + id] = clinic.mistakes_norm;
        rowMistakes['fact_' + id] = clinicData ? Number(clinicData.mistakes) : 0;
        rowMistakes['index_' + id] = rowMistakes['fact_' + id] > 0
            ? roundVal(WEIGHTS.MISTAKES * clinic.mistakes_norm / rowMistakes['fact_' + id])
            : 0;

        // summary row for style purpose
        rowSummary['index_' + id] = rowIsFirst['index_' + id] + rowIncomes['index_' + id] + rowRepeated['index_' + id]
            + rowWrapups['index_' + id] + rowEvaluation['index_' + id] + rowMistakes['index_' + id];
    });

    results.push(rowIsFirst);
    results.push(rowIncomes);
    results.push(rowWrapups);
    results.push(rowRepeated);
    results.push(rowEvaluation);
    results.push(rowMistakes);
    results.push(rowSummary);
    return {results, operatorKeys};
}

const seedSummarySheet = (data, operatorsData) => {
    let results = [];
    let superviser = data.positions.find(p => p.is_superviser == true);

    data.operators.forEach(operator => {
        let operatorRow = {full_name: operator.full_name};
        data.clinics.forEach(clinic => {
            let clinicData = operatorsData[clinic.clinic_id];
            if (clinicData) {
                let kpi;
                let results = clinicData ? clinicData.results : null;
                if (results) {
                    kpi = results[results.length - 1]['index_' + operator.id] || 0;
                }
                let rate = getRate(operator, clinic, kpi, superviser);
                let incomes = findOperatorBySearchField(clinicData.totals, operator.id, 'for_incomes', 'total_incomes');
                let totalIncomes = incomes ? incomes.total_incomes : 0;
                operatorRow['kpi_' + clinic.clinic_id] = kpi;
                operatorRow['rate_' + clinic.clinic_id] = rate;
                operatorRow['incomes_' + clinic.clinic_id] = totalIncomes;
                operatorRow['bonus_' + clinic.clinic_id] = (rate * totalIncomes);
            }
        });
        results.push(operatorRow);
    });
    return results;
}

export default (data, type = 'clinic') => {
    data.clinics = clearSpaces(_.uniqBy(data.clinics, 'clinic_id'));
    let totals = _.groupBy(data.totals, 'clinic_id');
    let operatorsData = {};

    data.clinics.forEach(clinic => {
        let clinicTotals = totals[clinic.clinic_id];
        if (clinicTotals) {
            operatorsData[clinic.clinic_id] = addClinicSheet(clinicTotals, clinic, data);
            operatorsData[clinic.clinic_id].totals = clinicTotals;
        }
    });
    if (type == 'clinic') {
        return operatorsData;
    }
    return seedSummarySheet(data, operatorsData);
}
