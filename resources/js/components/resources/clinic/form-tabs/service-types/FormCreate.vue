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
                @click="create">
                {{ __('Создать') }}
            </el-button>
            <el-button
                v-if="$can('service-types.send-ehealth') && clinic.ehealth_id"
                type="primary"
                :disabled="isMakingRequest"
                @click="createAndPost">
                {{ __('Создать и отправить в e-Health') }}
            </el-button>
        </div>
    </service-type-form>
</template>

<script>
import ServiceType from '@/models/clinic/service-type';
import ServiceTypeForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import EhealthMixin from './mixins/ehealth';

export default {
    mixins: [
        CreateMixin,
        EhealthMixin,
    ],
    components: {
        ServiceTypeForm,
    },
    props: {
        clinic: Object,
    },
    data() {
        return {
            model: new ServiceType({
                clinic_id: this.clinic.id,
            }),
        }
    },
    methods: {
        createAndPost() {
            this.$clearErrors();
            this.prepareRequest(this.clinic).then(() => {
                this.create();
            });
        },
    },
}
</script>