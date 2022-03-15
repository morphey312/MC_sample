<template>
    <div 
        class="form-footer text-right">
        <el-button
            v-if="model.isNew() && !hasClinics"
            @click="$emit('cancel')">
            {{ __('Отменить') }}
        </el-button>
        <el-button
            v-if="model.isNew()"
            type="primary"
            @click="$emit('next')">
            {{ __('Далее') }}
        </el-button>
        <el-button
            v-else-if="hasClinics"
            type="primary"
            @click="$emit('complete')">
            {{ __('Завершить') }}
        </el-button>
        <el-button
            v-if="hasClinics && $can('employees.send-ehealth')"
            type="primary"
            @click="$emit('completeAndPost')">
            {{ __('Завершить и отправить в e-Health') }}
        </el-button>
    </div>
</template>

<script>
export default {
    props: {
        model: Object,
        hasClinics: Boolean,
    },
}
</script>