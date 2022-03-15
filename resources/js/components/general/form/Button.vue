<template>
    <el-button
        :disabled="disabled"
        :type="type"
        :loading="loading"
        @click="clicked">
        <svg-icon
            v-if="hasIcon"
            :name="icon"
            class="icon-tiny icon-black" />
        <span :class="{'button-title': hasIcon}">
            {{ text }}
        </span>
    </el-button>
</template>
<script>
export default {
    props: {
        text: {
            type: String,
            default: ''
        },
        icon: {
            type: String,
            default: ''
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        type: {
            type: String,
            default: 'default',
        },
    },
    computed: {
        hasIcon() {
            return this.icon.length != 0;
        },
    },
    methods: {
        clicked() {
            this.$emit('click');
        },
    },
}
</script>
