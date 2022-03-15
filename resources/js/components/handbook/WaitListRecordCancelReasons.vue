<template>
    <manage-handbook
        :title="__('Причины отмены заявки в листе ожидания')"
        category="wait_list_record_cancel_reason"
        permission-group="wait-list-record-cancel-reasons"
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