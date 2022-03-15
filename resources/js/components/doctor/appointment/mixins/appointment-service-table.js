import PriceCalculationMixin from '@/mixins/appointment/analysis/price-calculation';
import {getServicePrice, getInsurancePrice} from '@/services/appointment/service-price';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        PriceCalculationMixin
    ],
    props: {
        insurancePolicy: {
            type: Object,
            default: null,
        },
        filters: {
            type: Object,
            default: null,
        },
        model: {
            type: Object,
            default: null,
        },
    },
    methods: {
        updateList(updates) {
            this.syncFilters(updates);
            this.filteredResults = this.filterResults([...this.rows],  _.onlyFilled(this.filter));
        },
        costChanged(row) {
            this.$emit('cost-changed', row);
        },
        toggleSelection(row, index) {
            if (row.by_policy === true) {
                row.by_policy = false;
                this.setServicePrice(row);
            }
            this.$emit('selection-changed', {row, index});
        },
        setServicePrice(service) {
            this.$nextTick(() => {
                let priceData;
                if (service.by_policy) {
                    priceData = getInsurancePrice(service, this.filters, CONSTANTS.PRICE.SET_TYPE.INSURANCE, this.insurancePolicy.insurance_company_id);
                    service.discount = 0;
                } else {
                    priceData = getServicePrice(service, this.filters, CONSTANTS.PRICE.SET_TYPE.BASE);
                    let discount = this.calcModelDiscount(service, 'service');
                    if (discount > service.discount) {
                        service.discount = discount;
                    }
                }
                if (priceData && priceData.id) {
                    service.price_id = priceData.id;
                    service.price = priceData.price;
                    service.selfCost = priceData.selfCost;
                }
                this.costChanged(service);
            });
        },
    }
}
