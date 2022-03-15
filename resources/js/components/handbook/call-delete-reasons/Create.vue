<template>
    <reason-form :model="model">
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
    </reason-form>
</template>

<script>
import CreateMixin from '@/mixins/generic-create';
import ReasonForm from './Form.vue';
import CallDeleteReason from '@/models/calls/delete-reason';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ReasonForm,
    },
    data() {
        return {
            model: new CallDeleteReason(),
        }
    },
}
</script>