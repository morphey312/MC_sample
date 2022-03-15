<script>
import BasePriceGrid from './BasePriceGrid.vue';
import Price from '@/models/price';

export default {
    extends: BasePriceGrid,
    methods: {
        prepareRows(rows) {
            let result = [];
            rows.forEach((service) => {
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
                set_id: this.getSetId(),
                ...initial,
            });
            _.assign(price, values);
            return {
                id: this.rowIdCounter++,
                service: service,
                price: price,
            };
        },
        getSetId() {
            let set = this.priceSets.find(s => s.type === this.activeFilters.set_type);
            return set ? set.id : null;
        },
    }
}
</script>
