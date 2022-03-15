<template>
    <call-request-form 
        :model="model"
        :limit-clinics="$isCreationLimited('call-requests')">
        <div slot="buttons" class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отмена') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </call-request-form>
</template>

<script>
import CallRequest from '@/models/call-request';
import CallRequestForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        CallRequestForm
    },
    data() {
        return {
            model: new CallRequest(),
        }
    },
}
</script>