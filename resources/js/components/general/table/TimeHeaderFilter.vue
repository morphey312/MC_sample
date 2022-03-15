<template>
    <el-time-select
        v-model="val"
        :placeholder="__('Все')"
        size="mini"
        :picker-options="{
            start: '06:00',
            step: '00:05',
            end: '22:00'
        }">
    </el-time-select>
</template>
<script>
export default {
    props: {
        value: String,
    },
    data() {
        return {
            val: this.value,
        };
    },
    watch: {
        value(v) {
            this.val = v;
        },
        val(v) {
            if (v !== this.value) {
                this.$emit('input', v);
                this.$emit('change');
            }
        },
    },
};
</script>