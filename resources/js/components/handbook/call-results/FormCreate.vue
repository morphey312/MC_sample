<template>
    <call-result-form :model="model">
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
                {{ __('Добавить') }}
            </el-button>
        </div>
    </call-result-form>	
</template>

<script>
import CreateMixin from '@/mixins/generic-create';
import CallResultForm from './Form.vue';
import CallResult from '@/models/calls/result';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        CallResultForm,
    },
    data() {
        return {
            model: new CallResult(),
        }
    },
}
</script>