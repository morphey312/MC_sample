<template>
    <form>
        <form-text
            :entity="model"
            property="cabinet_deny_reason"
            :label="__('Укажите причину отмены доступа')" />
        <div class="form-footer text-right">
            <el-button
                :disabled="!canSave" 
                @click="$emit('done')"
                type="primary">
                {{ __('Сохранить') }}
            </el-button>
            <el-button @click="$emit('cancel')">
                {{ __('Отмена') }}
            </el-button>
        </div>
    </form>
</template>

<script>
export default {
    props: {
        model: Object,
    },
    computed: {
        canSave() {
            return _.isFilled(this.model.cabinet_deny_reason);
        },
    },
}
</script>