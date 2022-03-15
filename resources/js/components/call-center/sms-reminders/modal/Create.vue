<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <form-select
                :entity="model"
                :options="templates"
                property="template_id"
                :clearable="false"
                :label="__('Выберите СМС')"
            />
        </el-row>
        <div
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="save">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </model-form>
</template>

<script>
import SmsReminder from "@/models/sms-reminder";
import CONSTANTS from '@/constants';
import NotificationTemplateRepository from "@/repositories/notification/template";

export default {
    props:{
        appointment: Object
    },
    mounted(){
        this.getPreparationTemplate();
    },
    data() {
        return {
            model: new SmsReminder({
                appointment_id: this.appointment.id,
                type: CONSTANTS.SMS_REMINDERS.TYPE.MANUAL,
                status: CONSTANTS.SMS_REMINDERS.STATUS.NONE,
                scheduled_at: this.$moment().format("YYYY-MM-DD HH:mm:ss")
            }),
            templates: new NotificationTemplateRepository({
                filters: {
                    channel_type: 'sms',
                    scenario: ['sms_appointment_reminder_operator_manual', 'preparation'],
                    disabled: false,
                }
            })
        }
    },
    methods: {
        getNotificationTemplatesFilter() {
            return _.onlyFilled({
                clinic: this.appointment.clinic_id,
                specialization: this.appointment.specialization_id,
                services: this.appointment.services ? this.appointment.services.map((service) => service.id) : null,
                disabled: false,
                channel_type: 'sms',
                scenario: ['sms_appointment_reminder_operator_manual', 'preparation']
            });
        },
        cancel(){
            this.$emit('close');
        },
        getPreparationTemplate(){
            this.templates.fetchList(this.getNotificationTemplatesFilter()).then((response) => {
                if (response[0]) {
                    this.model.template_id = response[0].id;
                }
            });
        },
        save() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$info(__('СМС - напоминание успешно создано'));
                this.cancel();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    }
}
</script>
