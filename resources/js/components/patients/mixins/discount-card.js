export default {
	methods: {
		cancel() {
            this.$emit('cancel');
        },
		hasSameCard() {
            let sameCard = this.patient.issued_discount_cards.find((card) => {
                let validFrom = card.valid_from;
                let expires = card.expires;

                return (card.discount_card_type_id == this.model.discount_card_type_id) && 
                       (card.id != this.model.id) && 
                       (card.clinic_id == this.model.clinic_id) && 
                       ((this.model.valid_from >= validFrom && this.model.valid_from <= expires) || 
                        (this.model.expires >= validFrom && this.model.expires <= expires) || 
                        (this.model.valid_from < validFrom && this.model.expires > expires))
            });

            return sameCard !== undefined;
        },
        isInvalidMaxExpire() {
            let cardType = this.getForm().card_types.find(type => type.id == this.model.discount_card_type_id);
            let expires = this.$moment(this.model.expires);
            return cardType.expire_period < expires.diff(this.model.valid_from, 'days')
        },
        getForm() {
            return this.$refs.cardForm;
        },
        crossError() {
        	return this.$error(__('Период действия карты пересекается с другой картой такого же типа'));
        },
        expireError() {
            return this.$error(__('Период действия карты больше установленного для типа карты'));
        },
	},
}