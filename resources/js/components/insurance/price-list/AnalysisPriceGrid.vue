<script>
import BasePriceGrid from '@/components/price-list/BasePriceGrid.vue';
import Price from '@/models/price';
import CONSTANTS from '@/constants';
import * as resultGenerator from "../../price-list/generators/analysis";

export default {
    extends: BasePriceGrid,
    computed: {
        activeSetId() {
            return this.activeFilters.set_id;
        },
        exportFileName(){
            return __('Прайс анализы страховые');
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
    data(){
        return {
            fileGenerator: resultGenerator,
            exportFields: [
                {title: __('ГОРОД'), name: 'city'},
                {title: __('НАЗВАНИЕ ПРАЙСА'), name: 'price_name', width: 25},
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
    }
};
</script>
