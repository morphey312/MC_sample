<template>
    <div 
        class="form-footer text-right">
        <el-button
            @click="$emit('cancel')">
            {{ __('Отменить') }}
        </el-button>
        <el-button
            type="primary"
            @click="$emit('update')">
            {{ __('Сохранить') }}
        </el-button>
        <el-button
            v-if="$can('employees.send-ehealth')"
            type="primary"
            @click="$emit('updateAndPost')">
            {{ __('Сохранить и отправить в e-Health') }}
        </el-button>
    </div>
</template>