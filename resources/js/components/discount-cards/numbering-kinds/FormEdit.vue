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
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </numbering-form>
</template>

<script>
import NumberingForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        NumberingForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
};
</script>