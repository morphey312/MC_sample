<template>
    <div
        class="memory-usage"
        :style="{color: color}"
        :title="`${used}MB/${limit}MB`">
        {{ memory }}
    </div>
</template>

<script>
export default {
    data() {
        return {
            memory: 0,
            used: 0,
            limit: 0,
            color: '#EEEEEE'
        }
    },
    created() {
        this.updateTimersListener = () => {
            this.used = Math.round(performance.memory.usedJSHeapSize / 1048576);
            this.limit = Math.round(performance.memory.jsHeapSizeLimit / 1048576);
            this.memory = Math.round(100 * performance.memory.usedJSHeapSize / performance.memory.jsHeapSizeLimit);
            if (this.memory > 60) {
                this.color = '#FF5050';
            } else if (this.memory > 30) {
                this.color = '#FFD342';
            } else {
                this.color = '#EEEEEE';
            }
        }
    },
    mounted() {
        if (window.performance !== undefined && window.performance.memory !== undefined) {
            this.$ticker.on(this.updateTimersListener);
        }
    },
    beforeDestroy() {
        if (window.performance !== undefined && window.performance.memory !== undefined) {
            this.$ticker.off(this.updateTimersListener);
        }
    },
};
</script>
