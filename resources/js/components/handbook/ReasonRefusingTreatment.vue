<template>
    <manage-handbook
        :title="__('Причины не взятия лечения')"
        category="reason_refusing_treatment"
        permission-group="reason-refusing-treatments"
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