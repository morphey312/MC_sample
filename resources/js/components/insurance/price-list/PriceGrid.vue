<script>
import BasePriceGrid from '@/components/price-list/BasePriceGrid.vue';
import Price from '@/models/price';
import CONSTANTS from '@/constants';

export default {
    extends: BasePriceGrid,
    computed: {
        activeSetId() {
            return this.activeFilters.set_id;
        },
    },
    methods: {
        prepareRows(rows) {
            let result = [];
            rows = rows.filter(service => {
                let basePrices = service.prices.filter(price => {
                    return price.set_type === CONSTANTS.PRICE.SET_TYPE.BASE && price.cost > 0;
                });
                return basePrices.length != 0;
            });
            
            rows.forEach((service) => {
                service.clinic_prices = service.prices;
                service.prices = service.prices.filter(p => p.set_id === this.activeSetId);

                if (service.prices.length === 0) {
                    result.push({
                        id: this.rowIdCounter++, 
                        service: service,
                        price: null,
                    });
                } else {
                    service.prices
                        .sort((a, b) => this.orderByClinics(a, b))
                        .forEach((price) => {
                            result.push({
                                id: this.rowIdCounter++, 
                                service: service,
                                price: price,
                            });
                        });
                }
            });
            return result;
        },
        createRow(service, values, initial = {}) {
            let price = new Price({
                service_id: service.id,
                set_id: this.activeSetId,
                ...initial,
            });
            _.assign(price, values);
            if (this.isMatchingFilter(price, this.activeFilters)) {
                return {
                    id: this.rowIdCounter++, 
                    service: service,
                    price: price,
                };
            }
            return null;
        },
    },
};
</script>
