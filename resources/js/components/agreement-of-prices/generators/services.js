import * as formatter from '@/services/format';
import moment from 'moment'
import CONSTANTS from '@/constants';
import handbook from "@/services/handbook";

const currencies = [
    {
        intl_name: 'uah',
        name: __('грн.')
    },
    {
        intl_name: 'eur',
        name: __('Євро')
    },
]

const addRows = (workbook, data) => {
    let worksheet = workbook.worksheets[0];

    data.forEach((row) => {
        let showNewClinics = row.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.ADD_CLINIC;
        let data = {
            clinics: showNewClinics
                ? formatter.listFormat(row.new_clinics, 'name')
                : formatter.listFormat(row.clinics, 'name'),
            price_name: row.set_name,
            specialization: row.specialization_name,
            code: showNewClinics
                ? formatter.listFormat(row.new_clinics, 'code')
                : formatter.listFormat(row.clinics, 'code'),
            service_name: row.name,
            service_name_lc1: row.name_lc1,
            service_name_lc2: row.name_lc2,
            service_name_lc3: row.name_lc3,
            service_name_ua: row.name_ua,
            service_name_ua_lc1: row.name_ua_lc1,
            service_name_ua_lc2: row.name_ua_lc2,
            service_name_ua_lc3: row.name_ua_lc3,
            is_base: row.is_base ? '1' : '',
            payment_destination: row.payment_destination_name,
            price: Number(row.cost).toFixed(2),
            recommended_price: Number(row.recommended_cost).toFixed(2),
            currency: getCurrency(row.currency),
            date: moment(row.price_date_from).format('DD.MM.YYYY'),
            site_service_type: row.site_service_type ? getSiteServiceType(row.site_service_type) : '',
            is_online: row.is_online === true ? __('Да') : __('Нет'),
        }
        worksheet.addRow(data);
    });
}

function  getCurrency(name) {
    let currency = _.find(currencies,(currency) => currency.intl_name === name);

    if (currency) {
        return currency.name
    }

    return __('грн.');
}

function getSiteServiceType(name) {
    let siteServiceTypes = handbook.getOptions('site_service_type');
    let type = _.find(siteServiceTypes,(siteServiceType) => siteServiceType.id === name);

    if (type) {
        return type.value;
    }

    return '';
}

export {
    addRows,
}
