<template>
    <service-type-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Обновить') }}
            </el-button>
            <el-button
                v-if="$can('service-types.send-ehealth') && clinic.ehealth_id"
                type="primary"
                :disabled="isMakingRequest"
                @click="updateAndPost">
                {{ __('Обновить и отправить в e-Health') }}
            </el-button>
        </div>
    </service-type-form>
</template>

<script>
import ServiceTypeForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';
import EhealthMixin from './mixins/ehealth';

export default {
    mixins: [
        EditMixin,
        EhealthMixin,
    ],
    components: {
        ServiceTypeForm,
    },
    props: {
        item: Object,
        clinic: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        }
    },
    methods: {
        updateAndPost() {
            this.$clearErrors();
            this.prepareRequest(this.clinic).then(() => {
                this.update();
            });
        },
    },
}
</script>