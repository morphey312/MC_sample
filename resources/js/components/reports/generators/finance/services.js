import Excel from 'exceljs';
import {numberFormat} from "@/services/format";

const WIDTH = 15;

const seedServiceList = (workbook, data = []) => {
    let worksheet = workbook.addWorksheet(__('Услуги'), {
        views: [
            {state: 'frozen', ySplit: 1},
        ],
    });

    let services = data.filter(row => row.for_service === 1);
    let paymentsIncome = data.filter(row => row.for_payed === 1 && row.type === 'income');
    let paymentsExpense = data.filter(row => row.for_payed === 1 && row.type === 'expense');
    let servicesResult = [];

    paymentsIncome.forEach(payment => {
        let service = services.find(item => {
            return item.service_id === payment.service_id &&
                   item.clinic_id === payment.clinic_id &&
                   payment.appointment_service_id === item.appointment_service_id
        })
        let serviceIndex = services.findIndex(item => {
            return item.service_id === payment.service_id &&
                   item.clinic_id === payment.clinic_id &&
                   payment.appointment_service_id === item.appointment_service_id
        })

        if(!service) {
            services.push(payment)
        } else if(service.for_payed === 0 && payment.appointment_service_id === service.appointment_service_id) {
            services.splice(serviceIndex, 1, payment)
        } else {
            if(payment.appointment_service_id !== service.appointment_service_id) {
                service.quantity = +service.quantity + +payment.quantity;
                service.sum_sold = +service.sum_sold + +payment.sum_sold;
            }
            service.sum_paid = +service.sum_paid + +payment.sum_paid;
        }
    });

    paymentsExpense.forEach(payment => {
        let service = services.find(item => {
            return item.service_id === payment.service_id &&
                item.clinic_id === payment.clinic_id &&
                payment.appointment_service_id === item.appointment_service_id
        });

        if(service) {
            service.sum_paid = +service.sum_paid - +payment.sum_paid;
        }
    })

    services.map(service => {
        let row = servicesResult.find(item => item.service_id === service.service_id && item.clinic_id === service.clinic_id)

        if(!row) {
            servicesResult.push(service);
        }else {
            row.quantity = +row.quantity + +service.quantity;
            row.sum_sold = +row.sum_sold + +service.sum_sold;
            row.sum_paid = +row.sum_paid + +service.sum_paid;
        }
    })

    servicesResult = _.sortBy(servicesResult, 'specialization');
    worksheet.columns = [
        {header: __('Клиника'), key: 'clinic', width: WIDTH},
        {header: __('Название услуги'), key: 'service_name', width: 50},
        {header: __('Специализация'), key: 'specialization', width: WIDTH},
        {header: __('Количество'), key: 'quantity', width: WIDTH},
        {header: __('Стоимость проданных услуг'), key: 'sum_sold', width: WIDTH},
        {header: __('Сумма оплаченных услуг'), key: 'sum_paid', width: WIDTH},
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
    servicesResult.forEach(service => {
        worksheet.addRow({
            clinic: service.clinic,
            service_name: service.name,
            specialization: service.specialization,
            quantity: parseInt(service.quantity),
            sum_sold: _.isFilled(service.sum_sold) ? parseInt(service.sum_sold): 0,
            sum_paid: parseInt(service.sum_paid),
        });
    });
}

const seedAnalysisList = (workbook, data) => {
    let worksheet = workbook.addWorksheet(__('Анализы'), {
        views: [
            {state: 'frozen', ySplit: 1},
        ],
    });
    let analyses = _.sortBy(data.filter(row => row.for_payments === 0), 'clinic');
    let payments = data.filter(row => row.for_payments === 1);

    worksheet.columns = [
        {header: __('Клиника'), key: 'clinic', width: WIDTH},
        {header: __('Название анализа'), key: 'analysis_name', width: 50},
        {header: __('Количество'), key: 'quantity', width: WIDTH},
        {header: __('Стоимость проданных анализов'), key: 'sum_sold', width: WIDTH},
        {header: __('Сумма оплаченных анализов'), key: 'sum_paid', width: WIDTH},
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

    analyses.forEach(analysis => {
        worksheet.addRow({
            clinic: analysis.clinic,
            analysis_name: analysis.name,
            quantity: analysis.quantity,
            sum_sold: 0,
            sum_paid: 0,
        });
    });

    payments.forEach(clinic => {
        let row = worksheet.addRow({
            clinic: clinic.clinic,
            sum_sold: clinic.sum_sold,
            sum_paid: clinic.sum_paid
        });
        row.font = {
            bold: true,
            size: 10,
        }
    });
}

export default (data) => {
    let workbook = new Excel.Workbook();
    seedServiceList(workbook, data.services);
    seedAnalysisList(workbook, data.analyses);
    return Promise.resolve(workbook);
}
