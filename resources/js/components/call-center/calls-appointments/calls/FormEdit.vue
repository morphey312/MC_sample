<template>
    <call-form 
        :model="model"
        :limit-clinics="$isUpdateLimited('calls')">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button 
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="updateModel">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </call-form>
</template>

<script>
import Call from '@/models/call';
import EditMixin from '@/mixins/generic-edit';
import CallForm from './Form.vue';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        CallForm,
    },
    props: {
        item: {
            type: Object,
        },
    },
    data() {
        return {
            model: this.item.clone(),
        }
    },
    mounted() {
        this.model.fetch();
    },
    methods: {
        updateModel() {
            if (this.waitRecordCancelReasonRequired()) {
                return this.$error(__('Выберите причину отмены заявки в листе ожидания'));
            }
            return this.update();
        },
        waitRecordCancelReasonRequired() {
            let waitListRecord = this.model.wait_list_record;
            return waitListRecord && waitListRecord.id && this.model.waitListReasonRequired && _.isVoid(waitListRecord.cancel_reason);
        },
    }
}
</script>