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
                @click="create">
                {{ __('Создать') }}
            </el-button>
            <el-button
                v-if="$can('clinics.send-ehealth')"
                type="primary"
                :disabled="isMakingRequest"
                @click="createAndPost">
                {{ __('Создать и отправить в e-Health') }}
            </el-button>
        </div>
    </clinic-form>
</template>

<script>
import Clinic from '@/models/clinic';
import ClinicForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import EhealthMixin from './mixins/ehealth';

export default {
    mixins: [
        CreateMixin,
        EhealthMixin,
    ],
    components: {
        ClinicForm
    },
    data() {
        return {
            model: new Clinic(),
        }
    },
    methods: {
        createAndPost() {
            this.$clearErrors();
            this.prepareRequest().then(() => {
                this.create();
            });
        }
    },
}
</script>
