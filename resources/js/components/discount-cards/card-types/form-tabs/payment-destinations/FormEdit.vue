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
                @click.prevent="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </discount-form>
</template>
<script>
import DiscountForm from './Form.vue';
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
        item: Object,
        discountType: Object,
    },
    data() {
        return {
           	model: this.item.clone(),
        }
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        update() {
        	return this.save('saved');
        },
    },
}
</script>