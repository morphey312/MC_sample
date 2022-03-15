<template>    
    <msp-form 
        :model="model"
        :modal-component="modalComponent">
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
            <el-button
                v-if="$can('msp.send-ehealth')"
                type="primary"
                :disabled="isMakingRequest"
                @click="updateAndPost">
                {{ __('Сохранить и отправить в e-Health') }}
            </el-button>
        </div>
    </msp-form>
</template>

<script>
import MspForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';
import EhealthMixin from './mixins/ehealth';

export default {
    mixins: [
        EditMixin,
        EhealthMixin,
    ],
    components: {
        MspForm,
    },
    props: {
        item: Object,
        modalComponent: Object,
    },
    data() {
        let model = this.item.clone();
        model.owner = this.item.owner.clone();
        return {
            model,
        };
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