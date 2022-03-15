<template>
    <clinic-form :model="model">
        <div
            slot="buttons"
            class="form-footer text-right"
        >
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="create"
            >
                {{ __('Добавить') }}
            </el-button>
        </div>
    </clinic-form>
</template>
<script>
import SettingsClinic from '@/models/notification/settings/clinic';
import ClinicForm from './Form.vue';

export default {
    components: {
        ClinicForm
    },
    props: {
        notificationTemplate: Object,
    },
    data() {
        return {
            model: new SettingsClinic({
                notification_template_id: this.notificationTemplate.id,
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
