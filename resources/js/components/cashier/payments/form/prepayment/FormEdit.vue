<template>
    <prepayment-form 
        :model="model"
        :cashbox-list="cashboxList"
        :readonly="true"
        :date="item.created" >
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                v-if="$can('payments.create-clinic')"
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </prepayment-form>
</template>

<script>
import BonusForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';
import PrepaymentForm from './Form.vue';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        PrepaymentForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
            cashboxList: [],
        };
    },
    mounted() {
        this.cashboxList = [
            {
                id: this.item.payment.cashbox_id,
                value: this.item.payment.cashbox_name,
            }
        ];
    }
}
</script>