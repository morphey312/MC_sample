<template>
    <template-form :model="model">
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
    </template-form>
</template>

<script>
import CreateMixin from '@/mixins/generic-create';
import TemplateForm from './Form.vue';
import NotificationTemplate from '@/models/notification/template';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        TemplateForm,
    },
    data() {
        return {
            model: new NotificationTemplate(),
        }
    },
}
</script>