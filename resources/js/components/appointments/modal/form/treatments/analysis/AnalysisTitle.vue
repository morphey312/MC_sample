<template>
    <div class="has-icon">
        <div class="ellipsis">
            {{ model.analysis.name }}
        </div>
        <el-popover
            v-if="showMessage"
            placement="bottom"
            min-width="340px"
            trigger="click">
            <b class="color-danger">{{ __('Назначенного анализа нет в клинике записи') }}</b>
            <template slot="reference">
                <svg-icon name="info-alt" class="icon-tiny icon-red" />
            </template>
        </el-popover>
    </div>
</template>
<script>
export default {
    props: {
        model: {
            type: Object,
            default: () => ({})
        },
        showMessage: {
            type: Boolean,
            default: false,
        }
    },
}
</script>