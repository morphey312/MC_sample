<template>
    <div :class="['alert', `alert-${type}`]">
        <svg-icon
            v-if="iconName"
            :name="iconName"
            class="icon-small" />
        <div class="alert-text">
            <slot />
        </div>
    </div>
</template>

<script>
const ICONS = {
    'info':       'info-alt',
    'skk':        'info-alt',
    'black-mark': 'warning',
    'attention':  'warning',
    'do_not_take_payment':  'info-alt',
    'conference': 'conference',
};

export default {
    props: {
        type: {
            type: String,
            default: 'info',
        },
        icon: {
            type: String,
        },
    },
    computed: {
        iconName() {
            return this.icon || ICONS[this.type];
        },
    },
}
</script>
