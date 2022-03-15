<template>
    <section>
        <manage-table
            ref="table"
            :fields="fields"
            :repository="repository"
            :enable-pagination="false"
            :filters="filters"
            >
            <template
                slot="payment_method"
                slot-scope="props"
            >
                <form-select
                    :entity="props.rowData"
                    :options="cashboxList"
                    property="cashbox_id"
                    :error-prefix="`e.${props.rowIndex}`"
                    label=""
                    css-class="m-0"
                    @changed="verifyPayment(props.rowData)"
                />
            </template>
            <template
                slot="laboratory_code"
                slot-scope="props">
                <span>{{ $formatter.listFormat(getLaboratoryCode(props.rowData)) }}</span>

            </template>
            <template
                slot="analysis_name"
                slot-scope="props">
                <span>{{ $formatter.listFormat(getAnalysisName(props.rowData)) }}</span>

            </template>
            <template
                slot="amount"
                slot-scope="props"
            >
                <el-input-number
                    v-model="props.rowData.payed_amount"
                    controls-position="right"
                    :step="1"
                    :min="0"
                    :max="getMaxAmount(props.rowData)"
                    @change="amountAvailability(props.rowData)"
                    class="text-right input-tiny"
                />
            </template>
            <template
            slot="deposit"
            slot-scope="props"
        >
            <el-input-number
                v-model="props.rowData.deposit"
                controls-position="right"
                :step="1"
                :min="0"
                :max="props.rowData.debt"
                class="text-right input-tiny"
                @change="depositAvailability(props.rowData)"
            />
        </template>
            <template
                slot="comment"
                slot-scope="props"
            >
                <el-input
                    v-model="props.rowData.comment"
                    type="textarea"
                    autosize
                    :rows="1"
                    class="table-textarea"
                    :placeholder="__('Добавить текст')"
                />
            </template>
        </manage-table>
        <section class="pt-0">
            <div class="form-footer text-right">
                <span>
                <b>{{ __('Итоговая сумма по всем анализам') }}  {{ $formatter.numberFormat(total) }} {{ __('грн') }}</b>
                / {{ __('Аванс:') }} {{ $formatter.numberFormat(depositAmount) }} {{ __('грн') }}
                </span>

                <el-button
                    @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                   @click="confirm"
                    type="primary">

                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </section>
    </section>

</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {

    props: {
        appointment_id: Number,
        analisys: {
            type: Array,
            default: () => [],
        },
        cashboxList: {
            type: Array,
            default: () => [],
        },
        nonFiscalCashboxId: {
            type: [Number, String],
            default: null,
        },
        total: Number,
        depositAmount: Number,
        totalUsedDeposit: Number
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return  Promise.resolve({
                    rows: this.analisys,
                });
            }),
            fields: [
                {
                    name: 'laboratory_code',
                    title: __('Код лаборатории'),
                    width: '55px',
                },
                {
                    name: 'analysis_name',
                    title: __('Название анализов'),
                    width: '35%',
                },
                {
                    name: 'debt',
                    title: __('Долг, грн'),
                    width: '100px',
                },
                {
                    name: 'amount',
                    title: __('Сумма, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'deposit',
                    title: __('С аванса, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'payment_method',
                    title: __('Форма оплаты'),
                    width: '100px',
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    width: '100px',
                },
                {
                    name: 'by_policy',
                    title: __('Полис'),
                    width: '50px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'franchise',
                    title: __('Фр-за, %'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'patient_payment',
                    title: __('К оплате пациентом, грн'),
                    width: '90px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                },
                {
                    name: 'insurance_payment',
                    title: __('К оплате СК, грн'),
                    width: '75px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                },
            ],
            filters: {
                appointment : this.appointment_id
            }
        };
    },
    methods: {
        verifyPayment(row) {
            this.$nextTick(() => {
                if ((row.deposit > 0) && (row.cashbox_id != this.nonFiscalCashboxId)) {
                    row.cashbox_id = this.nonFiscalCashboxId;
                }

                let selectedCashbox = this.selectCashboxList(row).find((cashbox) => cashbox.id === row.cashbox_id);

                if (selectedCashbox && selectedCashbox.changePaymentDate) {
                    row.created_at_active = true;
                } else {
                    row.created_at_active = false;
                    row.created_at = this.$moment().format('YYYY-MM-DD');
                }

                row.is_cash = this.cashboxList.find((item) => item.id === row.cashbox_id).useCash;
            });
        },
        getLaboratoryCode(row) {
            return row.analysis_items.map(item => {
                return item.laboratory_code
            });
        },
        getAnalysisName(row) {
            return row.analysis_items.map(item => {
                return item.name
            });
        },
        getMaxAmount(row) {
            if (row.prepayed) {
                return Number(row.debt - row.prepayed);
            }
            return Number(row.debt);
        },
        cancel() {
            this.$emit('cancel');
        },

        getTable() {
            return this.$refs.table;
        },
        isValidAmount(amount) {
            return _.isFilled(amount) && amount > 0;
        },
        confirm() {
            let rows = this.$refs.table.getData(),
                valid = true;
            rows.forEach(element => {
                if (this.isValidAmount(element.payed_amount) || this.isValidAmount(element.deposit)) {
                    if(!_.isFilled(element.cashbox_id)) {
                        valid = false;
                        return this.$error(__('Укажите пожалуйста форму оплаты'));
                    }
                }
            });
            if(valid) {
                this.$emit('selected', rows);
            }
        },
        amountAvailability(row) {
            if((row.payed_amount + row.deposit) > row.debt) {
                row.payed_amount = row.debt - row.deposit;
            }
        },
        depositAvailability(row) {
            if ((this.depositAmount == 0) || (this.totalUsedDeposit > this.depositAmount)
                || (row.debt === row.payed_amount)) {
                row.deposit = null;
             } else {
                if ((row.deposit + row.payed_amount) > row.debt) {
                    row.deposit = Number(row.debt - row.payed_amount);
                }

                if (this.nonFiscalCashboxId != null) {
                    row.cashbox_id = this.nonFiscalCashboxId;
                }

                row.cashbox_disabled = true;
                let rows = this.$refs.table.getData();
                this.$emit('changeDeposit',rows);
            }
        },
    }
}
</script>
