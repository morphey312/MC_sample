import Excel from 'exceljs';
import moment from 'moment';
import CONSTANTS from '@/constants';
import {numberFormat} from '@/services/format';

const WIDTH_WIDE = 35;
const WIDTH_MID = 17;
const WIDTH_THIN = 12;
const ALIGNMENT = {vertical: 'middle', horizontal: 'center', wrapText: true,};
const FONT_BOLD = {
    bold: true,
    size: 10,
    font: 'Calibri',
};
const BORDER_LIGHT_STYLES = {style: 'thin', color: {argb: 'FF000000'}};
const BORDER_AROUND = {
    top: BORDER_LIGHT_STYLES,
    left: BORDER_LIGHT_STYLES,
    bottom: BORDER_LIGHT_STYLES,
    right: BORDER_LIGHT_STYLES
};
const PERCENT_FORMAT = '0.0%';
const PAYMENT_DESTINATIONS = {
    TREATMENT: 4,
    CONSULTATION: 3,
    ANALYSES: 2,
    ANALYSES_KZK: 6,
    UZI: 5,
    RENTGEN: 9,
    DIAGNOSTIC: 10,
    /*TREATMENT: __('Лечение'),
    CONSULTATION: __('Консультация'),
    ANALYSES: __('Анализы'),
    ANALYSES_KZK: __('Анализы КЗК'),
    UZI: __('УЗИ'),
    RENTGEN: __('Рентген'),
    DIAGNOSTIC: __('Диагностика'),*/
}

const getSheetName = (filters) => {
    return moment(filters.date_end).format('MMMM YYYY');
}

const getClinicsData = (appointments, payments) => {
    let clinics = {};

    let appointmentClinics = getUniq(appointments, 'clinic');
    let paymentClinics = getUniq(payments, 'clinic_name');
    
    getUniq([...appointmentClinics, ...paymentClinics]).forEach(name => {
        if (!clinics[name]) {
            clinics[name] = {};
        }
        let clinicAppointments = appointments.filter(item => item.clinic === name);
        let clinicPayments = payments.filter(item => item.clinic_name === name);
        clinics[name] = getDoctorsClinicData(clinicAppointments, clinicPayments);
    });
    return clinics;
}

const getUniq = (list, field = null) => {
    if (field) {
        return _.uniqBy(list, field).map(item => item[field])
    }
    return _.uniq(list);
}

const getDoctorsClinicData = (appointments, payments) => {
    let appointmentDoctors = getUniq(appointments, 'doctor_id');
    let paymentDoctors = getUniq(payments, 'card_doctor_id');

    let result = {};
    getUniq([...appointmentDoctors, ...paymentDoctors]).forEach(doctor_id => {
        if (!result[doctor_id]) {
            result[doctor_id] = {};
        }
        let doctorAppointments = appointments.filter(item => item.doctor_id === doctor_id);
        let doctorPayments = payments.filter(item => item.card_doctor_id === doctor_id);

        result[doctor_id].name = doctorAppointments[0] ? doctorAppointments[0].doctor_name : doctorPayments[0].card_doctor_name;
        result[doctor_id].appointments = doctorAppointments;
        result[doctor_id].payments = doctorPayments;
    });
    return result;
}

const getColumns = () => {
    return [
        {header: __('Клиника'), key:'clinic', width: WIDTH_MID},
        {header: __('Врач'), key:'doctor', width: WIDTH_WIDE},
        {header: __('Первичные'), key:'is_first', width: WIDTH_MID},
        {header: __('Повторные'), key:'repeated', width: WIDTH_THIN},
        {header: __('Процент повторных пациентов'), key:'repeated_percent', width: WIDTH_THIN},
        {header: __('Оборот'), key:'turnover', width: WIDTH_THIN},
        {header: __('Средний чек на консультации + обследование'), key:'mid_check', width: WIDTH_MID},
        {header: __('Консультации'), key:'consultation', width: WIDTH_THIN},
        {header: __('Процент от общего оборота'), key:'consultation_precent', width: WIDTH_THIN},
        {header: __('Диагностика/рентген'), key:'diagnostic', width: WIDTH_THIN},
        {header: __('Анализы'), key:'analyses', width: WIDTH_THIN},
        {header: __('Процент от общего оборота'), key:'analyses_precent', width: WIDTH_THIN},
        {header: __('УЗИ'), key:'uzi', width: WIDTH_THIN},
        {header: __('Процент от общего оборота'), key:'uzi_precent', width: WIDTH_THIN},
        {header: __('Лечение'), key:'treatment', width: WIDTH_THIN},
        {header: __('Процент от общего оборота'), key:'treatment_precent', width: WIDTH_THIN},
        {header: __('Итого оборот'), key:'total_turnover', width: WIDTH_THIN},
    ];
}

const calcPaymentsByDestination = (payments, destination) => {
    let toCalc = [];

    if (_.isArray(destination)) {
        toCalc = payments.filter(item => destination.indexOf(item.payment_destination_id) !== -1);
    } else {
        toCalc = payments.filter(item => destination == item.payment_destination_id);
    }

    return toCalc.reduce((sum, item) => {
        if (item.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
            sum = sum - Number(item.payed_amount);
        } else {
            sum = sum + Number(item.payed_amount);
        }
        return sum;
    }, 0);
}

const getSummaryFormula = (firstRowNum, lastRowNum, letter) => {
    return `SUM(${letter}${firstRowNum}:${letter}${lastRowNum})`;
}

export default (data, filters) => {
    let workbook = new Excel.Workbook();
    let appointments = data.appointments;
    let payments = data.payments;

    const worksheet = workbook.addWorksheet(getSheetName(filters), {
        views: [
            {state: 'frozen', ySplit: 1,},
        ],
    });

    worksheet.columns = getColumns();
    let titleRow = worksheet.getRow(1);
    titleRow.alignment = ALIGNMENT;
    titleRow.height = 40;
    titleRow.font = FONT_BOLD;

    let clinicData = getClinicsData(appointments, payments);
    let clinicKeys = Object.keys(clinicData).sort();
    
    let summaries = {
        is_first: 0,
        repeated: 0,
        consultation: 0,
        diagnostic: 0,
        analyses: 0,
        uzi: 0,
        treatment: 0,
        turnover: 0,
    };

    clinicKeys.forEach(clinic => {
        let doctorIds = Object.keys(clinicData[clinic]);
        doctorIds.forEach((doctorId, index) => {
            let row = {};
            let doctorData = clinicData[clinic][doctorId];
            if (index === 0) {
                row['clinic'] = clinic;
            }

            let is_first = doctorData.appointments.filter(item => item.is_first == true).length;
            let repeated = doctorData.appointments.filter(item => item.is_first == false).length;
            let consultation = calcPaymentsByDestination(doctorData.payments, PAYMENT_DESTINATIONS.CONSULTATION);
            let diagnostic = calcPaymentsByDestination(doctorData.payments, [PAYMENT_DESTINATIONS.DIAGNOSTIC, PAYMENT_DESTINATIONS.RENTGEN]);
            let analyses = calcPaymentsByDestination(doctorData.payments, [PAYMENT_DESTINATIONS.ANALYSES, PAYMENT_DESTINATIONS.ANALYSES_KZK]);
            let uzi = calcPaymentsByDestination(doctorData.payments, PAYMENT_DESTINATIONS.UZI);
            let treatment = calcPaymentsByDestination(doctorData.payments, PAYMENT_DESTINATIONS.TREATMENT);

            row['doctor'] = doctorData.name;
            row['is_first'] = is_first;
            row['repeated'] = repeated;
            row['consultation'] = consultation;
            row['diagnostic'] = diagnostic;
            row['analyses'] = analyses;
            row['uzi'] = uzi;
            row['treatment'] = treatment;
            worksheet.addRow(row);

            summaries.is_first += is_first;
            summaries.repeated += repeated;
            summaries.consultation += consultation;
            summaries.diagnostic += diagnostic;
            summaries.analyses += analyses;
            summaries.uzi += uzi;
            summaries.treatment += treatment;
        });
    });

    let letters = {};

    worksheet.columns.forEach(column => {
        letters[column.key] = column.letter;
    });

    worksheet.eachRow((row, rowNumber) => {
        if (rowNumber === 1) {
            return false;
        }
        
        //Seed is first percent
        let IsFirstformula = (letters['repeated'] + rowNumber) + '/' + (letters['is_first'] + rowNumber);
        let isFirstResult = worksheet.getCell(letters['is_first'] + rowNumber).value;
        let repeatedResult = worksheet.getCell(letters['repeated'] + rowNumber).value;
        let isFirstresult = isFirstResult > 0 ? (repeatedResult / isFirstResult) : 0;
        let repeatedCell = worksheet.getCell(letters['repeated_percent'] + rowNumber);
        repeatedCell.value = {formula: IsFirstformula, result: isFirstresult};
        repeatedCell.numFmt = PERCENT_FORMAT;

        //Get row payment destination results for next calculations
        let consultation = worksheet.getCell(letters['consultation'] + rowNumber).value;
        let diagnostic = worksheet.getCell(letters['diagnostic'] + rowNumber).value;
        let analyses = worksheet.getCell(letters['analyses'] + rowNumber).value;
        let uzi = worksheet.getCell(letters['uzi'] + rowNumber).value;
        let treatment = worksheet.getCell(letters['treatment'] + rowNumber).value;

        //Seed turnover cell
        let turnoverResult = (consultation + diagnostic + analyses + uzi + treatment);
        let turnoverFormula = (letters['consultation'] + rowNumber) + '+' + (letters['diagnostic'] + rowNumber) + '+' + (letters['analyses'] + rowNumber) + '+' + (letters['uzi'] + rowNumber) + '+' + (letters['treatment'] + rowNumber);
        let turnoverCell = worksheet.getCell(letters['turnover'] + rowNumber);
        let turnoverTotalCell = worksheet.getCell(letters['total_turnover'] + rowNumber);
        turnoverCell.value = {formula: turnoverFormula, result: turnoverResult};
        turnoverTotalCell.value = {formula: turnoverFormula, result: turnoverResult};

        //Get row turnover result for next calculations
        let turnover = worksheet.getCell(letters['turnover'] + rowNumber).value.result;

        //Seed consultation_precent
        let consultationPrecent = consultation / turnover;
        let consultationFormula = (letters['consultation'] + rowNumber) + '/' + (letters['turnover'] + rowNumber);
        let consultationPrecentCell = worksheet.getCell(letters['consultation_precent'] + rowNumber);
        consultationPrecentCell.value = {formula: consultationFormula, result: consultationPrecent};
        consultationPrecentCell.numFmt = PERCENT_FORMAT;

        //Seed analyses_precent
        let analysesPrecent = analyses / turnover;
        let analysesFormula = (letters['analyses'] + rowNumber) + '/' + (letters['turnover'] + rowNumber);
        let analysesPrecentCell = worksheet.getCell(letters['analyses_precent'] + rowNumber);
        analysesPrecentCell.value = {formula: analysesFormula, result: analysesPrecent};
        analysesPrecentCell.numFmt = PERCENT_FORMAT;

        //Seed uzi_precent
        let uziPrecent = uzi / turnover;
        let uziFormula = (letters['uzi'] + rowNumber) + '/' + (letters['turnover'] + rowNumber);
        let uziPrecentCell = worksheet.getCell(letters['uzi_precent'] + rowNumber)
        uziPrecentCell.value = {formula: uziFormula, result: uziPrecent};
        uziPrecentCell.numFmt = PERCENT_FORMAT;

        //Seed uzi_precent
        let treatmentPrecent = treatment / turnover;
        let treatmentFormula = (letters['treatment'] + rowNumber) + '/' + (letters['turnover'] + rowNumber);
        let treatmentPrecentCell = worksheet.getCell(letters['treatment_precent'] + rowNumber)
        treatmentPrecentCell.value = {formula: treatmentFormula, result: treatmentPrecent};
        treatmentPrecentCell.numFmt = PERCENT_FORMAT;

        //Seed mid_check
        let midCheck = ((turnover - treatment) / worksheet.getCell(letters['is_first'] + rowNumber).value);
        let midCheckFormula = '(' + (letters['turnover'] + rowNumber) + '-' + (letters['treatment'] + rowNumber) + ')' + '/' + (letters['is_first'] + rowNumber);
        let midCheckCell = worksheet.getCell(letters['mid_check'] + rowNumber);
        midCheckCell.value = {formula: midCheckFormula, result: Number(numberFormat(midCheck))};

        //Add turnover to summary
        summaries.turnover += turnover;

        row.eachCell(cell => {
            if (cell._column.key !== 'clinic') {
                cell.border = BORDER_AROUND;
            }
        });
    });

    //Seed summary row
    let lastRowNum = worksheet.lastRow.number;
    let summaryRow = {
        is_first: {result: summaries.is_first, formula: getSummaryFormula(2, lastRowNum, letters.is_first)},
        repeated: {result: summaries.repeated, formula: getSummaryFormula(2, lastRowNum, letters.repeated)},
        turnover: {result: summaries.turnover, formula: getSummaryFormula(2, lastRowNum, letters.turnover)},
        consultation: {result: summaries.consultation, formula: getSummaryFormula(2, lastRowNum, letters.consultation)},
        diagnostic: {result: summaries.diagnostic, formula: getSummaryFormula(2, lastRowNum, letters.diagnostic)},
        analyses: {result: summaries.analyses, formula: getSummaryFormula(2, lastRowNum, letters.analyses)},
        uzi: {result: summaries.uzi, formula: getSummaryFormula(2, lastRowNum, letters.uzi)},
        treatment: {result: summaries.treatment, formula: getSummaryFormula(2, lastRowNum, letters.treatment)},
        total_turnover: {result: summaries.turnover, formula: getSummaryFormula(2, lastRowNum, letters.total_turnover)},
    };

    summaryRow = worksheet.addRow(summaryRow);
    summaryRow.font = FONT_BOLD;
    
    return Promise.resolve(workbook);
}