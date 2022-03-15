<template>
    <call-request-form 
        :model="model"
        :limit-clinics="$isUpdateLimited('call-requests')">
        <div slot="buttons" class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отмена') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </call-request-form>
</template>

<script>
import CallRequest from '@/models/call-request';
import CallRequestForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        CallRequestForm
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
}
</script>