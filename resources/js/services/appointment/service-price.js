/**
 * Get ordinary price
 * 
 * @param {object} data 
 * @param {object} filters 
 * @param {string} setType 
 * 
 * @returns {object}
 */
const getServicePrice = (data, filters, setType) => {
    let actualPrices = [];
    if (!_.isEmpty(data.prices)) {
        actualPrices = filterActualPrices(data, filters, setType);
    }
    return getPriceFields(actualPrices);
}

/**
 * Get insurance company price 
 * 
 * @param {object} data 
 * @param {object} filters 
 * @param {string} setType 
 * @param {int} insurerId 
 * 
 * @returns {object}
 */
const getInsurancePrice = (data, filters, setType, insurerId) => {
    let actualPrices = [];
    if (!_.isEmpty(data.prices)) {
        actualPrices = filterActualPrices(data, filters, setType)
                        .filter(price => price.set_owner && price.set_owner.owner_id === insurerId);
    }
    return getPriceFields(actualPrices);
}

/**
 * Get price fields 
 * 
 * @param {array} prices 
 * 
 * @returns {object}
 */
const getPriceFields = (prices) => {
    let cost = {
        id: null,
        price: 0,
        selfCost: 0,
    };

    if (prices.length != 0) {
        cost.id = prices[0].id;
        cost.price = prices[0].cost.toFixed(2);
        cost.selfCost = prices[0].self_cost.toFixed(2);
    }
    return cost;
}

/**
 * Get service actual price
 *
 * @param {object} data
 * @param {object} filters
 * 
 * @returns {object}
 */
const filterActualPrices = (data, filters, setType) => {
    let date = `${filters.hasPrice.from}`;
    // console.log(data.prices, setType, filters);
    
    return data.prices.filter((price) => {
        if (price.clinics) {
            if (price.clinics.indexOf(filters.hasPrice.clinic) !== -1 && 
                price.date_from <= date && 
                (price.date_to >= date || _.isNil(price.date_to)) &&
                price.cost > 0 &&
                price.set_type == setType) {
                return true;
            }
        }
        return false;
    });
}

export {
    getServicePrice,
    getInsurancePrice,
    filterActualPrices,
}