export default {
    methods: {
        getPaymentAggrScript(amountType = 'payed_amount') {
            return `
                if(doc['type.keyword'].value == 'expense') {
                    return -(doc['${amountType}'].value)
                }
                return doc['${amountType}'].value`;
        }
    },
}
