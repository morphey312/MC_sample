<template>
    <div v-loading="model.loading || model.saving">
        <form-select
            :entity="model"
            :options="cashiers"
            property="destination_id"
            :label="__('Выберите сотрудника')"
        />
        <form-input
            :entity="model"
            property="amount"
            :label="__('Сумма, грн')"
            :placeholder="__('Введите сумму')"
        />
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="transfer">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import CreateMixin from '@/mixins/generic-create';
import CashboxMixin from '../mixins/cashbox';
import TransferMixin from '../mixins/transfer';

export default {
    mixins: [
        CreateMixin,
        CashboxMixin,
        TransferMixin,
    ],
    methods: {
        castCashiers(data) {
            let list = [];
            data.forEach((item) => {
                if (item.cashboxes) {
                    let cashboxId = this.getCashNonPrinterCashboxId(item.cashboxes);
                    if (cashboxId) {
                        list.push({
                            id: cashboxId,
                            value: item.value,
                        });
                    }
                }
            });
            return list;
        },
        transfer() {
            if (_.isVoid(this.model.destination_id)) {
                return this.$error(__('Выберите кассира'));
            }

            if (this.isInvalidAmount()) {
                return this.$error(__('Укажите корректное значение суммы перевода'));
            }
            return this.create();
        },
    }
}
</script>
