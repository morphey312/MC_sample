import Excel from 'exceljs';
import {dateFormat, listFormat} from '@/services/format';
import handbook from '@/services/handbook';

const COLUMN_STYLE = {
    alignment: {vertical: 'middle', wrapText: true},
    border: {
        right: {style:'medium', color: {argb:'000'}}
    }
};
const COLUMN_MIN_WIDTH = 20;
const COLUMN_MAX_WIDTH = 40;

export default (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Данные'), {
        views: [
            {state: 'frozen', ySplit: 1},
        ],
    });
    
    worksheet.columns = [{
        header: __('Дата первичного посещения'), 
        key: 'date',
        width: COLUMN_MIN_WIDTH, 
        style: COLUMN_STYLE,
    }, {
        header: __('Отделение'), 
        key: 'specialization_name',
        width: COLUMN_MIN_WIDTH, 
        style: COLUMN_STYLE,
    }, {
        header: __('Лечащий врач'), 
        key: 'doctor_name',
        width: COLUMN_MIN_WIDTH, 
        style: COLUMN_STYLE,
    }, {
        header: __('Номер карты'), 
        key: 'card_number',
        width: COLUMN_MIN_WIDTH, 
        style: COLUMN_STYLE,
    }, {
        header: __('Диагноз'), 
        key: 'diagnosis',
        width: COLUMN_MAX_WIDTH, 
        style: COLUMN_STYLE,
    }, {
        header: __('Причина невзятия лечения'), 
        key: 'rejection_reason',
        width: COLUMN_MIN_WIDTH, 
        style: COLUMN_STYLE,
    }, {
        header: __('Дата следующего посещения'), 
        key: 'next_date',
        width: COLUMN_MIN_WIDTH, 
        style: COLUMN_STYLE,
    }, {
        header: __('Назначения'), 
        key: 'assignments',
        width: COLUMN_MAX_WIDTH, 
        style: COLUMN_STYLE,
    }];
    worksheet.properties.defaultRowHeight = 30;
    
    let titleRow = worksheet.getRow(1);
    titleRow.height = 30;
    titleRow.eachCell(cell => {
        cell.alignment  = {
            wrapText: true,
            horizontal: 'center',
            vertical: 'middle',
        };
        cell.font = {
            bold: true,
            size: 10,
        }
    });

    data.forEach((row) => {
        worksheet.addRow({
            date: dateFormat(row.date),
            specialization_name: row.specialization_name,
            doctor_name: row.doctor_name,
            card_number: row.card_number,
            rejection_reason: handbook.getOption('reason_refusing_treatment', row.rejection_reason),
            diagnosis: listFormat(row.diagnosis),
            assignments: listFormat(row.assignments),
            next_date: dateFormat(row.next_date),
        });
    });
    
    return Promise.resolve(workbook);
}