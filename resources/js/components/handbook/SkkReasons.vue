<template>
    <manage-handbook
        :title="__('Причины обращения в СКК')"
        category="skk_reason"
        permission-group="skk-reasons"
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