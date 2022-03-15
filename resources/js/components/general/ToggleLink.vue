<template>
    <a 
        :class="cssClass"
        href="#" 
        @click.prevent="toggle">
        <span v-if="leftCaret" :class="{caret: true, toggled: value}"></span>
        <slot></slot>
        <span v-if="!leftCaret" :class="{caret: true, toggled: value}"></span>
    </a>
</template>

<script>
export default {
    props: {
        value: {
            type: Boolean,
        },
        leftCaret: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        cssClass() {
            return [
                'toggle-link', 
                this.class,
            ];
        },
    },
    methods: {
        toggle() {
            this.$emit('input', !this.value);
        },
    },
};
</script>