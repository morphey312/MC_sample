<template>
    <policy-form 
        :model="model"
        :insurance-companies="insuranceCompanies">
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
        </div>
    </policy-form>
</template>

<script>
import InsurancePolicy from '@/models/patient/insurance-policy';
import PolicyForm from './Form.vue';
import PolicyMixin from './mixin/policy';

export default {
    mixins: [
        PolicyMixin,
    ],
    components: {
        PolicyForm,
    },
    props: {
        patient: Object,
    },
    data() {
        return {
            model: new InsurancePolicy({
                patient_id: this.patient.id,
            }),
        }
    },
    methods: {
        create() {
            this.$clearErrors();
            
            this.model.validate().then((errors) => {
                if (_.isEmpty(errors)) {
                    this.$info(__('Полис был успешно добавлен'));
                    this.$emit('created', this.model); 
                    return;
                }
                return this.$displayErrors({errors});
            });
        },
    },
}
</script>