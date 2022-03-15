<template>
    <form v-loading="model.loading || model.saving">
        <slot />
    </form>
</template>

<script>
export default {
    props: {
        model: {
            type: Object,
            required: true,
        },
    },
};
</script>