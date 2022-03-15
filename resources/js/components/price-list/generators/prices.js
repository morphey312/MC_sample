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

const reportAddRows = (workbook, data, fields) => {
    let worksheet = workbook.worksheets[0];
    data.forEach((row) => {
        let data = {
            city: row.price ? formatter.listFormat(row.price.clinic_names) : '',
            price_name: row.set_name,
            specialization: row.service.specialization_name,
            code: row.service.clinics[0] ? row.service.clinics[0].code : null,
            service_name: row.service.name,
            service_name_lc1: row.service.name_lc1,
            service_name_lc2: row.service.name_lc2,
            service_name_lc3: row.service.name_lc3,
            service_name_ua: row.service.name_ua,
            service_name_ua_lc1: row.service.name_ua_lc1,
            service_name_ua_lc2: row.service.name_ua_lc2,
            service_name_ua_lc3: row.service.name_ua_lc3,
            is_base: row.service.is_base ? '1' : '',
            payment_destination: row.service.payment_destination_name,
            self_price: getClinicPriceData(row.service.clinic_prices).join('; '),
            price: row.price ? Number(row.price.cost).toFixed(2) : '',
            currency: row.price ? getCurrency(row.price.currency) : '',
            date: row.price ? moment(row.price.date_from).format('DD.MM.YYYY') : '',
            site_service_type: row.service.site_service_type ? getSiteServiceType(row.service.site_service_type) : '',
            is_online: row.service.is_online === true ? __('Да') : __('Нет'),
        }
        worksheet.addRow(data);
    });
}

function getClinicPriceData(clinic_prices) {
    if (typeof clinic_prices === 'undefined' || clinic_prices.length === 0) {
        return [];
    }
    let basePrices = clinic_prices.filter(price => price.set_type === CONSTANTS.PRICE.SET_TYPE.BASE);
    return basePrices.map(price => {
        return price.clinic_names.join(', ') + ": " + price.cost;
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
    reportAddRows,
}
