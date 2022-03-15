<template>
    <el-date-picker
        v-model="val"
        format="dd/MM/yyyy"
        value-format="yyyy-MM-dd"
        type="date"
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