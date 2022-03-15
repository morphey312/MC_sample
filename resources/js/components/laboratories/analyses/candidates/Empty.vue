<template>
    <section class="form-tab p-0">
        <div class="empty-analysis-list" :class="listClass">
            <div class="empty-analysis-wrapper" :class="wrapperClass">
                <div v-if="showImage" class="empty-img"></div>
                <slot />
            </div>
        </div>
    </section>
</template>

<script>
export default {
    props: {
        listClass: {
            type: String,
            default: '',
        },
        wrapperClass: {
            type: String,
            default: '',
        },
        showImage: {
            type: Boolean,
            default: true,
        }
    },
}   
</script>