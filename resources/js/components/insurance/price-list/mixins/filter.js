import CONSTANTS from '@/constants';

export default {
    props: {
        selectedPriceSet: String,
        priceSets: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            availableClinics: [],
        };
    },
    mounted() {
        this.loadClinics();
    },
    methods: {
        makeFilters(filters) {
            // the behavior of this filter is changed from:
            // "match services related to particular clinic" to:
            // "match services where price (not) exists for particular clinic"
            if (filters.price_exists === CONSTANTS.PRICE.EXISTENS.HAS_PRICE_WITH_PERIOD) {
                filters.has_price = {
                    from: filters.price_date_from,   
                    to: filters.price_date_to,
                    set_id: filters.set_id,
                };
                if (filters.clinic !== undefined) {
                    filters.has_price.clinic = filters.clinic;
                }
            }
            else if (filters.price_exists === CONSTANTS.PRICE.EXISTENS.HAS_PRICE) {
                filters.has_price = {
                    set_id: filters.set_id,
                };
                if (filters.clinic !== undefined) {
                    filters.has_price.clinic = filters.clinic;
                }
            } 
            else if (filters.price_exists === CONSTANTS.PRICE.EXISTENS.HAS_NO_PRICE_WITH_PERIOD) {
                filters.has_no_price = {
                    from: filters.price_date_from,   
                    to: filters.price_date_to,
                    set_id: filters.set_id,
                };
                if (filters.clinic !== undefined) {
                    filters.has_no_price.clinic = filters.clinic;
                }
            } 
            else if (filters.price_exists === CONSTANTS.PRICE.EXISTENS.HAS_NO_PRICE) {
                filters.has_no_price = {
                    set_id: filters.set_id,
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
        loadClinics() {
            let option = _.find(this.priceSets, (v) => v.id === this.filter.set_id);
            this.clinics.fetchList(_.onlyFilled({
                has_insurance: option.owner_id,
            })).then((list) => {
                this.availableClinics = list;
                this.$emit('change-clinics', list);
            });
        }
    },
    watch: {
        ['filter.set_id'](val) {
            this.loadClinics();
        }
    },
}