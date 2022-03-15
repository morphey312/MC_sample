<template>
    <analysis-form 
        :model="model"
        :limit-clinics="$isUpdateLimited('analyses')">
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
    </analysis-form>
</template>

<script>
import AnalysisForm from './Form.vue';
import Analysis from '@/models/analysis';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        AnalysisForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: new Analysis(),
        };
    },
    mounted() {
        this.model.set('id', this.item.id);
        this.model.fetch();
    },
};
</script>