<template>
    <transition
        :css="false"
        @enter="enter"
        @after-enter="afterEnter"
        @enter-cancelled="enterCancelled"
        @leave="leave"
        @after-leave="afterLeave">
        <div v-show="open"><div class="content"><slot></slot></div></div>
    </transition>
</template>

<script>
export default {
    props: {
        open: {
            type: Boolean,
            default: false,
        },
        duration: {
            type: Number,
            default: 0.5,
        },
    },
    created() {
        this.timeout = null;
    },
    methods: {
        enter (el, done) {
            this.$emit('show');
            let content = el.children[0];
            el.style.height = 0;
            el.className = 'drawer active open';
            el.style.transition = `height ${this.duration}s`;
            this.timeout = setTimeout(() => {
                el.style.height = content.clientHeight + 'px';
                this.timeout = setTimeout(done, this.duration * 1000);
            }, 50);
        },
        afterEnter (el) {
            el.className = 'drawer';
            el.style.height = 'auto';
            el.style.transition = 'none';
            this.$emit('shown');
        },
        enterCancelled (el) {
            el.className = 'drawer';
            el.style.height = 'auto';
            el.style.transition = 'none';
            clearTimeout(this.timeout);
        },
        leave (el, done) {
            this.$emit('hide');
            let content = el.children[0];
            el.style.height = content.clientHeight + 'px';
            el.className = 'drawer active close';
            el.style.transition = `height ${this.duration}s`;
            this.timeout = setTimeout(() => {
                el.style.height = 0;
                this.timeout = setTimeout(done, this.duration * 1000);
            }, 50);
        },
        afterLeave (el) {
            el.className = 'drawer';
            el.style.height = 'auto';
            el.style.transition = 'none';
            this.$emit('hidden');
        },
    },
};
</script>