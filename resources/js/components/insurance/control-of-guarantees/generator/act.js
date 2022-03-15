import Excel from 'exceljs';
import {numberFormat} from '@/services/format';
import moment from 'moment';
import CONSTANTS from '@/constants';

const MonthsShort = [__('Січ'), __('Лют'), __('Бер'), __('Кві'), __('Тра'), __('Чер'), __('Лип'), __('Сер'), __('Вер'), __('Жов'), __('Лис'), __('Гру')];
const localMoment = moment();
localMoment.locale('uk', {
    months : [__('січня'), __('лютого'), __('березня'), __('квітня'), __('травня'), __('червня'), __('липня'), __('серпня'), __('вересня'), __('жовтня'), __('листопада'), __('грудня')],
    monthsShort: MonthsShort,
});

const ALIGNMENT = {vertical: 'middle', wrapText: true, horizontal: 'center'};
const COLUMN_STYLE = {
    alignment: ALIGNMENT,
    font: {
        size: 14,
    }
};
const ROW_NUM_START = 1;
const LETTER_A = 'A';
const LEFT_LETTER_START = LETTER_A;
const BORDER_LIGHT_STYLES = {style: 'thin', color: {argb: 'FF000000'}};
const BORDER_AROUND = {
    top: BORDER_LIGHT_STYLES,
    left: BORDER_LIGHT_STYLES,
    bottom: BORDER_LIGHT_STYLES,
    right: BORDER_LIGHT_STYLES
};

const getFormatedAmount = ( price = '', notRound = false) => {
    if (notRound) {
        return Number(price).toFixed(2);
    }
    return Math.round(Number(price));
}

const getServiceName = (service) => {
    if (service.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES
        && service.analysis_names && service.analysis_names.length != 0) {
        return service.analysis_names.join(', ');
    }
    return service.service_name_ua;
}

const getRows = (data = [], clinic) => {
    let rows = [];
    let groups = _.groupBy(data, 'patient_name');
    let groupKeys = Object.keys(groups).sort();

    groupKeys.forEach(patient => {
        groups[patient].forEach((item, index) => {
            let appointmentDate = moment(item.appointment_date).locale('uk', {monthsShort: MonthsShort});
            let row = [
                patient,
                item.policy_number,
                item.patient_card,
                appointmentDate.format('DD.MM.YYYY'),
                getFormatedAmount(numberFormat(item.cost), clinic.not_round_cost),
                getFormatedAmount(item.franchise, clinic.not_round_cost),
                getFormatedAmount(item.payed, clinic.not_round_cost),
                item.quantity,
                getServiceName(item),
                _.isNull(item.note) ? ' ' : _.isNull(item.note.task) ? ' ' : item.note.task,
                _.isNull(item.note) ? ' ' : _.isNull(item.note.note) ? ' ' : item.note.note,
            ];
            rows.push(row);
        });
    });
    return rows;
}
export default (data = [], requisites = {}) => {
    let clinic = requisites.clinic || {};
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Сводная'), {
        autoFilter: false,
        pageSetup: {
            paperSize: 9,
            fitToPage: true,
        },
    });

    worksheet.columns = [
        {header: '', key: 'patient', width: 30, style: COLUMN_STYLE},
        {header: '', key: 'policy_number', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'patient_card', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'date', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'cost', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'franchise', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'payed', width: 16, style: COLUMN_STYLE},
        {header: '', key: 'quantity', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'service', width: 39, style: COLUMN_STYLE},
        {header: '', key: 'note', width: 39, style: COLUMN_STYLE},
        {header: '', key: 'task', width: 39, style: COLUMN_STYLE},
    ];

    let rows = getRows(data, clinic);
    const table = worksheet.addTable({
        name: 'Act',
        ref: `${LEFT_LETTER_START}${ROW_NUM_START}`,
        headerRow: true,
        style: {
            theme: null,
        },
        columns: [
            {name: __('П. І. Б. Застрахованої особи')},
            {name:  __('Номер поліса')},
            {name:  __('Амбулаторна карта')},
            {name:  __('Дата')},
            {name:  __('Вартість згідно прайсу, грн.')},
            {name:  __('Франшиза, %')},
            {name:  __('Сплачено за франшизою, грн.')},
            {name:  __('Кількість послуг')},
            {name:  __('Найменування послуг згідно прайсу')},
            {name:  __('Задача')},
            {name:  __('Примечание')},
        ],
        rows,
    });
    table.commit();

    //Table header styles
    let tableHeaderRow = worksheet.getRow(ROW_NUM_START);
    tableHeaderRow.font = {size: 14, bold: true,};
    tableHeaderRow.height = 75;

    //Table rows styles
    let tableRowsTotal = ROW_NUM_START + table.height;
    for (let rowNum = ROW_NUM_START; rowNum <= tableRowsTotal; rowNum++) {
        let row = worksheet.getRow(rowNum);
        row.eachCell(cell => {
            cell.border = BORDER_AROUND;
        });

        if (rowNum > ROW_NUM_START) {
            row.height = 70;
        }
    }

    return Promise.resolve({book: workbook});
}
