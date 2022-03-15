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
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </policy-form>
</template>

<script>
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
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
    methods: {
        update() {
            this.$clearErrors();
            this.model.validate().then((errors) => {
                if (_.isEmpty(errors)) {
                    this.$info(__('Полис был успешно обновлен'));
                    this.$emit('saved', this.model); 
                    return;
                }
                return this.$displayErrors({errors});
            });
        },
    },
}
</script>