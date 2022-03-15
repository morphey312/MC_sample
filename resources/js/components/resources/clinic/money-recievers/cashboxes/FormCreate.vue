<template>
    <cashbox-form
        :model="model">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </cashbox-form>
</template>

<script>
import CreateMixin from '@/mixins/generic-create';
import CashboxForm from "./Form";
import MoneyRecieverCashbox from "@/models/money-reciever/cashbox";

export default {
    components: {
        CashboxForm,
    },
    mixins: [
        CreateMixin,
    ],
    props: {
        moneyReciever: Object,
    },
    data() {
        return {
            model: new MoneyRecieverCashbox({
                money_reciever_id: this.moneyReciever.id,
            }),
        }
    },
}
</script>

<style scoped>

</style>
