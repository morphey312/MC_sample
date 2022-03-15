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
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </contractor-form>
</template>

<script>
import ContractorForm from './Form.vue';

export default {
    components: {
        ContractorForm,
    },
    props: {
        item: Object,
        clinics: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        update() {
            this.model.validate().then((e) => {
                if (e && Object.keys(e).length !== 0) {
                    this.$displayErrors({errors: e});
                } else {
                    this.item.set(this.model.attributes);
                    this.$emit('saved', this.model);
                }
            });
        },
    }
}
</script>
