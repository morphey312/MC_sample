<template>
    <manage-handbook
        :title="__('Валюты')"
        category="currency"
        :model-key="true"
        permission-group="currencies"
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