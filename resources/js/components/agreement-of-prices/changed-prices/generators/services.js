import * as formatter from '@/services/format';
import moment from 'moment'

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

const addRows = (workbook, data, sheet) => {
 
    let worksheet = workbook.worksheets.find(item => item.name === sheet);
 
    data.forEach((row) => {
        let price = filterPrices(row.prices);
        
        let data = {
            service_name: row.name,
            price: Number(price.cost).toFixed(2),
            date: moment(price.updated_at).format('DD.MM.YYYY'),
            date_from: moment(price.date_from).format('DD.MM.YYYY'),
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
