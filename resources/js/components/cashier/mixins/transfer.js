import EmployeeRepository from '@/repositories/employee';
import CashTransfer from '@/models/employee/cashbox/cash-transfer';

export default {
    props: {
        cashier: Object,
        sourceId: [Number, String],
    },
    data() {
        return {
            model: new CashTransfer({
                cashier_id: this.cashier.id,
                source_id: this.sourceId,
            }),
            cashiers: [],
        }
    },
    mounted() {
        this.getCashiers();
    },
    methods: {
        getCashiers() {
            let employee = new EmployeeRepository();
            employee.fetchList(this.getCashierFilters()).then((response) => {
                this.cashiers = this.castCashiers(response);
            });
        },
        getCashierFilters() {
            return _.onlyFilled({
                transfer_clinic: this.cashier.clinic_id,
            });
        },
        isInvalidAmount() {
            let amount = Number(this.model.amount);
            return isNaN(amount) || amount <= 0;
        },
    },
}
