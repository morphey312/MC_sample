<template>
    <clinic-form :model="model">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </clinic-form>
</template>
<script>
import ClinicForm from './Form.vue';
import MailingSettingsClinic from '@/models/notification/mailing-setting/mailing-clinic';

export default {
    components: {
        ClinicForm
    },
    props: {
        notificationMailingTemplate: Object,
    },
    data() {
        return {
            model: new MailingSettingsClinic({
                notification_mailing_template_id: this.notificationMailingTemplate.id,
            }),
        }
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('created', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },
}
</script>
