<template>
    <div class="sections-wrapper">
        <call-purpose-form :model="model">
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
        </call-purpose-form>
    </div>
</template>

<script>
import CallPurposeForm from './Form.vue';
import CallRequestPurpose from '@/models/call-request/purpose';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        CallPurposeForm,
    },
    data() {
        return {
            model: new CallRequestPurpose(),
        }
    },
}
</script>