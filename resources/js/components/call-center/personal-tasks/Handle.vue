<template>
    <handle-form 
        :model="model"
        :attachments-data="item.attachments_data"
        @patient-selected="cancel">
        <div 
            slot="buttons" 
            slot-scope="data"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="data.countUploads !== 0"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </handle-form>
</template>

<script>
import HandleForm from './HandleForm.vue';
import CONSTANT from '@/constants';
import voipCounters from '@/services/voip/counters';

export default {
    components: {
        HandleForm
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        update() {
            this.$clearErrors();
            this.model.isFeedback = true;
            if (this.model.status === CONSTANT.PERSONAL_TASK.STATUS.NEW) {
                this.model.status = null;
            }
            this.model.save().then((response) => {
                if (this.item.status === CONSTANT.PERSONAL_TASK.STATUS.NEW &&
                    this.model.status !== CONSTANT.PERSONAL_TASK.STATUS.NEW) {
                    voipCounters.tasks--;
                }
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },
}
</script>
