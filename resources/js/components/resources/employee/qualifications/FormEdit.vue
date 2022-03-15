<template>
    <qualification-form :model="model">
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
    </qualification-form>
</template>

<script>
import QualificationForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        QualificationForm,
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
