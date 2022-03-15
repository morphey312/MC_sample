<template>
    <span class="yes-no-addon">
        <el-checkbox v-model="yes">{{ __('Да') }}</el-checkbox> 
        <span class="separator">/</span>
        <el-checkbox v-model="no">{{ __('Нет') }}</el-checkbox>
    </span>
</template>

<script>
export default {
    props: {
        value: [String, Boolean, Number],
    },
    data() {
        return {
            yes: this.isYes(this.value),
            no: this.isNo(this.value),
        };
    },
    methods: {
        isYes(v) {
            return _.isVoid(v) ? false : Boolean(Number(v)) === true;
        },
        isNo(v) {
            return _.isVoid(v) ? false : Boolean(Number(v)) === false;
        },
    },
    watch: {
        value(v) {
            this.yes = this.isYes(v);
            this.no = this.isNo(v);
        },
        yes(v) {
            if (v === true) {
                this.$emit('input', true);
            } else if (this.no === false) {
                this.$emit('input', null);
            }
        },
        no(v) {
            if (v === true) {
                this.$emit('input', false);
            } else if (this.yes === false) {
                this.$emit('input', null);
            }
        },
    },
}
</script>