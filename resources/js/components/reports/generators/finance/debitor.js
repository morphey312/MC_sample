import Excel from 'exceljs';
import {dateFormat, listFormat, numberFormat} from '@/services/format';

const COLUMN_STYLE = {
    alignment: {vertical: 'middle', wrapText: true},
    border: {
        right: {style:'medium', color: {argb:'000'}}
    }
};
const COLUMN_MIN_WIDTH = 20;
const COLUMN_MAX_WIDTH = 40;
const WORKSHEET_TYPE_INSURANCE = 'insurance';

const getColumns = (worksheetType = '') => {
    let columns = [
        {header: __('Клиника'), key: 'clinic', width: COLUMN_MIN_WIDTH, style: COLUMN_STYLE},
        {header: __('ФИО пациента'), key: 'patient', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
        {header: __('Карта'), key: 'card', width: COLUMN_MIN_WIDTH, style: COLUMN_STYLE},
        {header: __('Специализация'), key: 'specialization', width: COLUMN_MIN_WIDTH, style: COLUMN_STYLE},
        {header: __('Врач'), key: 'doctor', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
        {header: __('Полная стоимость'), key: 'cost', width: COLUMN_MIN_WIDTH, style: COLUMN_STYLE},
        {header: __('Услуга'), key: 'services', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
        {header: __('Остаток долга'), key: 'debt', width: COLUMN_MIN_WIDTH, style: COLUMN_STYLE},
        {header: __('Дата начала курса лечения'), key: 'course_start', width: COLUMN_MIN_WIDTH, style: COLUMN_STYLE},
        {header: __('Дата последнего визита'), key: 'last_visit', width: COLUMN_MIN_WIDTH, style: COLUMN_STYLE},
        {header: __('Диагноз'), key: 'diagnosis', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
        {header: __('Комментарий врача'), key: 'doctor_comment', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
        {header: __('Звонок коллектора/ФИО коллектора'), key: 'collector', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
        {header: __('Примечание по разговору'), key: 'call_comment', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
        {header: __('СМИ'), key: 'source_name', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
    ];

    if(worksheetType === WORKSHEET_TYPE_INSURANCE){
        columns.push(
            {header: __('Страховая компания'), key: 'insurance_act_company_name', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
            {header: __('Номер акта'), key: 'insurance_act_number', width: COLUMN_MAX_WIDTH, style: COLUMN_STYLE},
        )
    }

    return columns;
}

const sumServices = (services, field) => {
    return services.reduce((total, service) => {
        return total + Number(service[field]);
    }, 0);
}

const formateDate = (date, format = 'DD.MM.YYYY') => {
    return dateFormat(date, format);
}

const getLastName = (fullName) => {
    let parts = fullName.replace(/^\s+/,"").split(' ');
    return parts.length != 0 ? parts[0] : '';
}

const getCollectorCallsInfo = (collector_calls) => {
    let output = [];
    if (collector_calls.length !== 0) {
        collector_calls.forEach(call => {
            output.push(`${formateDate(call.date, 'DD.MM')} - ${call.result}/${getLastName(call.operator)}`);
        });
    }
    return listFormat(output);
}

const seedWorksheet = (worksheet, list = [], workSheetType = null) => {
    worksheet.columns = getColumns(workSheetType);
    worksheet.properties.defaultRowHeight = 40;

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

    list.forEach(item => {
        let row = {};
        let servicesCost = sumServices(item.services, 'cost');
        let servicesPayed = sumServices(item.services, 'payed');

        row['clinic'] = item.clinic_name;
        row['card'] = item.patient_card ? (item.patient_card.number + item.card_specialization) : '';
        row['specialization'] = item.specialization;
        row['doctor'] = item.doctor_name;
        row['patient'] = item.patient_name;
        row['cost'] = servicesCost;
        row['debt'] = Number(numberFormat(servicesCost - servicesPayed));
        row['services'] = listFormat(item.services, 'service_name');
        row['course_start'] = _.isFilled(item.course_start) ? formateDate(item.course_start) : '';
        row['last_visit'] = _.isFilled(item.last_visit_date) ? formateDate(item.last_visit_date) : '';
        row['diagnosis'] = listFormat(item.diagnoses);
        row['doctor_comment'] = listFormat(item.doctor_comments);
        row['collector'] = getCollectorCallsInfo(item.collector_calls);
        row['call_comment'] = listFormat(item.collector_calls, 'comment');
        row['source_name'] = item.source_name;
        row['insurance_act_company_name'] = listFormat(getInsuranceCompanyNamesFromServices(item.services));
        row['insurance_act_number'] = listFormat(getInsuranceCompanyActNumbersServices(item.services));

        row = worksheet.addRow(row);

        if (item.has_appointment) {
            row.fill = {
                type: 'pattern',
                pattern:'solid',
                fgColor: {
                    argb: 'ffff00',
                }
            };
        }
    });
}

const getInsuranceCompanyNamesFromServices = (services) => {
    return _.uniq(services.map((service) => {
        return service.insurance_act_company_name;
    }));
}

const getInsuranceCompanyActNumbersServices = (services) => {
    return _.uniq(services.map((service) => {
        return service.insurance_act_number;
    }));
}

const getLists = (data) => {
    let ordinary = [];
    let insurance = [];
    let corporateClient = [];
    data.forEach(row => {
        let debtServices = row.services.filter(service => Number(service.cost) > Number(service.payed));
        let ordinaryServices = debtServices.filter(service => service.by_policy == 0);
        let insuranceServices = debtServices.filter(service => service.by_policy == 1);
        let corporateClientServices = (debtServices.length > 0 && row.legal_entity_id != null) ? debtServices : [];

        if (ordinaryServices.length != 0 && corporateClientServices.length == 0) {
            ordinary.push({
                ...row.attributes,
                services: ordinaryServices,
            });
        }

        if (insuranceServices.length != 0 && corporateClientServices.length == 0) {
            insurance.push({
                ...row.attributes,
                services: insuranceServices,
            });
        }

        if (corporateClientServices.length != 0) {
            corporateClient.push({
                ...row.attributes,
                services: corporateClientServices,
            });
        }
    });

    return {ordinary, insurance, corporateClient}
}

export default (data) => {
    let workbook = new Excel.Workbook();
    const worksheet = workbook.addWorksheet(__('Без страховых'), {
        views: [
            {state: 'frozen', ySplit: 1},
        ],
    });

    const worksheetInsurance = workbook.addWorksheet(__('Страховые'), {
        views: [
            {state: 'frozen', ySplit: 1},
        ],
    });

    const worksheetCorporateClient = workbook.addWorksheet(__('Корп. клиент'), {
        views: [
            {state: 'frozen', ySplit: 1},
        ],
    });

    let lists = getLists(data);

    seedWorksheet(worksheet, lists.ordinary);
    seedWorksheet(worksheetInsurance, lists.insurance, WORKSHEET_TYPE_INSURANCE);
    seedWorksheet(worksheetCorporateClient, lists.corporateClient);

    return Promise.resolve(workbook);
}
