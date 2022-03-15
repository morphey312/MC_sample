<template>
    <protocol-template-form 
        :model="model"
        :limit-clinics="$isCreationLimited('protocol-templates')">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create" >
                {{ __('Добавить') }}
            </el-button>
        </div>
    </protocol-template-form>
</template>

<script>
import ProtocolTemplateForm from './Form.vue';
import ProtocolTemplate from '@/models/patient/card/protocol-template';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ProtocolTemplateForm,
    },
    data() {
        return {
            model: new ProtocolTemplate(),
        }
    },
}
</script>