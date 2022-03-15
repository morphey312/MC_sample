<template>
    <protocol-template-form 
        :model="model"
        :limit-clinics="$isUpdateLimited('protocol-templates')">
        <div 
            slot="buttons" 
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </protocol-template-form>
</template>

<script>
import ProtocolTemplateForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        ProtocolTemplateForm
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