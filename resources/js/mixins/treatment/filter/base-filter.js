import CONSTANTS from '@/constants';
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        defaultPriceSet: {
            type: String,
            default: CONSTANTS.PRICE.SET_TYPE.BASE,
        },
        permissions: String,
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited(this.permissions),
            }),
        };
    },
    computed: {
        dateDisable() {
            return !(this.filter.price_exists == CONSTANTS.PRICE.EXISTENS.HAS_PRICE_WITH_PERIOD || 
                this.filter.price_exists == CONSTANTS.PRICE.EXISTENS.HAS_NO_PRICE_WITH_PERIOD);
        },
    },
    methods: {
        getPriceSet() {
            return this.defaultPriceSet;
        },
        makeFilters(filters) {
            // If price existence filter is combined with clinic filter
            // the behavior of this filter is changed from:
            // "match services related to particular clinic" to:
            // "match services where price (not) exists for particular clinic"
            if (filters.price_exists === CONSTANTS.PRICE.EXISTENS.HAS_PRICE_WITH_PERIOD) {
                filters.has_price = {
                    from: filters.price_date_from,   
                    to: filters.price_date_to,
                    set: this.getPriceSet(),
                };
                if (filters.clinic !== undefined) {
                    filters.has_price.clinic = filters.clinic;
                }
            } 
            else if (filters.price_exists === CONSTANTS.PRICE.EXISTENS.HAS_PRICE) {
                filters.has_price = {
                    set: this.getPriceSet(),
                };
                if (filters.clinic !== undefined) {
                    filters.has_price.clinic = filters.clinic;
                }
            } 
            else if (filters.price_exists === CONSTANTS.PRICE.EXISTENS.HAS_NO_PRICE_WITH_PERIOD) {
                filters.has_no_price = {
                    from: filters.price_date_from,   
                    to: filters.price_date_to,
                    set: this.getPriceSet(),
                };
                if (filters.clinic !== undefined) {
                    filters.has_no_price.clinic = filters.clinic;
                }
            } 
            else if (filters.price_exists === CONSTANTS.PRICE.EXISTENS.HAS_NO_PRICE) {
                filters.has_no_price = {
                    set: this.getPriceSet(),
                };
                if (filters.clinic !== undefined) {
                    filters.has_no_price.clinic = filters.clinic;
                }
            }

            delete filters.price_date_from;
            delete filters.price_date_to;
            delete filters.price_exists;

            return _.onlyFilled(filters);
        },
        convertPriceParams(params) {
            let converted = {...params};
            if (converted.has_price !== undefined) {
                if (converted.has_price.from !== undefined) {
                    converted.price_exists = CONSTANTS.PRICE.EXISTENS.HAS_PRICE_WITH_PERIOD
                    converted.price_date_from = converted.has_price.from;
                    converted.price_date_to = converted.has_price.to;
                } else {
                    converted.price_exists = CONSTANTS.PRICE.EXISTENS.HAS_PRICE;
                }
                
                if (converted.has_price.clinic !== undefined) {
                    converted.clinic = converted.has_price.clinic;
                }
                
                delete converted.has_price;
            } 
            else if (converted.has_no_price !== undefined) {
                if (converted.has_no_price.from !== undefined) {
                    converted.price_exists = CONSTANTS.PRICE.EXISTENS.HAS_NO_PRICE_WITH_PERIOD;
                    converted.price_date_from = converted.has_no_price.from;
                    converted.price_date_to = converted.has_no_price.to;
                } else {
                    converted.price_exists = CONSTANTS.PRICE.EXISTENS.HAS_NO_PRICE;
                }
                
                if (converted.has_no_price.clinic !== undefined) {
                    converted.clinic = converted.has_no_price.clinic;
                }
                
                delete converted.has_no_price;
            }
            else {
                converted.price_exists = CONSTANTS.PRICE.EXISTENS.NO_MATTER_PRICE;
            }
            
            return converted;
        },
    },
    watch: {
        defaultPriceSet(val) {
            this.sync({});
        },
    },
};