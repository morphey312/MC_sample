import {numberFormat} from '@/services/format';
import * as IncomeBuilder from '@/services/report/income-builder';

const DISPLAY_SPECIALIZATION = [
    __('УРО'),
    __('ПРО'),
    __('ГИН'),
    __('ДЕРМ'),
    __('ХИР'),
    __('ЭНД'),
    __('ГАСТРО'),
    __('ЭНК'),
    __('ЛОР'),
    __('ТЕР'),
    __('АНАЛИЗЫ'),
    __('УЗИ'),
];

const OTHER_INCOMES = __('Другие доходы');

export default (results, daySheets) => {
    let tempData = [];
    let daysCount = {};
    let clinicNames = Object.keys(results).sort();
    let otherIncomesRow = {title: OTHER_INCOMES, valueFormatter: numberFormat};
    let periodTotalRow = {title: __('Итого доход за период'), valueFormatter: numberFormat};
    let clinicSessionsCount = getClinicSessions(daySheets);
    let sessionsRow = {title: __('Количество календарных дней'), 'col-total': clinicSessionsCount};

    clinicNames.forEach(clinicName => {
        let clinic = results[clinicName];
        if (!clinic) {
            return;
        }
        
        let specKeys = Object.keys(clinic.payments);
        sessionsRow[`col-${clinicName}`] = clinicSessionsCount;
        daysCount[clinicName] = clinicSessionsCount;
        otherIncomesRow[`col-${clinicName}`] = 0;
        otherIncomesRow['col-total'] = otherIncomesRow['col-total'] || 0;
        periodTotalRow[`col-${clinicName}`] = periodTotalRow[`col-${clinicName}`] || 0;
        periodTotalRow['col-total'] = periodTotalRow['col-total'] || 0;

        specKeys.forEach(specName => {
            if (DISPLAY_SPECIALIZATION.indexOf(specName) === -1) {
                let payed = IncomeBuilder.calcPayed(clinic.payments[specName]);
                otherIncomesRow[`col-${clinicName}`] += payed;
                otherIncomesRow['col-total'] += payed;
                periodTotalRow[`col-${clinicName}`] += payed;
                periodTotalRow['col-total'] += payed;
            }
        });
    });

    tempData.push(sessionsRow);

    DISPLAY_SPECIALIZATION.forEach(specName => {
        let specRow = {title: specName, valueFormatter: numberFormat};
        specRow['col-total'] = specRow['col-total'] || 0;
        periodTotalRow['col-total'] = periodTotalRow['col-total'] || 0;

        clinicNames.forEach(clinicName => {
            let clinic = results[clinicName];
            let paymentList = (clinic.payments && clinic.payments[specName]) ? clinic.payments[specName] : [];
            let payed = IncomeBuilder.calcPayed(paymentList);
            periodTotalRow[`col-${clinicName}`] = periodTotalRow[`col-${clinicName}`] || 0;

            specRow[`col-${clinicName}`] = payed;
            specRow['col-total'] += payed;
            periodTotalRow[`col-${clinicName}`] += payed;
            periodTotalRow['col-total'] += payed;
        });

        tempData.push(specRow);
    });

    DISPLAY_SPECIALIZATION.forEach((specName, index) => {
        let row = tempData[index + 1];
        row['col-percent'] = percentStr(row['col-total'], periodTotalRow['col-total']);
    });

    otherIncomesRow['col-percent'] = percentStr(otherIncomesRow['col-total'], periodTotalRow['col-total']);
    tempData.push(otherIncomesRow);
    tempData.push(periodTotalRow);
    tempData.push({});

    let isFirstRow = {title: __('Количество первичных за период'), 'col-total': 0};
    let repeatedRow = {title: __('Количество повторных за период'), 'col-total': 0};
    let patientsRow = {title: __('Итого общее кол-во пациентов'), 'col-total': 0};
    
    clinicNames.forEach(clinicName => {
        let isFirstCount = IncomeBuilder.getPatientVisitType(results[clinicName].appointments);
        let repeatedCount = IncomeBuilder.getPatientVisitType(results[clinicName].appointments, 0);
        let totalPatients = isFirstCount + repeatedCount;
        isFirstRow[`col-${clinicName}`] = isFirstCount;
        repeatedRow[`col-${clinicName}`] = repeatedCount;
        patientsRow[`col-${clinicName}`] = totalPatients;
        isFirstRow['col-total'] += isFirstCount;
        repeatedRow['col-total'] += repeatedCount;
        patientsRow['col-total'] += totalPatients;
    });

    isFirstRow['col-percent'] = percentStr(isFirstRow['col-total'], patientsRow['col-total']);
    repeatedRow['col-percent'] = percentStr(repeatedRow['col-total'], patientsRow['col-total']);

    tempData.push(isFirstRow);
    tempData.push(repeatedRow);
    tempData.push({});
    tempData.push(patientsRow);

    let dayilyPayedRow = {title: __('Среднедневная выручка'), 'col-total': 0, valueFormatter: numberFormat};
    let firstPatientPayedRow = {title: __('Средний чек (оборот/первичных)'), 'col-total': 0, valueFormatter: numberFormat};
    let patientRatioRow = {title: __('Соотношение вторичных/первичных'), 'col-total': 0, valueFormatter: numberFormat};
    let patientPayedRow = {title: __('Средний чек (оборот/итого первичных,вторичных)'), 'col-total': 0, valueFormatter: numberFormat};

    clinicNames.forEach(clinicName => {
        dayilyPayedRow['col-total'] = periodTotalRow['col-total'] / sessionsRow['col-total'];
        dayilyPayedRow[`col-${clinicName}`] = periodTotalRow[`col-${clinicName}`] / sessionsRow[`col-${clinicName}`];

        firstPatientPayedRow['col-total'] = periodTotalRow['col-total'] / isFirstRow['col-total'];
        firstPatientPayedRow[`col-${clinicName}`] = periodTotalRow[`col-${clinicName}`] / isFirstRow[`col-${clinicName}`];

        patientRatioRow['col-total'] = repeatedRow['col-total'] / isFirstRow['col-total'];
        patientRatioRow[`col-${clinicName}`] = repeatedRow[`col-${clinicName}`] / isFirstRow[`col-${clinicName}`];

        patientPayedRow['col-total'] = periodTotalRow['col-total'] / (repeatedRow['col-total'] + isFirstRow['col-total']);
        patientPayedRow[`col-${clinicName}`] = periodTotalRow[`col-${clinicName}`] / (repeatedRow[`col-${clinicName}`] + isFirstRow[`col-${clinicName}`]);
    });

    tempData.push({});
    tempData.push(dayilyPayedRow);
    tempData.push({});
    tempData.push(firstPatientPayedRow);
    tempData.push({});
    tempData.push(patientRatioRow);
    tempData.push({});
    tempData.push(patientPayedRow);

    console.log(tempData);
    return tempData;
}

const getClinicSessions = (daySheets) => {
    let clinic = _.maxBy(daySheets, 'sheets_count');
    return clinic ? clinic.sheets_count : 0;
}

const percentStr = (dividend, divider) => {
    return numberFormat(dividend / divider * 100) + '%';
}