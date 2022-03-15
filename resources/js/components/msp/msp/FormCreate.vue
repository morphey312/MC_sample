<template>
    <msp-form :model="model">
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
                v-if="$can('msp.send-ehealth')"
                type="primary"
                :disabled="isMakingRequest"
                @click="createAndPost">
                {{ __('Создать и отправить в e-Health') }}
            </el-button>
        </div>
    </msp-form>
</template>

<script>
import Msp from '@/models/msp';
import MspForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import EhealthMixin from './mixins/ehealth';

export default {
    mixins: [
        CreateMixin,
        EhealthMixin,
    ],
    components: {
        MspForm,
    },
    data() {
        return {
            model: new Msp(),
            activeTab: 'info',
        }
    },
    methods: {
        createAndPost() {
            this.$clearErrors();
            this.prepareRequest().then(() => {
                this.create();
            });
        },
    },
}
</script>