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

const addRows = (workbook, data) => {
    let worksheet = workbook.worksheets[0];

    data.forEach((row) => {
        let showNewClinics = row.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.ADD_CLINIC;
        let data = {
            clinics: showNewClinics
                ? formatter.listFormat(row.new_clinics, 'name')
                : formatter.listFormat(row.clinics, 'name'),
            price_name: row.set_name,
            laboratory: row.laboratory_name,
            laboratory_code: row.laboratory_code,
            code: showNewClinics
                ? formatter.listFormat(row.new_clinics, 'code')
                : formatter.listFormat(row.clinics, 'code'),
            analysis_name: row.service_name,
            duration: showNewClinics
                ? formatter.listFormat(row.new_clinics, 'duration')
                : formatter.listFormat(row.clinics, 'duration'),
            price: Number(row.cost).toFixed(2),
            recommended_price: Number(row.recommended_cost).toFixed(2),
            currency: getCurrency(row.currency),
            date: moment(row.price_date_from).format('DD.MM.YYYY'),
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

export {
    addRows,
}
