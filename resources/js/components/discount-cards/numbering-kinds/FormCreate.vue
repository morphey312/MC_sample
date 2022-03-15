<template>
    <numbering-form :model="model">
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
    </numbering-form>
</template>

<script>
import NumberingForm from './Form.vue';
import NumberingKind from '@/models/discount-card-type/numbering-kind';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        NumberingForm,
    },
    data() {
        return {
            model: new NumberingKind(),
        }
    },
}
</script>