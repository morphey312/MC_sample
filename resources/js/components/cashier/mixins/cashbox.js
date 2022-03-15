export default {
    methods: {
        getCashAndPrinterCashbox(cashboxes) {
            return this.filterCashCashbox(cashboxes, false);
        },
        getCashNonPrinterCashbox(cashboxes) {
            return this.filterCashCashbox(cashboxes, false);
        },
        filterCashCashbox(cashboxes, isFiscal = true) {
            return cashboxes.find((box) => {
                if (box.payment_method && box.payment_method.id === 4 && box.payment_method.use_cash == true && box.payment_method.clinics.length != 0) {
                    let clinic = box.payment_method.clinics.find((clinic) => {
                        return clinic.is_fiscal == isFiscal && clinic.clinic_id == this.$store.state.user.cashierClinicId;
                    })
                    if (clinic) {
                        return true;
                    }
                    return false;
                }
                return false;
            });
        },
        filterCashCashboxes(cashboxes, isFiscal = true) {
            return cashboxes.filter((box) => {
                if (box.payment_method && box.payment_method.use_cash == true && box.payment_method.clinics.length != 0) {
                    let clinic = box.payment_method.clinics.find((clinic) => {
                        return clinic.is_fiscal == isFiscal && clinic.clinic_id == this.$store.state.user.cashierClinicId;
                    })

                    if (clinic) {
                        return true;
                    }

                    return false;
                }
                return false;
            })
        },
        getCashNonPrinterCashboxId(cashboxes) {
            let cashbox = this.getCashNonPrinterCashbox(cashboxes);
            return cashbox ? cashbox.id : null;
        },
        getCashAndPrinterCashboxId(cashboxes) {
            let cashbox = this.getCashAndPrinterCashbox(cashboxes);

            return cashbox ? cashbox.id : null;
        },
        getBalanceValue(amount) {
            return isNaN(amount) ? 0 : Number(amount);
        },
        getBalance(row) {
            return this.getBalanceValue(row.income) - this.getBalanceValue(row.expense);
        },
        // TODO::refactor use payment method id
        getNonCashNonFiscalCahboxId(cashboxes, paymentMethodId = null) {
            let cashbox = cashboxes.filter(box => {
                return box.payment_method.use_cash === false;
            }).find(box => {
                if (paymentMethodId) {
                    return box.payment_method.clinics.filter(clinic => clinic.is_fiscal == true).length == 0 && box.payment_method.id === paymentMethodId;
                }

                return box.payment_method.clinics.filter(clinic => clinic.is_fiscal == true).length == 0;
            })
            return cashbox ? cashbox.id : null;
        },
        prepareCashboxes(cashboxes) {
            let result = [...cashboxes];
            result.forEach((cashbox) => {
                cashbox.value = cashbox.payment_method.name;
            })

            return result;
        }
    }
}
