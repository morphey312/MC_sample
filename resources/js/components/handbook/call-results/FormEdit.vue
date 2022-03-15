<template>
    <call-result-form :model="model">
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
    </call-result-form>	
</template>

<script>
import EditMixin from '@/mixins/generic-edit';
import CallResultForm from './Form.vue';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        CallResultForm
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
}
</script>