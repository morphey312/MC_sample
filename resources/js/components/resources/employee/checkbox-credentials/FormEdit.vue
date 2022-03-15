<template>
    <checkbox-credentials-form :model="model">
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
    </checkbox-credentials-form>
</template>

<script>
import CheckboxCredentialsForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    components: {
        CheckboxCredentialsForm,
    },
    mixins: [
        EditMixin,
    ],
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
}
</script>
