<script>
import BasePriceGrid from './BasePriceGrid.vue';
import Price from '@/models/price';
import * as resultGenerator from "./generators/analysis";

export default {
    extends: BasePriceGrid,
    data(){
        return {
            fileGenerator: resultGenerator,
            exportFields: [
                {title: __('ГОРОД'), name: 'city'},
                {title: __('ЛАБОРАТОРИЯ'), name: 'laboratory', width: 20},
                {title: __('КОД ЛАБОРАТОРИИ'), name: 'laboratory_code'},
                {title: __('КОД КЛИНИКИ'), name: 'code', width: 15},
                {title: __('АНАЛИЗ'), name: 'analysis_name', width: 25},
                {title: __('КОЛ-ВО ДНЕЙ ДЛЯ ВЫПОЛНЕНИЯ АНАЛИЗА'), name: 'duration', width: 10},
                {title: __('Цена в клинике'), name: 'self_price', width: 15},
                {title: __('ЦЕНА'), name: 'price', width: 10},
                {title: __('ВАЛЮТА'), name: 'currency', width: 10},
                {title: __('ДАТА'), name: 'date', width: 15},
            ]
        }
    },
    computed: {
        exportFileName(){
            return __('Прайс анализы');
        }
    },
    methods: {
        prepareRows(rows) {
            let result = [];
            rows.forEach((service) => {
                service.clinic_prices = service.prices;
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
            if (this.isMatchingFilter(price, this.activeFilters)) {
                return {
                    id: this.rowIdCounter++,
                    service: service,
                    price: price,
                };
            }
            return null;
        },
        getSetId() {
            let set = this.priceSets.find(s => s.type === this.activeFilters.set_type);
            return set ? set.id : null;
        },
    }
}
</script>
