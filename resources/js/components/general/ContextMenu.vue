<template>
    <el-popover
        :placement="placement"
        :trigger="trigger"
        v-model="expand">
        <div 
            class="context-menu"
            @click="onClicked">
            <slot />
        </div>
        <svg-icon
            slot="reference"
            name="dots" 
            class="icon-tiny icon-blue icon-outline" />
    </el-popover>
</template>

<script>
export default {
    props: {
        placement: {
            type: String,
            default: 'bottom-start',
        },
        trigger: {
            type: String,
            default: 'click',
        },
        visible: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            expand: this.visible,
        };
    },
    methods: {
        onClicked(e) {
            this.expand = false;
        },
    },
    watch: {
        visible(val) {
            this.expand = val;
        },
    },
}
</script>