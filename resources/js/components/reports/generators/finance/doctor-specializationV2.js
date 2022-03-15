import Excel from 'exceljs';
import moment from 'moment';


const WIDTH_WIDE = 35;
const WIDTH_MID = 17;
const WIDTH_THIN = 12;
const ALIGNMENT = {vertical: 'middle', horizontal: 'center', wrapText: true,};
const FONT_BOLD = {
    bold: true,
    size: 10,
    font: 'Calibri',
};

const PERCENT_FORMAT = '0.0%';

const getSheetName = (filters) => {
    return moment(filters.date_end).format('MMMM YYYY');
}

const getColumns = () => {
    return [
        {header: __('Врач'), key:'doctor', width: WIDTH_WIDE},
        {header: __('Первичные'), key:'is_first', width: WIDTH_MID},
        {header: __('Повторные'), key:'repeated', width: WIDTH_THIN},
        {header: __('Процент повторных пациентов'), key:'repeated_percent', width: WIDTH_THIN},
        {header: __('Оборот'), key:'turnover', width: WIDTH_THIN},
        {header: __('Средний чек на консультации + обследование'), key:'mid_check', width: WIDTH_MID},
        {header: __('Консультации'), key:'consultation', width: WIDTH_THIN},
        {header: __('Процент от общего оборота'), key:'consultation_percent', width: WIDTH_THIN},
        {header: __('Диагностика/рентген'), key:'diagnostic', width: WIDTH_THIN},
        {header: __('Процент от общего оборота'), key:'diagnostic_percent', width: WIDTH_THIN},
        {header: __('Анализы'), key:'analyses', width: WIDTH_THIN},
        {header: __('Процент от общего оборота'), key:'analyses_percent', width: WIDTH_THIN},
        {header: __('УЗИ'), key:'uzi', width: WIDTH_THIN},
        {header: __('Процент от общего оборота'), key:'uzi_percent', width: WIDTH_THIN},
        {header: __('Лечение'), key:'treatment', width: WIDTH_THIN},
        {header: __('Процент от общего оборота'), key:'treatment_percent', width: WIDTH_THIN},
        {header: __('Итого оборот'), key:'total_turnover', width: WIDTH_THIN},
    ];
}

const getSummaryFormula = (firstRowNum, lastRowNum, letter) => {
    return `SUM(${letter}${firstRowNum}:${letter}${lastRowNum})`;
}

export default (data, filters) => {
    let workbook = new Excel.Workbook();
    const worksheet = workbook.addWorksheet(getSheetName(filters), {
        views: [
            {state: 'frozen', ySplit: 1,},
        ],
    });
    let rowNumber = 1;
    worksheet.columns = getColumns();
    let titleRow = worksheet.getRow(1);
    titleRow.alignment = ALIGNMENT;
    titleRow.height = 40;
    titleRow.font = FONT_BOLD;

    data.forEach(doctor => {
        let row = {};
        rowNumber++
        let formulas = {
            repeated_percent: worksheet.getColumn('repeated').letter + rowNumber + '/' + '(' +
                worksheet.getColumn('is_first').letter + rowNumber + '+' +
                worksheet.getColumn('repeated').letter + rowNumber  + ')',
            mid_check: '(' + worksheet.getColumn('turnover').letter + rowNumber + '-' +
                worksheet.getColumn('treatment').letter + rowNumber + ')' + '/' +'(' +
                worksheet.getColumn('is_first').letter + rowNumber + '+' +
                worksheet.getColumn('repeated').letter + rowNumber  + ')',
            consultation_percent: '(' + worksheet.getColumn('consultation').letter + rowNumber + '/' +
                worksheet.getColumn('turnover').letter + rowNumber + ')',
            diagnostic_percent: '(' + worksheet.getColumn('diagnostic').letter + rowNumber + '/' +
                worksheet.getColumn('turnover').letter + rowNumber + ')',
            analyses_percent: '(' + worksheet.getColumn('analyses').letter + rowNumber + '/' +
                worksheet.getColumn('turnover').letter + rowNumber + ')',
            uzi_percent: '(' + worksheet.getColumn('uzi').letter + rowNumber + '/' +
                worksheet.getColumn('turnover').letter + rowNumber + ')',
            treatment_percent: '(' + worksheet.getColumn('treatment').letter + rowNumber + '/' +
                worksheet.getColumn('turnover').letter + rowNumber + ')',

        }
        row['doctor'] = doctor.name;
        row['is_first'] = +doctor.first_count;
        row['repeated'] = +doctor.second_count;
        row['repeated_percent'] = {formula: formulas.repeated_percent, result: +doctor.secondPercentage/100};
        let repeated_percent = worksheet.getCell(worksheet.getColumn('repeated_percent').letter + (rowNumber - 1));
        repeated_percent.numFmt = PERCENT_FORMAT;
        row['turnover'] = +doctor.total;
        row['mid_check'] = {formula: formulas.mid_check, result: +doctor.averageConsultationsExploration};
        row['consultation'] = +doctor.totalConsultationPayments;
        row['consultation_percent'] = {formula: formulas.consultation_percent,result: +doctor.averageConsultations/100};
        let consultation_percent = worksheet.getCell(worksheet.getColumn('consultation_percent').letter + (rowNumber - 1));
        consultation_percent.numFmt = PERCENT_FORMAT;
        row['diagnostic'] = +doctor.totalXrayDiagnosticsPayments;
        row['diagnostic_percent'] = {formula:formulas.diagnostic_percent ,result:+doctor.averageDiagnostics/100};
        let diagnostic_percent = worksheet.getCell(worksheet.getColumn('diagnostic_percent').letter + (rowNumber - 1));
        diagnostic_percent.numFmt = PERCENT_FORMAT;
        row['analyses'] = +doctor.totalAnalisysPayments;
        row['analyses_percent'] = {formula:formulas.analyses_percent ,result: +doctor.averageAnalizys/100};
        let analyses_percent = worksheet.getCell(worksheet.getColumn('analyses_percent').letter + (rowNumber - 1));
        analyses_percent.numFmt = PERCENT_FORMAT;
        row['uzi'] = +doctor.totalSonarsPayments;
        row['uzi_percent'] = {formula: formulas.uzi_percent ,result: +doctor.averageSonars/100};
        let uzi_percent = worksheet.getCell(worksheet.getColumn('uzi_percent').letter + (rowNumber - 1));
        uzi_percent.numFmt = PERCENT_FORMAT;
        row['treatment'] = +doctor.totalTreatmentsPayments;
        row['treatment_percent'] = {formula:formulas.treatment_percent ,result: +doctor.averageTreatments/100};
        let treatment_percent = worksheet.getCell(worksheet.getColumn('treatment_percent').letter + (rowNumber - 1));
        treatment_percent.numFmt = PERCENT_FORMAT;
        row['total_turnover'] = +doctor.total;

        if (doctor.name === __('Итого')){
            let summary = worksheet.addRow(row);
            summary.font = FONT_BOLD
        } else  {
            worksheet.addRow(row);
        }

    });


    return Promise.resolve(workbook);
}
