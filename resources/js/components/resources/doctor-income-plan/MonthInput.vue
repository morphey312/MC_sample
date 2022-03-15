<template>
    <el-input-number
        v-model="model[prop]"
        controls-position="right"
        :step="1"
        :min="0"
        :disabled="editDisabled"
        class="text-right input-tiny" />
</template>
<script>
export default {
    props: {
        model: Object,
        prop: String,
    },
    computed: {
        editDisabled() {
            let reference = this.model._reference;
            if (!this.$can('doctor-income-plans.update-norm')) {
                if (reference[this.prop] == 0) {
                    return false;
                }
                return true;
            }
            return false;
        },
    },
}
</script>