import moment from 'moment';
import CONSTANT from '@/constants';

class PriceCalculator
{
    /**
     * Constructor
     * 
     * @param {array} prices
     */ 
    constructor(prices) {
        this._prices = prices;
    }
    
    /**
     * Get price in clinic
     * 
     * @param {number} clinic
     * @param {object} options
     * 
     * @returns {object}
     */ 
    calc(clinic, options = {}) {
        let result = {
            price: null,
            cost: 0,
            selfCost: 0,
            discount: 0,
            costWithDiscount: 0,
        };
        
        options = {
            date: moment(options.date).startOf('day'),
            set: CONSTANT.PRICE.SET_TYPE.BASE,
            ...options,
        };
        
        let price = this.findPrice(clinic, options);
        
        if (price !== undefined) {
            result.price = price;
            result.cost = price.cost;
            result.costWithDiscount = price.cost;
            result.selfCost = price.self_cost;
            this.applyDiscount(result, options);
        }
        
        return result;
    }
    
    /**
     * Apply discount to price calculations
     * 
     * @param {object} price
     * @param {object} options
     */ 
    applyDiscount(price, options) {
        let discountValue = 0;
        
        if (options.manualDiscount) {
            discountValue = options.manualDiscount;
        }
        
        if (options.discountCard !== undefined) {
            let cardDiscountValue = this.getDiscount(options.discountCard, options);
            if (cardDiscountValue !== false && cardDiscountValue > discountValue) {
                discountValue = cardDiscountValue;
            }
        }
        
        price.discount = discountValue;
        price.costWithDiscount = this.calcDiscount(price.cost, discountValue);
    }
    
    /**
     * Calc price with discount
     * 
     * @param {number} cost
     * @param {number} discount
     * 
     * @returns {number}
     */ 
    calcDiscount(cost, discount) {
        return cost * (100 - discount) / 100;
    }
    
    /**
     * Get discount value
     * 
     * @param {object} card
     * @param {object} options
     * 
     * @returns {object}
     */ 
    getDiscount(card, {date, paymentDestination}) {
        if (!card.type.use_detail_payments) {
            return card.type.discount_percent;
        } else if (paymentDestination) {
            let discount = _.find(card.type.payment_destinations, (destination) => {
                if (destination.payment_destination_id === paymentDestination) {
                    let start = moment(destination.date_start).startOf('day');
                    let end = moment(destination.date_end).endOf('day');
                    return date.isSameOrAfter(start) && date.isBefore(end);
                }
                return false;
            });
            
            if (discount !== undefined) {
                return discount.discount_percent;
            }
        }
        return false;
    }
    
    /**
     * Find price
     * 
     * @param {number} clinic
     * @param {object} options
     * 
     * @returns {object}
     */ 
    findPrice(clinic, {date, set}) {
        return _.find(this._prices, (price) => {
            if (price.set_type === set && price.clinics.indexOf(clinic) !== -1) {
                let start = moment(price.date_from).startOf('day');
                let end = price.date_to ? moment(price.date_to).startOf('day') : null;
                if (date.isSameOrAfter(start) && (end === null || date.isSameOrBefore(end))) {
                    return true;
                }
            }
            return false;
        });
    }
}

export default PriceCalculator;