<template>
    <position-form :model="model">
        <div 
            slot="buttons" 
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </position-form>
</template>

<script>
import PositionForm from './Form.vue';
import Position from '@/models/employee/position';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        PositionForm,
    },
    data() {
        return {
            model: new Position(),
        }
    },
}
</script>