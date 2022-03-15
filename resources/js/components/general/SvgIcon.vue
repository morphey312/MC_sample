<template>
    <span 
        :class="cssClass"
        :title="title"
        :disabled="disabled"
        @click="click">
        <svg>
            <use :xlink:href="href"></use>
        </svg><span 
            v-if="$slots.default"
            class="icon-label">
            <slot></slot>
        </span>
    </span>
</template>

<script>
const SPRITE = '/svg/spritesheet/sprite.svg';

export default {
    props: {
        name: {
            type: String,
            required: true,
        },
        disabled: {
            type: Boolean,
            default: false
        },
        title: {
            type: String,
        },
    },
    computed: {
        cssClass() {
            let classes = [
                'svg-icon',
                this.class,
            ];

            if(this.disabled){
                classes.push('disabled');
            }

            return classes;
        },
        href() {
            return `${SPRITE}#${this.name}`;
        },
    },
    methods:{
        click($event) {
            if (!this.disabled) {
                this.$emit('click', $event)
            }
        }
    }
};
</script>
