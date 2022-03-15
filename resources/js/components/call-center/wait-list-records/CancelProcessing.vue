<template>
    <div>
        <el-row>
            <el-col :span="24">
                <p>{{ __('Обработка записи в листе ожидания автоматически завершена другим оператором') }}</p>
                <p>{{ __('После закрытия уведомления и завершения действий по обработке, обновите страницу') }}</p>
            </el-col>
        </el-row>
        <el-row class="text-right dialog-footer">
            <el-button @click="cancel">
                {{ __('Отменить/Завершить обработку') }}
            </el-button>
        </el-row>
    </div>
</template>
<script>
export default {
    methods: {
        cancel() {
            this.$emit('cancel');
        }
    }
}
</script>