<template>
    <analysis-form 
        :model="model"
        :limit-clinics="$isCreationLimited('analyses')">
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
    </analysis-form>
</template>

<script>
import AnalysisForm from './Form.vue';
import Analysis from '@/models/analysis';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        AnalysisForm,
    },
    data() {
        return {
            model: new Analysis(),
        }
    },
}
</script>