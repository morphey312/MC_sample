<template>
    <div class="sections-wrapper">
        <call-purpose-form :model="model">
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
        </call-purpose-form>
    </div>
</template>

<script>
import EditMixin from '@/mixins/generic-edit';
import CallPurposeForm from './Form.vue';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        CallPurposeForm,
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