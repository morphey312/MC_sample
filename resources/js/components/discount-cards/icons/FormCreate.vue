<template>
    <icon-form :model="model">
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
    </icon-form>
</template>

<script>
import IconForm from './Form.vue';
import Icon from '@/models/discount-card-type/icon';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        IconForm,
    },
    data() {
        return {
            model: new Icon(),
        }
    },
}
</script>