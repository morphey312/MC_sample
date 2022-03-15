import Excel from 'exceljs';
import CONSTANTS from '@/constants';

const ALIGNMENT = {vertical: 'middle', horizontal: 'center'};
const BORDER_LIGHT_STYLES = {style: 'thin', color: {argb: 'FF000000'}};
const BORDER_AROUND = {
    top: BORDER_LIGHT_STYLES,
    left: BORDER_LIGHT_STYLES,
    bottom: BORDER_LIGHT_STYLES,
    right: BORDER_LIGHT_STYLES
};
const BANK_PAYMENT_METHOD = 5;

const getPayedParts = (appointmentServices = []) => {
    let patientPayed = 0;
    let companyPayed = 0;

    appointmentServices.forEach(service => {
        service.payments.forEach(payment => {
            let amount = Number(payment.payed_amount);
            if (payment.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
                if (payment.payment_method_id === BANK_PAYMENT_METHOD) {
                    companyPayed -= amount;
                } else {
                    patientPayed -= amount;
                }
            } else {
                if (payment.payment_method_id === BANK_PAYMENT_METHOD) {
                    companyPayed += amount;
                } else {
                    patientPayed += amount;
                }
            }
        });
    });
    return {company: companyPayed, patient: patientPayed};
}

const getTotalPayed = (list = []) => {
    return list.reduce((sum, item) => {
        return sum + item.payments.reduce((sum, payment) => {
            if (payment.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
                return sum - Number(payment.payed_amount);
            }
            return sum + Number(payment.payed_amount);
        }, 0);
    }, 0);
}

const getSummaryRow = (data, columnKey) => {
    let totals = getTotals(data);
    let row = {
        is_first: totals.is_first,
        payed: totals.payed,
    };
    row[columnKey] = __('Итого');
    return row;
}

const getTotals = (list) => {
    let is_first = 0;
    let payed = 0;
    Object.keys(list).forEach(item => {
        is_first += Number(list[item].is_first);
        payed += Number(list[item].payed);
    });
    return {is_first, payed};
}

const seedCompanySheet = (workbook, companyTotals) => {
    let worksheet = workbook.addWorksheet(__('Страховые'), {
        views: [
            {state: 'frozen', ySplit: 1}
        ]
    });
    worksheet.columns = [
        {header: __('Страховая компания'), key: 'insurer', width: 20,},
        {header: __('Первичные пациенты'), key: 'is_first', width: 12,},
        {header: __('Доход'), key: 'payed', width: 12,},
    ];
    
    Object.keys(companyTotals).sort().forEach(companyName => {
        let row = {
            insurer: companyName,
            is_first: companyTotals[companyName].is_first,
            payed: companyTotals[companyName].payed,
        };
        worksheet.addRow(row);
    });
    let summaryRow = worksheet.addRow(getSummaryRow(companyTotals, 'insurer'));
    summaryRow.font = {bold: true,size: 10,font: 'Calibri',};
}

const seedClinicSheet = (workbook, clinicTotals) => {
    let worksheet = workbook.addWorksheet(__('Клиники'), {
        views: [
            {state: 'frozen', ySplit: 1}
        ]
    });
    worksheet.columns = [
        {header: __('Клиника'), key: 'clinic', width: 20,},
        {header: __('Первичных'), key: 'is_first', width: 12,},
        {header: __('Доход'), key: 'payed', width: 12,},
    ];
    
    Object.keys(clinicTotals).sort().forEach(clinicName => {
        let row = {
            clinic: clinicName,
            is_first: clinicTotals[clinicName].is_first,
            payed: clinicTotals[clinicName].payed,
        };
        worksheet.addRow(row);
    });
    let summaryRow = worksheet.addRow(getSummaryRow(clinicTotals, 'clinic'));
    summaryRow.font = {bold: true,size: 10,font: 'Calibri',};
}

const getDataKeys = (list1 = [], list2 = []) => {
    return _.uniq([
        ...list1,
        ...list2
    ]).sort();
}

export default (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Общие'), {
        views: [
            {state: 'frozen', ySplit: 2}
        ]
    });
    let columns = [
        {header: __('Страховая компания'), key: 'insurer', width: 30, height: 15,style: {border: BORDER_AROUND}},
        {header: __('Клиника'), key: 'clinic', width: 20,style: {border: BORDER_AROUND}},
        {header: __('Отделение'), key: 'card_specialization', width: 12,style: {border: BORDER_AROUND}},
        {header: __('Количество пациентов'), key: 'is_first', width: 12,style: {border: BORDER_AROUND}},
        {header: __('Количество пациентов'), key: 'repeated', width: 12,style: {border: BORDER_AROUND}},
        {header: __('Доход'), key: 'patient_payed', width: 12,style: {border: BORDER_AROUND}},
        {header: __('Доход'), key: 'insurer_payed', width: 12,style: {border: BORDER_AROUND}},
        {header: __('Доход'), key: 'total', width: 12,style: {border: BORDER_AROUND}},
    ];

    worksheet.columns = columns;

    //First row styles
    worksheet.getRow(1).alignment = ALIGNMENT;
    worksheet.mergeCells(worksheet.getColumn('is_first').letter + '1:' + worksheet.getColumn('repeated').letter + '1');
    worksheet.mergeCells(worksheet.getColumn('patient_payed').letter + '1:' + worksheet.getColumn('total').letter + '1');
    
    //Subheader row
    let rowSubHeader = {
        is_first: __('Первичных'),
        repeated: __('Повторные'),
        patient_payed: __('от пациента'),
        insurer_payed: __('от страховой'),
        total: __('Итого'),
    };
    rowSubHeader = worksheet.addRow(rowSubHeader);

    let companyTotals = {};
    let clinicTotals = {};

    /**
     * Seed table data
     */ 
    //Group by insurence company
    let insurerAppointments = _.groupBy(data.appointments, 'insurer');
    let insurerServices = _.groupBy(data.payments, 'insurer');
    let insurers = getDataKeys(Object.keys(insurerAppointments), Object.keys(insurerServices));
    
    insurers.forEach(insurerName => {
        //Group by clinic
        let clinicAppointments = _.groupBy(insurerAppointments[insurerName], 'clinic_name');
        let clinicServices = _.groupBy(insurerServices[insurerName], 'clinic_name');
        let clinics = getDataKeys(Object.keys(clinicAppointments), Object.keys(clinicServices));

        clinics.forEach(clinic => {
            //Group by card specialization
            let specializationAppointments = _.groupBy(clinicAppointments[clinic], 'card_specialization');
            let specializationServices = _.groupBy(clinicServices[clinic], 'card_specialization');
            let specializations = getDataKeys(Object.keys(specializationAppointments), Object.keys(specializationServices));
            
            specializations.forEach(specialization => {
                let row = {
                    insurer: insurerName,
                    clinic: clinic,
                    card_specialization: specialization,
                };
                let appointments = specializationAppointments[specialization] || [];
                let appointmentServices = specializationServices[specialization] || [];
                
                row.is_first = appointments.filter(item => item.is_first).length;
                row.repeated = appointments.filter(item => !item.is_first).length;
                row.total = getTotalPayed(appointmentServices);

                let payedParts = getPayedParts(appointmentServices);
                row.patient_payed = payedParts.patient;
                row.insurer_payed = payedParts.company;
                worksheet.addRow(row);

                if (clinicTotals[clinic]) {
                    clinicTotals[clinic].payed += row.total;
                    clinicTotals[clinic].is_first += row.is_first;
                } else {
                    clinicTotals[clinic] = {
                        payed:row.total,
                        is_first: row.is_first,
                    };
                }

                if (companyTotals[insurerName]) {
                    companyTotals[insurerName].payed += row.total;
                    companyTotals[insurerName].is_first += row.is_first;
                } else {
                    companyTotals[insurerName] = {
                        payed:row.total,
                        is_first: row.is_first,
                    };
                }
            });
        });
    });
    
    seedCompanySheet(workbook, companyTotals);
    seedClinicSheet(workbook, clinicTotals);
    return Promise.resolve(workbook);
}