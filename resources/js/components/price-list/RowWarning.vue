<template>
    <svg-icon 
        v-if="message"
        name="warning" 
        class="icon-tiny"
        :title="message" />
</template>


<script>
export default {
    props: {
        message: [String, Boolean],
    },
}
</script>