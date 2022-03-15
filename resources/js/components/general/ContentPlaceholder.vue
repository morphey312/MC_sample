<template>
    <div 
        v-loading="true"
        class="content-placeholder"
        :style="{height: height}">
        <slot />
    </div>
</template>

<script>
export default {
    props: {
        height: {
            type: String,
            default: '300px',
        },
    },
};
</script>