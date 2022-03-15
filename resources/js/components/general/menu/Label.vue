<template>
    <div>
       <svg-icon v-if="icon" :name="icon" class="icon-white" />
       <span class="title-item">
           <slot/>
       </span>
    </div>
</template>

<script>
export default {
    props: {
        icon: {
            type: String
        },
    },
}
</script>