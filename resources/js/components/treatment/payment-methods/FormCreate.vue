<template>
    <payment-method-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create" >
                {{ __('Добавить') }}
            </el-button>
        </div>
    </payment-method-form>
</template>

<script>
import PaymentMethodForm from './Form.vue';
import PaymentMethod from '@/models/payment-method';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        PaymentMethodForm,
    },
    data() {
        return {
            model: new PaymentMethod(),
        }
    },
}
</script>