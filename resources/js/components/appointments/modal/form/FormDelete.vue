<template>
    <div>
        <form>
            <template v-if="showReason">
                <el-row :gutter="20">
                    <el-col :span="24">
                        <form-select
                            :entity="model"
                            :options="deleteReasons"
                            property="appointment_delete_reason_id"
                            :label="__('Выберите причину удаления')"
                        />
                        <form-text
                            :entity="model"
                            property="delete_reason_comment"
                            :label="__('Комментарий по поводу удаления записи')"
                        />
                    </el-col>
                </el-row>
                <el-row class="text-center dialog-footer">
                    <el-button @click="cancel">
                        {{ __('Отменить') }}
                    </el-button>
                    <el-button
                        type="primary"
                        @click.prevent="deleteAppointment">
                        {{ saveButtonText }}
                    </el-button>
                </el-row>
            </template>
            <el-row class="text-center pb-10" v-else >
                <el-button @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    @click.prevent="showDeleteReasonForm">
                    {{ __('Удалить') }}
                </el-button>
            </el-row>
        </form>
    </div>
</template>

<script>
import Appointment from '@/models/appointment';
import DeleteReasonRepository from '@/repositories/appointment/delete-reason';

export default {
    props: {
        daySheetData: {
            type: Object,
            required: true
        },
        edit: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            model: null,
            showReason: false,
            deleteReasons: [],
        }
    },
    computed: {
        saveButtonText(){
            return  this.edit ? __('Сохранить') : __('Удалить')
        }
    },
    beforeMount() {
        let appointment = new Appointment();
        this.model = appointment.castToInstance(Appointment, this.daySheetData.appointment);
        if(this.edit){
            this.showDeleteReasonForm();
        }
    },
    methods: {
        cancel() {
            this.model.unset([
                    'appointment_delete_reason_id',
                    'delete_reason_comment',
                    'delete_reason_comment_required'
                ]);

            this.$emit('cancel');
        },
        showDeleteReasonForm() {
            this.showReason = true;
            this.getReasons();
        },
        deleteAppointment() {
            this.$clearErrors();
            this.model.set('is_deleted', true);
            this.setDeleteCommentRequired();
            this.saveModel();
        },
        getReasons() {
            let reason = new DeleteReasonRepository();

            reason.fetchList({notUseForAppointmentDelete: 0}).then((response) => {
                this.deleteReasons = response;
            })
        },
        setDeleteCommentRequired() {
            let reasonId = this.model.appointment_delete_reason_id;

            if (reasonId !== null) {
                let reason = _.find(this.deleteReasons, {id: reasonId});

                if (reason.comment_required) {
                    this.model.set('delete_reason_comment_required', true);
                }
            }
        },
        saveModel() {
            this.model.save().then((response) => {
                this.$info(__('Запись была успешно {action}', {action: this.edit ? __('сохранена') : __('удалена')}));
                this.$emit('deleted', this.daySheetData.appointment.id);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },
}
</script>
