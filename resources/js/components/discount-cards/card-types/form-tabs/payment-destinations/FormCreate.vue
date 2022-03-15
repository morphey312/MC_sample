<template>
    <discount-form
        :model="model"
        :destinations="destinations">
        <div 
            slot="buttons" 
            class="dialog-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </discount-form>
</template>
<script>
import DiscountForm from './Form.vue';
import PaymentDestination from '@/models/discount-card-type/payment-destination';
import PaymentSaveMixin from './mixin/save';

export default {
    mixins: [
        PaymentSaveMixin,
    ],
    components: {
        DiscountForm,
    },
    props: {
        destinations: Array,
        discountType: Object,
    },
    data() {
        return {
            model: new PaymentDestination(),
        }
    },
    watch: {
        ['model.payment_destination_id'](val) {
            let current = this.destinations.find(item => item.id == val);
            this.model.name = current.value;
        },
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.model.id = this.model.getId();
            return this.save();            
        },
    },
}
</script>