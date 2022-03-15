import CONSTANTS from '@/constants';
import {number} from "echarts/src/export";

export default {
    created() {
        this.cachedDiscounts = new Map();
    },
    methods: {
        setModelCost(model, price) {
            if (this.clinicRoundedCost()) {
                return model.cost = this.$formatter.numberFormat(price * model.quantity);
            }
            return model.cost = Math.round(price * model.quantity).toFixed(2);
        },
        clinicRoundedCost() {
            if (this.appointmentData && this.appointmentData.clinic && this.appointmentData.clinic.not_round_cost) {
                return true;
            }
            if (this.appointment && this.appointment.clinic_requisites.not_round_cost){
                return true;
            }
            return false;
        },
        calcModelPrice(model) {
            let price;
            if (model.analysis.price > 0) {
                if (_.isFilled(model.discount)) {
                    price = model.analysis.price - (model.analysis.price / 100 * model.discount);
                } else {
                    price = this.$formatter.numberFormat(model.analysis.price, 2);
                }
                if (_.isFunction(this.setAssignmentQuantity)) {
                    this.setAssignmentQuantity(model);
                }
                return this.setModelCost(model, price);
            }
        },
        isNewDiscountCard() {
            return this.$discountData.discountCard && this.$discountData.oldDiscountCard
                ? this.$discountData.oldDiscountCard.id !== this.$discountData.discountCard.id
                : !(!this.$discountData.oldDiscountCard && this.$discountData.discountCard);
        },
        getDiscountCardId(newId = false) {
            return this.getDiscountCardOrDefault(newId ? this.$discountData.discountCard : this.$discountData.oldDiscountCard || this.$discountData.discountCard).id;
        },
        calcModelDiscount(model, modelType) {
            let refreshDiscountType = this.$discountData.refreshDiscountType;
            let discountModel = Number(model.discount);

            if (this.$discountData.disabled) {
                return discountModel;
            } else if (this.$discountData.firstCalcDiscountCard[modelType] && !this.$discountData.reload[modelType] && refreshDiscountType === 2) {
                return discountModel;
            } else if (!this.$discountData.firstCalcDiscountCard[modelType] && !this.$discountData.reload[modelType] && refreshDiscountType === 0) {
                return discountModel;
            }

            let withoutCard = !this.$discountData.discountCard;
            let discountCard = withoutCard ? null : this.$discountData.discountCard;
            let discountDiscountCard = 0;


            if (discountCard) {
                discountDiscountCard = this.getDiscountByDiscountCard(discountCard, model, modelType);
            }

            if (refreshDiscountType !== 0) {
                if (model.payed > 0) {
                    let modelPrice = this.getModelPriceWithDiscount(model);
                    let maxDiscount = parseFloat((100 - (100 * model.payed / model.price)).toFixed())

                    if (model.payed === modelPrice && ((model.price / 100 * maxDiscount) + model.payed).toFixed() == Number(model.price) && discountModel < maxDiscount) {
                        discountModel = maxDiscount
                    } else if (((model.price / 100 * maxDiscount) + model.payed).toFixed() == Number(model.price) && maxDiscount <= discountModel) {
                        discountModel = maxDiscount
                    } else {
                        discountModel = discountDiscountCard
                    }
                    return discountModel
                }

                if (withoutCard && refreshDiscountType === 1)
                    return discountDiscountCard

                return withoutCard ? discountModel : discountDiscountCard
            }

            return discountModel
        },
        setAnalysisDiscount(model) {
            if (model.by_policy) {
                return;
            }
            model.discount = this.calcModelDiscount(model, 'analysis');
        },
        isDiscountsNotChanged(discountModel, discountDiscountCard) {
            return discountModel === 0 || discountModel === discountDiscountCard;
        },
        getDiscountCardOrDefault(discountCard) {
            return discountCard || {
                id: 'default',
            };
        },
        getModelPriceWithDiscount(service, field_price = 'price', field_discount = 'discount') {
            let price;

            if (_.isFilled(service[field_discount])) {
                price = Number(service[field_price]) - (Number(service[field_price]) / 100 * service[field_discount]);
            } else {
                price = service[field_price];
            }
            return Math.round(price);
        },
        getDiscountByDiscountCard(discountCard, model, modelType) {
            let fn;
            let discount = 0;

            if (discountCard.type.use_detail_payments == false) {
                discount = discountCard.type.discount_percent;
            } else {
                if (discountCard.type.use_detail_payments == true &&
                    discountCard.type.payment_destinations.length !== 0) {
                    switch (modelType) {
                    case 'service':
                        fn = (payment) => model.payment_destination_id === payment.payment_destination_id;
                        break;
                    case 'analysis':
                        fn = (payment) => payment.additional_service_mark === CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_ANALYSES;
                    }

                    discount = this.getDiscountByDiscountCardFromPaymentDestinations(discountCard, fn);
                }
            }

            return discount;
        },
        getDiscountFromCacheOrCommon(key, commonDiscounts, notEqualDiscounts) {
            return !this.$discountData.refreshDiscountType && this.cachedDiscounts.has(key)
                ? this.cachedDiscounts.get(key)
                : (notEqualDiscounts ? commonDiscounts.discountModel : commonDiscounts.discountDiscountCard);
        },
        getDiscountByDiscountCardFromPaymentDestinations(discountCard, fn) {
            let discount = discountCard.type.payment_destinations.find((payment) => {
                return fn(payment) && this.model.date >= payment.date_start && this.model.date <= payment.date_end;
            });

            return discount ? discount.discount_percent : 0;
        },
    }
}
