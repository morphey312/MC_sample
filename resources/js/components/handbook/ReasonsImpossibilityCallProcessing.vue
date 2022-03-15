<template>
    <manage-handbook
        :title="__('Причины невозможности обработки звонка')"
        category="reason_impossibility_of_call_processing"
        permission-group="call-unprocessibility-reasons"
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