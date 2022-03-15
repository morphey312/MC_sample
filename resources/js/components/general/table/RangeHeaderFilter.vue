<template>
    <div class="range form-input-group">
        <div class="form-input">
            <el-input 
                v-model="min" 
                :placeholder="__('От')"
                @change="changed" />
        </div>
        <div class="form-input">
            <el-input 
                v-model="max" 
                :placeholder="__('До')"
                @change="changed" />
        </div>
    </div>
</template>
<script>
export default {
    props: {
        value: Object,
    },
    data() {
        return {
            min: _.get(this.value, 'min', null),
            max: _.get(this.value, 'max', null),
        };
    },
    methods: {
        changed() {
            this.$nextTick(() => {
                this.$emit('change');
            });
        },
    },
    watch: {
        value(v) {
            this.min = _.get(v, 'min', null);
            this.max = _.get(v, 'max', null);
        },
        min(v) {
            this.$emit('input', {
                min: v,
                max: this.max,
            });
        },
        max(v) {
            this.$emit('input', {
                min: this.min,
                max: v,
            });
        },
    },
};
</script>