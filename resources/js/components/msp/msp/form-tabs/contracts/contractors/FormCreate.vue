<template>
    <contractor-form 
        :model="model"
        :clinics="clinics">
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
                {{ __('Добавить') }}
            </el-button>
        </div>
    </contractor-form>
</template>

<script>
import MspSubcontractor from '@/models/msp/contract/contractor';
import ContractorForm from './Form.vue';

export default {
    components: {
        ContractorForm,
    },
    props: {
        legalEntity: Object,
        clinics: Object,
    },
    data() {
        return {
            model: new MspSubcontractor({
                ehealth_id: this.legalEntity.id,
	            name: this.legalEntity.name,
                edrpou: this.legalEntity.edrpou,
                type: this.legalEntity.type,
            }),
        }
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.model.validate().then((e) => {
                if (e && Object.keys(e).length !== 0) {
                    this.$displayErrors({errors: e});
                } else {
                    this.$emit('created', this.model);
                }
            });
        },
    }
}
</script>
