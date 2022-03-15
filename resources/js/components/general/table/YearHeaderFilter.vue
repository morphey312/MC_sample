<template>
    <el-date-picker
        v-model="val"
        format="yyyy"
        value-format="yyyy"
        type="year"
        :placeholder="__('Все')"
        :clearable="true"
        size="mini">
    </el-date-picker>
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
                this.$nextTick(() => {
                    this.$emit('change');
                });
            }
        },
    },
};
</script>