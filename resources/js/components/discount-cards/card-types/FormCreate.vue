<template>
    <card-type-form :model="model">
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
                {{ __('Создать') }}
            </el-button>
        </div>
    </card-type-form>
</template>

<script>
import CardTypeForm from './Form.vue';
import DiscountCardType from '@/models/discount-card-type';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        CardTypeForm,
    },
    data() {
        return {
            model: new DiscountCardType(),
        }
    },
}
</script>