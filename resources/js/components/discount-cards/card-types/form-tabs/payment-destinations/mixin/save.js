export default {
	methods: {
		save(emitAction = 'created') {
			this.model.validate().then((errors) => {
                if(_.isEmpty(errors)) {
                    if (this.invalidPaymentPeriod()) {
                        return this.$error(__('Пересекается с другими скидками того же назначения платежа'));
                    }

                    this.$emit(emitAction, this.model); 
                    return;
                }

                return this.$displayErrors({errors});
            });
		},
		invalidPaymentPeriod() {
            if (this.discountType.payment_destinations.length === 0) {
                return false;
            }

            let start = this.model.date_start;
            let end = this.model.date_end;
            let crossPeriod = this.discountType.payment_destinations.find((payment) => {
                if ((payment.id != this.model.id) && (payment.payment_destination_id == this.model.payment_destination_id)) {
                    return (payment.date_start >= start && payment.date_start <= end) ||
                           (payment.date_end >= start && payment.date_end <= end) ||
                           (payment.date_start <= start && payment.date_end >= end);
                }
                return false;
            });

            return _.isFilled(crossPeriod);
        },
	},
}