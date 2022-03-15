<template>
    <document-form :model="model">
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
    </document-form>
</template>

<script>
import DocumentForm from '@/components/resources/employee/documents/Form.vue';

export default {
    components: {
        DocumentForm,
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
