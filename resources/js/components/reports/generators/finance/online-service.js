import Excel from 'exceljs';
import {dateFormat} from '@/services/format';

const WIDTH_WIDE = 35;
const WIDTH_MID = 15;
const WIDTH_THIN = 10;
const ALIGNMENT = {
    vertical: 'middle', 
    horizontal: 'center',
    wrapText: true,
};
const FONT_BOLD = {
    bold: true,
    size: 10,
    font: 'Calibri',
};
const BORDER_STYLE = {style:'medium', color: {argb:'000'}};
const DATE_FORMAT = 'DD-MM-YYYY';

export default (data) => {
    const workbook = new Excel.Workbook();
    const worksheet = workbook.addWorksheet(__('Данные'), {
        views: [
            {state: 'frozen', ySplit: 1},
        ],
    });
    let groups = _.groupBy(data, 'service.appointment_id');
    
    worksheet.columns = [
        { header: __('Клиника'), key: 'appointment_clinic', width: WIDTH_THIN},
        { header: __('Карта'), key: 'card', width: WIDTH_THIN},
        { header: __('ФИО пациента'), key: 'patient', width: WIDTH_WIDE},
        { header: __('Врач'), key: 'appointment_doctor', width: WIDTH_WIDE},
        { header: __('Дата'), key: 'appointment_date', width: WIDTH_MID},
        { header: __('Услуга'), key: 'appointment_service', width: WIDTH_WIDE},
        { header: __('Сумма оплаты'), key: 'appointment_service_payed', width: WIDTH_MID},
        { header: __('Клиника'), key: 'clinic', width: WIDTH_THIN},
        { header: __('Врач'), key: 'doctor', width: WIDTH_WIDE},
        { header: __('Дата'), key: 'payment_date', width: WIDTH_MID},
        { header: __('Услуга'), key: 'service', width: WIDTH_WIDE},
        { header: __('Сумма оплаты'), key: 'amount', width: WIDTH_MID},
    ];

    let titleRow = worksheet.getRow(1);
    titleRow.alignment = ALIGNMENT;
    titleRow.font = FONT_BOLD;
    titleRow.height = 20;
    
    Object.keys(groups).forEach(group => {
        groups[group].forEach((item, index) => {
            let row = {};

            if (index === 0) {
                row.appointment_clinic = item.service.clinic_name;
                row.card = item.card_number;
                row.patient = item.patient_name;
                row.appointment_doctor = item.service.doctor_name;
                row.appointment_date = dateFormat(item.service.date, DATE_FORMAT);
                row.appointment_service = item.service.name;
                row.appointment_service_payed = item.service.payed;
            }

            row.clinic = item.clinic_name;
            row.doctor = item.doctor_name;
            row.payment_date = dateFormat(item.created, DATE_FORMAT);
            row.service = item.service_name;
            row.amount = Number(item.payed_amount);

            row = worksheet.addRow(row);

            if (index === (groups[group].length - 1)) {
                row.border = {bottom: BORDER_STYLE};
            }
        });
    });
    
    return Promise.resolve(workbook);
}