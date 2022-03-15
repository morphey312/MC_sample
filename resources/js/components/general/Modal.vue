<template>
    <el-dialog
        :visible="visible"
        :width="width"
        :top="top"
        :before-close="close"
        :custom-class="customClass"
        :close-on-click-modal="closeOnFocusLost"
        :close-on-press-escape="closeOnEscape"
        :show-close="showClose"
        center
        @closed="closed">
        <template slot="title">
            <h2>{{ header }}</h2>
            <slot name="header-addon" />
        </template>
        <slot></slot>
    </el-dialog>
</template>

<script>
export default {
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        width: {
            type: String,
            default: '50%'
        },
        top: {
            type: String,
            default: '15px',   
        },
        header: {
            type: String,
            default: ''
        },
        customClass: {
            type: String,
            default: ''
        },
        closeOnFocusLost: {
            type: Boolean,
            default: false,
        },
        closeOnEscape: {
            type: Boolean,
            default: true,
        },
        showClose: {
            type: Boolean,
            default: true,
        },
    },
    methods: {
        close() {
            this.$emit('close');
        },
        closed() {
            this.$emit('closed');
        },
    },
}
</script>