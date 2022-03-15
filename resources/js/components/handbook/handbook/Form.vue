<template>
    <form @submit.prevent="save">
        <form-input-i18n
            :entity="model"
            property="value"
            :label="__('Название')" />
        <form-input
            v-if="modelKey"
            :entity="model"
            property="key"
            :label="__('Код')" />
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                native-type="submit">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </form>
</template>

<script>
import WarnExtChanges from '@/mixins/warn-external-changes';

export default {
    mixins: [
        WarnExtChanges,
    ],
    props: {
        model: Object,
        modelKey: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        save() {
            this.confirmExternalOverwrite(() => {
                let isNew = this.model.isNew();
                this.model.save(!this.modelKey).then(() => {
                    this.$emit(isNew ? 'created' : 'saved', this.model);
                }).catch((e) => {
                    this.$displayErrors(e);
                });
            });
        },
    },
}
</script>