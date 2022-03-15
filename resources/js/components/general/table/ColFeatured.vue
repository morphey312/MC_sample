<template>
    <span @click="toggleFeatured">
        <el-tooltip 
            placement="bottom"
            effect="light"
            :open-delay="1000"
            popper-class="light-popover-content">
            <template slot="content">{{ tooltipText }}</template>
            <svg-icon name="star" class="icon-tiny" :class="className"/>
        </el-tooltip>
    </span>
</template>
<script>
export default {
    props: {
        tooltipText: {
            type: String,
            default: '',
        },
        featured: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        className() {
            return this.featured ? 'icon-blue' : 'icon-grey';
        },
    },
    methods: {
        toggleFeatured() {
            this.$emit('featured-changed');
        }
    }
}
</script>