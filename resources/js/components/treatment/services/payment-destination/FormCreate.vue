<template>
    <destination-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </destination-form>
</template>

<script>
import DestinationForm from './Form.vue';
import PaymentDestination from '@/models/service/payment-destination';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        DestinationForm,
    },
    data() {
        return {
            model: new PaymentDestination(),
        }
    },
}
</script>