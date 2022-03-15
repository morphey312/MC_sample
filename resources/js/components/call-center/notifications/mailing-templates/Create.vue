<template>
    <mailing-template-form :model="model">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </mailing-template-form>
</template>

<script>
import CreateMixin from '@/mixins/generic-create';
import MailingTemplateForm from './Form.vue';
import NotificationMailingTemplate from "@/models/notification/mailing-template";

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        MailingTemplateForm,
    },
    data() {
        return {
            model: new NotificationMailingTemplate(),
        }
    },
}
</script>
