import Excel from 'exceljs';

export default (data, specialization_id = null, type = 'table') => {
    if (type == 'table') {
        return getReportData(data, specialization_id);
    }
    if (type == 'export') {
        return getExportData(data);
    }
}
const getExportData = (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Услуги'));

    worksheet.columns = [
        { header: __('Название услуги'), key: 'service' },
        { header: __('Клиника'), key: 'clinic' },
        { header: __('Специализация'), key: 'specialization' },
        { header: __('Количество'), key: 'count' },
        { header: __('Продано на сумму'), key: 'payed' },
        { header: __('Оплачено'), key: 'payments' },
    ];

    data.forEach(row => {

        worksheet.addRow({
            service: row.service || '',
            clinic: row.clinic || '',
            specialization: row.specialization,
            count: row.count,
            payed: row.payed,
            payments: row.payments,
        });
    });

    return workbook;
}
const getReportData = (data, specialization_id) => {
    let byClinic = {};
    let byService = {};

    data.forEach((row) => {
        let services = specialization_id ? row.services.filter(analyse => specialization_id.includes(analyse.specialization_id)) : row.services;
        services.forEach((serviceRow) => {
            let clinic = row.clinic_id;
            let service;
            let clinicData;

            let payed = 0;
            serviceRow.payments.forEach((payment) => {
                payed += payment.payed_amount;
            })
            if (byService[serviceRow.service_id] === undefined) {
                service = byService[serviceRow.service_id] = {
                    name: serviceRow.name,
                    count: serviceRow.qty,
                    sum_sold: serviceRow.cost * serviceRow.qty,
                    payed: payed,
                    specialization: serviceRow.specialization.id,
                    byClinic: {}
                }
            } else {
                service = byService[serviceRow.service_id];
                service.count += serviceRow.qty;
                service.sum_sold += serviceRow.cost * serviceRow.qty;
                service.payed += payed;
            }

            if (service.byClinic[clinic] === undefined) {
                clinicData = service.byClinic[clinic] = {
                    count: serviceRow.qty,
                    sum_sold: serviceRow.cost * serviceRow.qty,
                    payed: payed,
                };
            } else {
                clinicData = service.byClinic[clinic]
                clinicData.count += serviceRow.qty;
                clinicData.sum_sold += serviceRow.cost * serviceRow.qty;
                clinicData.payed += payed;
            }
        });
    });

    return {byClinic, byService};
}
