import Excel from 'exceljs';

const WIDTH = 15;

const Services = (workbook, data = []) => {
    let worksheet = workbook.addWorksheet(__('Данный перечень услуг необходимо вручную разнести по пациентам'), {
        views: [
            {state: 'frozen', ySplit: 1},
        ],
    });
    worksheet.columns = [
        {header: __('Пациент'), key: 'patient', width: WIDTH},
        {header: __('Номер полиса'), key: 'policy_number', width: WIDTH},
        {header: __('Номер карты'), key: 'patient_card', width: WIDTH},
        {header: __('Дата записи'), key: 'appointment_date', width: WIDTH},
        {header: __('Стоимость по прайсу'), key: 'cost', width: WIDTH},
        {header: __('Франшиза, %'), key: 'franchise', width: WIDTH},
        {header: __('Оплачено, грн'), key: 'payed', width: WIDTH},
        {header: __('Количество услуг'), key: 'quantity', width: WIDTH},
        {header: __('Услуга'), key: 'service_name_detailed', width: WIDTH},
        {header: __('Диагноз'), key: 'diagnosis', width: WIDTH},
        {header: __('Номер акта'), key: 'act_number', width: WIDTH},
    ];

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

    data.forEach(row => {
        worksheet.addRow({
            patient: row.patient_name,
            policy_number: row.policy_number,
            patient_card: row.patient_card,
            appointment_date: row.appointment_date,
            cost: row.cost,
            franchise: row.franchise,
            quantity: row.quantity,
            service_name_detailed: row.service_name_detailed,
            diagnosis: row.diagnosis.join(','),
            act_number: row.act_service.number,
        });
    });
}



export default (data) => {
    let workbook = new Excel.Workbook();
    Services(workbook, data);
    return Promise.resolve(workbook);
}
