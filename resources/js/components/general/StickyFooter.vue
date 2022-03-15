<template>
    <div 
        class="sticky-footer-container"
        :style="{'min-height': (threshold + 5) + 'px'}">
        <div 
            ref="track" 
            :style="`top: ${threshold}px`"
            class="track" />
        <slot />
        <div 
            v-show="showFooter"
            class="sticky-footer">
            <slot name="footer" />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        threshold: {
            type: Number,
            default: 200,
        },
    },
    data() {
        return {
            showFooter: false,
        };
    },
    created() {
        this.observer = new IntersectionObserver(this.updateVisibility);
    },
    mounted() {
        this.$nextTick(() => {
            this.observer.observe(this.$refs.track);
        });
    },
    destroyed() {
        this.observer.disconnect();
    },
    methods: {
        updateVisibility(entries) {
            this.showFooter = entries[0].isIntersecting
                || entries[0].boundingClientRect.top <= 0;
        },
    },
};
</script>