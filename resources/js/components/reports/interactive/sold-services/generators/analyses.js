import Excel from 'exceljs';
import CONSTANTS from "@/constants";

export default (data, laboratory_id = null, type = 'table') => {
    if (type == 'table') {
        return getReportData(data, laboratory_id);
    }
    if (type == 'export') {
        return getExportData(data);
    }
}
const getExportData = (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Анализы'));

    worksheet.columns = [
        { header: __('Название анализа'), key: 'analysis' },
        { header: __('Клиника'), key: 'clinic' },
        { header: __('Код анализа лаборатории'), key: 'analysis_code' },
        { header: __('Код анализа клиники'), key: 'analysis_code_clinic' },
        { header: __('Лаборатория'), key: 'laboratory' },
        { header: __('Количество'), key: 'count' },
        { header: __('Продано на сумму'), key: 'payed' },
        { header: __('Оплачено'), key: 'payments' },
    ];

    data.forEach(row => {

        worksheet.addRow({
            analysis: row.analysis,
            clinic: row.clinic || '',
            analysis_code: row.analysis_code || '',
            analysis_code_clinic: row.analysis_code_clinic || '',
            laboratory: row.laboratory || '',
            count: row.count,
            payed: row.payed,
            payments: row.payments,
        });
    });

    return workbook;
}
const getReportData = (data, laboratory_id) => {
    let byClinic = {};
    let byAnalysis = {};

    data.forEach((row) => {
        row.services.forEach(service => {
            let isAnalysisService = service.service_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES;
            if(isAnalysisService) {
                let analysis = laboratory_id ? service.analysis.filter(analyse => laboratory_id.includes(analyse.laboratory.laboratory_id)) : service.analysis;

                analysis.forEach((analysisRow) => {
                    let clinic = row.clinic_id;
                    let analysis;
                    let clinicData;

                    let payed = 0;
                    service.payments.forEach((payment) => {
                        payed += payment.payed_amount;
                    })
                    if (byAnalysis[analysisRow.id] === undefined) {
                        analysis = byAnalysis[analysisRow.id] = {
                            analysis_code: analysisRow.laboratory.laboratory_code,
                            analysis_code_clinic: analysisRow.laboratory.laboratory_code,
                            laboratory: analysisRow.laboratory.laboratory_name || '',
                            analysis: analysisRow.name,
                            count: service.qty,
                            sum_sold: service.cost * service.qty,
                            payed: payed,
                            byClinic: {}
                        }
                    } else {
                        analysis = byAnalysis[analysisRow.id]
                        analysis.count += service.qty;
                        analysis.sum_sold += service.cost * service.qty;
                        analysis.payed += payed;
                    }

                    if (analysis.byClinic[clinic] === undefined) {
                        clinicData = analysis.byClinic[clinic] = {
                            count: service.qty,
                            sum_sold: service.cost * service.qty,
                            payed: payed,
                        };
                    } else {
                        clinicData = analysis.byClinic[clinic]
                        clinicData.count += service.qty;
                        clinicData.sum_sold += service.cost * service.qty;
                        clinicData.payed += payed;
                    }
                });
            }
        });
    });

    return {byClinic, byAnalysis};
}
