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
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </clinic-form>
</template>

<script>
import ClinicForm from './Form.vue';
import MailingSettingsClinic from '@/models/notification/mailing-setting/mailing-clinic';

export default {
    components: {
        ClinicForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: new MailingSettingsClinic({id: this.item.id}),
        }
    },
    mounted() {
        this.model.fetch();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        update() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('saved', response);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    }
}
</script>
