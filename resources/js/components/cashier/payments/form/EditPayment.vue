<template>
    <div v-if="loaded">
        <edit-extended-list
            v-if="isExtendedEdit"
            ref="extendedList"
            :model="model"
            :cashbox-list="cashboxList"
            :is-online-cashier="isOnlineCashier"
            :is-cash-and-fiscal="isCashAndFiscal"
            @saved="saved" />
        <edit-list
            v-else
            ref="list"
            :model="model"
            :is-cash-and-fiscal="isCashAndFiscal" />
        <div
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import EditList from './edit/List.vue';
import EditExtendedList from './edit/ExtendedList.vue';
import EditMixin from '@/mixins/generic-edit';
import CONSTANTS from '@/constants';
import BatchRequest from '@/services/batch-request';
import Payment from '@/models/payment';


export default {
    mixins: [
        EditMixin,
    ],
    components: {
        EditList,
        EditExtendedList,
    },
    props: {
        item: Object,
        cashier: Object,
        cashboxes: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            model: {},
            loaded: false,
            isCashAndFiscal: true,
            isExtendedEdit: this.$can('payments.temp-delete-clinic'),
            batchRequest: new BatchRequest('/api/v1/payments/batch'),
            cashboxList: [],
            isOnlineCashier: (this.$store.state.user.system_status === CONSTANTS.EMPLOYEE.SYSTEM_STATUSES.ONLINE_PAYMENT),
        };
    },
    mounted() {
        this.getCashboxList();
    },
    beforeMount() {
        this.isCashAndFiscal = this.getIsCashAndFiscal();
        this.model = new Payment({id: this.item.id});
        this.model.fetch(['service']).then(res => {
            if(this.item.items) {
                this.castModel();
            }
            this.loaded = true;
        });
    },
    computed: {
        newPayedAmount() {
            if (this.getExtendedList()) {
                return this.getExtendedList().newPayedAmount;
            }
            return 0;
        }
    },
    methods: {
        castModel() {
            this.model.items = this.item.items;
            this.model.clinic = this.item.clinic_id;
            this.model.payed_amount = this.item.payed_amount;
              this.model.amount = this.item.amount;
        },
        getCashboxList() {
            if (this.cashboxes.length === 0) {
                return;
            }
            this.cashboxes.forEach((box) => {
                this.cashboxList.push({
                    id: box.id,
                    value: box.payment_method.name,
                })
            });
        },
        getIsCashAndFiscal() {
            if (this.item.cashbox && this.item.cashbox.payment_method) {
                if (this.item.cashbox.payment_method.use_cash == false) {
                    return false;
                }
                return this.item.cashbox.payment_method.clinics.find((clinic) => {
                    return clinic.clinic_id == this.item.clinic_id && clinic.is_fiscal == true;
                }) != undefined;
            }
            return false;
        },
        getChangedAttributes() {
            let list = this.getExtendedList();
            let service = list.debtServices.find(service => service.id == this.model.service_id);
            return {
                service_id: service.id,
                doctor_id: this.model.doctor_id,
                appointment_id: service.appointment_id,
                payment_destination_id: service.payment_destination_id,
                amount: this.newPayedAmount,
                payed_amount: this.newPayedAmount,
                cashbox_id: this.model.cashbox_id,
                cashier_id: this.model.cashier_id,
                comment: this.model.comment,
                ...(this.isOnlineCashier ? {
                    created_at: this.model.created_at,
                } : {}),

            }
        },
        getExtendedList() {
            return this.$refs.extendedList;
        },
        amountChanged() {
            return this.newPayedAmount < this.item.payed_amount;
        },
        prepareUpdatePayment(row) {
            let payment = new Payment(row);
            payment.doctor_id = this.model.doctor_id;
            payment.cashbox_id = this.model.cashbox_id;
            return payment;
        },
        saved() {
            this.$emit('saved', this.model)
        },
        update() {
            this.$clearErrors();
            if (this.isExtendedEdit === false) {
                return this.save();
            } else {
                if(this.model.items) {
                    this.batchRequest.reset();
                    this.model.items.forEach(row => {
                        row.comment = this.model.comment
                        this.batchRequest.update(this.prepareUpdatePayment(row));
                    });
                    this.batchRequest.submit().then((result) => {
                        this.$emit('saved', this.model);
                    });
                }else {
                    return this.updateService();
                }
            }
        },
        updateService() {
            this.model.updateService(this.getChangedAttributes()).then((response) => {
                this.$emit('saved', this.item);
            }).catch((e) => {
                this.onSaveError(e);
                this.$displayErrors(e);
            });
        },
        save() {
            this.item.confirmExternalOverwrite().then((response) => {
                this.$emit('saved', this.item);
            }).catch((e) => {
                this.onSaveError(e);
                this.$displayErrors(e);
            });
        },
    },
}
</script>
