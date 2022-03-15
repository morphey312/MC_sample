<template>
    <manage-handbook
        :title="__('Города')"
        category="city"
        permission-group="cities"
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