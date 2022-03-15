<template>
    <laboratory-form :model="model">
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
    </laboratory-form>
</template>

<script>
import LaboratoryForm from './Form.vue';
import Laboratory from '@/models/analysis/laboratory';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        LaboratoryForm,
    },
    data() {
        return {
            model: new Laboratory(),
        }
    },
}
</script>