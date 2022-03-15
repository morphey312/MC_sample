<template>
    <mailing-providers-form :model="model">
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
    </mailing-providers-form>
</template>

<script>
import CreateMixin from '@/mixins/generic-create';
import MailingProvidersForm from './Form.vue';
import NotificationMailingProvides from "@/models/notification/mailing-provider";

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        MailingProvidersForm,
    },
    data() {
        return {
            model: new NotificationMailingProvides(),
        }
    },
}
</script>
