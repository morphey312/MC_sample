<template>
    <archive-form :model="model">
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
    </archive-form>
</template>

<script>
import MspArchive from '@/models/msp/archive';
import ArchiveForm from './Form.vue';

export default {
    components: {
        ArchiveForm,
    },
    data() {
        return {
            model: new MspArchive(),
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
