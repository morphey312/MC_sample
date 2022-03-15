<template>
    <div>
        <p v-if="needUpdateStatus">
            {{ __('Вы покидаете форму приема пациента. Вы хотите завершить этот прием?') }}
        </p>
        <p v-else-if="outpatientRecordUnsaved">
            {{ __('В амбулаторной карте пациента остались несохраненные изменения. Сохранить их сейчас?') }}
        </p>
        <div class="form-footer text-right">
            <el-button
                type="default"
                @click="leave">
                {{ __('Нет, я еще вернусь') }}
            </el-button>
            <el-button
                type="primary"
                @click="complete">
                {{ needUpdateStatus ? __('Завершить прием') : __('Сохранить сейчас') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import lts from '@/services/lts';
import CONSTANT from '@/constants';
import RejectionReasonModal from './RejectionReasonModal.vue';

export default {
    props: {
        outpatientRecord: Object,
        outpatientRecordUnsaved: Boolean,
        needUpdateStatus: Boolean,
        appointment: Object,
        next: Function,
    },
    methods: {
        leave() {
            this.$emit('close', this.next);
        },
        complete() {
            if (this.outpatientRecordUnsaved) {
                this.$clearErrors();
                this.outpatientRecord.save().then((response) => {
                    if (this.outpatientRecord.previousVisit == null && this.appointment.treatment_course_id == null){
                        this.$modalComponent(RejectionReasonModal, {
                            model: this.appointment
                        }, {
                            close: (dialog) => {
                                dialog.close();
                                this.leave();
                            },
                        }, {
                            header: __('Укажите причину отказа лечения'),
                            width: '450px',
                            customClass: 'no-footer',
                        });
                    }else{
                        this.updateAppointment();
                    }
                    delete lts.outpatientRecord;
                }).catch((e) => {
                    this.$displayErrors(e);
                });
            } else {
                this.updateAppointment();
            } 
        },
        updateAppointment() {
            if (this.needUpdateStatus) {
                this.appointment.changeSystemStatus(CONSTANT.APPOINTMENT.STATUSES.COMPLETED).then(() => {
                    this.leave();
                });
            } else {
                this.leave();
            }
        },
    },
}
</script>