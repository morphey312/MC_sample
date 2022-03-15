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
        appointmentData: {
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
                this.setAnalysisPrice(row);
            }
            this.$emit('selection-changed', {row, index});
        },
        checkDisabledDate (date) {
            return this.$moment(date).isBefore(this.$moment(), "day");
        },
        setAnalysisPrice(row) {
            this.$nextTick(() => {
                let priceData;
                if (row.by_policy) {
                    priceData = getInsurancePrice(row, this.appointmentData, CONSTANTS.PRICE.SET_TYPE.INSURANCE, this.insurancePolicy.insurance_company_id);
                    row.discount = 0;
                } else {
                    priceData = getServicePrice(row, this.appointmentData, CONSTANTS.PRICE.SET_TYPE.BASE);
                    let discount = this.calcModelDiscount(row, 'analysis');
                    if (discount > row.discount) {
                        row.discount = discount;
                    }
                }
                if (priceData && priceData.id) {
                    row.price_id = priceData.id;
                    row.analysis.price = priceData.price;
                    row.selfCost = priceData.selfCost;
                }
                this.costChanged(row);
            });
        },
    }
}
