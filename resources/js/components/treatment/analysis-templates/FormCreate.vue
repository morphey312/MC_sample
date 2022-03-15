<template>
    <analysis-template-form 
        :model="model"
        :limit-clinics="$isCreationLimited('analysis-templates')">
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
    </analysis-template-form>
</template>

<script>
import AnalysisTemplateForm from './Form.vue';
import AnalysisTemplate from '@/models/analysis/template';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        AnalysisTemplateForm,
    },
    data() {
        return {
            model: new AnalysisTemplate(),
        }
    },
}
</script>