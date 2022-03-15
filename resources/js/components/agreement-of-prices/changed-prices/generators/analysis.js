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
        let price = filterPrices(row.prices)
        let data = {
            analysis_name: row.name,
            price: Number(price.cost).toFixed(2),
            date: moment(price.updated_at).format('DD.MM.YYYY'),
            date_from: moment(price.date_from).format('DD.MM.YYYY'),
            laboratory_code: row.laboratory_code,
            code: formatter.listFormat(row.clinics, 'code'),
            duration: formatter.listFormat(row.clinics, 'duration'),
        }
        worksheet.addRow(data);
    });
}

function filterPrices(prices) {
    let ordered = _.orderBy(prices, 'updated_at', ['desc']);
    let lastPrice = _.first(ordered);
    return lastPrice;
}

export {
    addRows,
}
