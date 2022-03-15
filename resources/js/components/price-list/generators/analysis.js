import * as formatter from '@/services/format';
import moment from 'moment'
import CONSTANTS from '@/constants';

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

const reportAddRows = (workbook, data, fields, filters) => {
    let worksheet = workbook.worksheets[0];
    let filterClinics = _.wrapArray(filters.clinic);
    data.forEach((row) => {
        let clinicIds = filterClinics.length === 0 ? (row.price ? row.price.clinics : []) : filterClinics;
        let clinics = row.service.clinics.filter(c => clinicIds.indexOf(c.id) !== -1);
        let data = {
            city: formatter.listFormat(clinics, 'name'),
            price_name: row.set_name || '',
            laboratory: row.service.laboratory_name,
            laboratory_code: row.service.laboratory_code,
            code: formatter.listFormat(clinics, 'code'),
            analysis_name: row.service.name,
            duration: formatter.listFormat(clinics, 'duration_days'),
            self_price: getClinicPriceData(row.service.clinic_prices).join('; '),
            price: row.price ? Number(row.price.cost).toFixed(2) : '',
            currency: row.price ? getCurrency(row.price.currency) : '',
            date: row.price ? moment(row.price.date_from).format('DD.MM.YYYY') : '',
        }
        worksheet.addRow(data);
    });
}

function getClinicPriceData(clinic_prices) {
    if (clinic_prices.length === 0) {
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

export {
    reportAddRows,
}
