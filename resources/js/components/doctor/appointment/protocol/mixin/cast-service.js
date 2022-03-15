import PriceCalculator from '@/services/price-calculator';

export default {
    methods: {
        castServiceRows(rows) {
            return rows.map((row) => {
                let calculator = new PriceCalculator(row.prices);
                let cost = calculator.calc(this.appointment.clinic_id, {
                    discountCard: this.appointment.discount_card,
                });
                return {
                    cost: cost.cost,
                    discount: cost.discount,
                    costWithDiscount: cost.costWithDiscount,
                    priceId: cost.price ? cost.price.id : null,
                    service: row,
                };
            });
        }
    }
}