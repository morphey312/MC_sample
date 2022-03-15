<template>
    <div :class="['card-record-section', {'collapsed': !contentVisible}]">
        <el-row :gutter="20">
            <el-col :span="contentVisible ? 5 : 20">
                <toggle-link
                    v-model="expand"
                    :left-caret="true"
                    class="section">
                    {{ label }}
                </toggle-link>
                <slot name="button"></slot>
            </el-col>
            <el-col :span="contentVisible ? 19 : 4">
                <drawer
                    :open="expand"
                    @show="contentShown"
                    @hidden="contentHidden">
                    <slot />
                </drawer>
            </el-col>
        </el-row>
    </div>
</template>

<script>
export default {
    props: {
        label: String,
    },
    data() {
        return {
            expand: true,
            contentVisible: true,
        };
    },
    methods: {
        contentShown() {
            this.contentVisible = true;
        },
        contentHidden() {
            this.contentVisible = false;
        }
    }
};
</script>
