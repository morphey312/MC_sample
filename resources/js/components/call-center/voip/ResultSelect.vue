<template>
    <div>
        <el-select 
            v-model="value" 
            :disabled="disabled"
            :placeholder="__('Результат звонка')">
            <template
                v-for="status in processStatuses">
                <el-option
                    v-if="isNormalResult(status)"
                    :key="status.id"
                    :label="status.value"
                    :value="status.id">
                    {{ status.value }}
                </el-option>
                <template v-else>
                    <el-collapse v-model="expands">
                        <el-collapse-item 
                            :title="__('Обработка невозможна')" 
                            :name="1">
                            <el-option
                                v-for="reason in impossibilityReasons"
                                :key="reason.id"
                                :label="__('Обработка невозможна: {reason}', {reason: reason.value})"
                                :value="reason.id">
                                {{ reason.value }}
                            </el-option>
                        </el-collapse-item>
                    </el-collapse>
                </template>
            </template>
        </el-select>
        <modal
            :header="__('Другая причина')"
            :visible="showOtherReasonDialog"
            :close-on-escape="false"
            @close="discardOtherReason"
            width="400px">
            <el-input 
                v-model="model.unprocessibility_reason_comment"
                :autofocus="true"
                :placeholder="__('Кратко опишите причину')" />
            <div class="form-footer text-right">
                <el-button
                    @click="discardOtherReason">
                    {{ __('Отмена') }}
                </el-button>
                <el-button 
                    :disabled="!model.unprocessibility_reason_comment"
                    type="primary" 
                    @click="acceptOtherReason">
                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </modal>
        <modal
            :header="__('Примечание')"
            :visible="showCommentDialog"
            :close-on-escape="false"
            @close="discardComment"
            width="400px">
            <el-input 
                v-model="model.comment"
                :autofocus="true"
                :placeholder="__('Кратко опишите причину')" />
            <div class="form-footer text-right">
                <el-button
                    @click="discardComment">
                    {{ __('Отмена') }}
                </el-button>
                <el-button 
                    :disabled="!model.comment"
                    type="primary" 
                    @click="acceptComment">
                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </modal>
    </div>
</template>

<script>
import CONSTANTS from '@/constants';
import Modal from '@/components/general/Modal.vue';

export default {
    components: {
        Modal,
    },
    props: {
        model: {
            type: Object,
            required: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            value: '',
            expands: [],
            processStatuses: this.$handbook.getOptions('call_process_status'),
            impossibilityReasons: [
                ...this.$handbook.getOptions('reason_impossibility_of_call_processing'),
                {
                    id: CONSTANTS.PROCESS_LOG.IMPROCESSIBLE_REASON_OTHER,
                    value: __('Другая причина'),
                },
            ],
            showOtherReasonDialog: false,
            showCommentDialog: false,
        };
    },
    methods: {
        isNormalResult(status) {
            return status.id !== CONSTANTS.PROCESS_LOG.STATUS.IMPROCESSIBLE;
        },
        discardOtherReason() {
            this.showOtherReasonDialog = false;
            this.value = null;
        },
        acceptOtherReason() {
            this.model.validateAttribute('unprocessibility_reason_comment').then((err) => {
                if (_.isEmpty(err)) {
                    this.showOtherReasonDialog = false;
                } else {
                    this.$error(_.castArray(err)[0]);
                }
            });
        },
        discardComment() {
            this.showCommentDialog = false;
            this.value = null;
        },
        acceptComment() {
            this.model.validateAttribute('comment').then((err) => {
                if (_.isEmpty(err)) {
                    this.showCommentDialog = false;
                } else {
                    this.$error(_.castArray(err)[0]);
                }
            });
        },
    },
    watch: {
        value(val) {
            if (val === CONSTANTS.PROCESS_LOG.IMPROCESSIBLE_REASON_OTHER) {
                this.model.status = CONSTANTS.PROCESS_LOG.STATUS.IMPROCESSIBLE;
                this.model.unprocessibility_reason = val;
                this.model.unprocessibility_reason_comment = null;
                this.model.comment = null;
                this.showOtherReasonDialog = true;
            } else if (_.find(this.impossibilityReasons, (item) => item.id == val) !== undefined) {
                this.model.status = CONSTANTS.PROCESS_LOG.STATUS.IMPROCESSIBLE;
                this.model.unprocessibility_reason = val;
                this.model.unprocessibility_reason_comment = null;
                this.model.comment = null;
            } else {
                this.model.status = val;
                this.model.unprocessibility_reason = null;
                this.model.unprocessibility_reason_comment = null;
                this.model.comment = null;
                if (val === CONSTANTS.PROCESS_LOG.STATUS.NONPROCESSED) {
                    this.showCommentDialog = true;
                }
            }
        },
        ['model.status'](val) {
            if (!val) {
                this.value = null;
            }
        }
    },
};
</script>