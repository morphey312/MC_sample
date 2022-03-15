<template>
    <source-form 
        :model="model"
        :limit-clinics="$isCreationLimited('information-sources')">
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
    </source-form>
</template>

<script>
import SourceForm from './Form.vue';
import InformationSource from '@/models/patient/information-source';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        SourceForm,
    },
    data() {
        return {
            model: new InformationSource(),
        }
    },
}
</script>