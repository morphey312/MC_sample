import Excel from 'exceljs';

const WIDTH = 15;
const getServicePayed = (list, service_id, clinic) => {
    let payedRow = list.find(row => row.service_id === service_id && row.for_payed === 1 && row.clinic_id === clinic)
    return payedRow ? Number(payedRow.sum_paid) : 0;
}

const PatientDeposit = (workbook, data = []) => {
    let worksheet = workbook.addWorksheet(__('Авансы пациентов'), {
        views: [
            {state: 'frozen', ySplit: 1},
        ],
    });
    worksheet.columns = [
        {header: __('Пациент'), key: 'patient', width: WIDTH},
        {header: __('Cумма аванса'), key: 'deposit_amount', width: WIDTH},
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
            deposit_amount: (Number(row.cost) - Number(row.act_service.insurance_payment) - Number(row.payed)) * -1,
        });
    });
}



export default (data) => {
    let workbook = new Excel.Workbook();
    PatientDeposit(workbook, data);
    return Promise.resolve(workbook);
}
