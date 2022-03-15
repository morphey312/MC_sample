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
                @click="update">
                {{ __('Обновить') }}
            </el-button>
            <el-button
                v-if="$can('msp-contracts.send-ehealth')"
                type="primary"
                :disabled="isMakingRequest"
                @click="updateAndPost">
                {{ __('Обновить и отправить в e-Health') }}
            </el-button>
        </div>
    </contract-form>
</template>

<script>
import ContractForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';
import EhealthMixin from './mixins/ehealth';

export default {
    mixins: [
        EditMixin,
        EhealthMixin,
    ],
    components: {
        ContractForm,
    },
    props: {
        item: Object,
        msp: Object,
    },
    data() {
        let model = this.item.clone();
        if (_.isFilled(model.ehealth_id)) {
            // Create new update contract request
            model.unset('id');
            model.unset('ehealth_id');
            model.unset('ehealth_request_id');
            model.unset('ehealth_status');
        } else if (_.isFilled(model.ehealth_request_id)) {
            // Create new contract request
            model.unset('id');
            model.unset('ehealth_status');
        }
        return {
            model,
        }
    },
    methods: {
        updateAndPost() {
            this.$clearErrors();
            this.prepareRequest().then(() => {
                this.update();
            });
        },
    },
}
</script>