<template>
    <contract-form :model="model">
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
                v-if="$can('msp-contracts.send-ehealth')"
                type="primary"
                :disabled="isMakingRequest"
                @click="createAndPost">
                {{ __('Создать и отправить в e-Health') }}
            </el-button>
        </div>
    </contract-form>
</template>

<script>
import MspContract from '@/models/msp/contract';
import ContractForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import EhealthMixin from './mixins/ehealth';

export default {
    mixins: [
        CreateMixin,
        EhealthMixin,
    ],
    components: {
        ContractForm,
    },
    props: {
        msp: Object,
    },
    data() {
        return {
            model: new MspContract({
                msp_id: this.msp.id,
            }),
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