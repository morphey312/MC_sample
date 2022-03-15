<template>
    <div class="range form-input-group">
        <div class="form-input">
            <el-date-picker
                v-model="min"
                format="dd/MM/yyyy"
                value-format="yyyy-MM-dd"
                type="date"
                :placeholder="__('С')"
                size="mini">
            </el-date-picker>
        </div>
        <div class="form-input">
            <el-date-picker
                v-model="max"
                format="dd/MM/yyyy"
                value-format="yyyy-MM-dd"
                type="date"
                :placeholder="__('По')"
                size="mini">
            </el-date-picker>
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
            if (v != _.get(this.value, 'min')) {
                this.$emit('input', {
                    min: v,
                    max: this.max,
                });
                this.changed();
            }
        },
        max(v) {
            if (v != _.get(this.value, 'max')) {
                this.$emit('input', {
                    min: this.min,
                    max: v,
                });
                this.changed();
            }
        },
    },
};
</script>