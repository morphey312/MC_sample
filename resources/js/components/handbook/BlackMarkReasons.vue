<template>
    <manage-handbook
        :title="__('Причины черной метки')"
        category="black_mark_reason"
        permission-group="black-mark-reasons"
    />
</template>

<script>
import ManageHandbook from './handbook/Manage.vue';

export default {
    components: {
        ManageHandbook,
    },
}
</script>