<template>
    <div
        class="card-record-field"
        :class="{growable: isGrowable}"
        :key="field.key"
        :style="{width: space}">
        <div
            v-if="field.label"
            class="prefix"
            :class="classList">
            {{ field.label }}
        </div>
        <sketchpad
            v-if="field.isSketchpad()"
            v-model="field.bond.value"
            :html="field.html"
            :readonly="readonly"
            :width="field.width"
            :height="field.height"></sketchpad>
        <template v-if="field.options">
            <el-checkbox
                v-for="option in field.options"
                v-model="checked[option.key]"
                :key="option.key"
                :label="option.key"
                :disabled="readonly">
                {{ option.label }}
            </el-checkbox>
        </template>
        <template v-if="field.hasInput()">
            <el-input
                v-if="field.multiline"
                v-model="field.bond.value"
                autosize
                :class="{changed: field.changed && !field.label }"
                type="textarea"
                :readonly="readonly" />
            <el-input
                v-else
                v-model="field.bond.value"
                :class="{changed: field.changed && !field.label }"
                :readonly="readonly" />
        </template>
        <ds-input
            v-else-if="field.hasDS()"
            v-model="field.bond.value"
            :readonly="readonly" />
        <div
            v-if="field.suffix"
            class="suffix">
            {{ field.suffix }}
        </div>
    </div>
</template>

<script>
import DsInput from './DS.vue';
import Sketchpad from './Sketchpad';

export default {
    components: {
        DsInput,
        Sketchpad
    },
    props: {
        field: Object,
        space: String,
        readonly: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            checked: this.mapChecked(this.field.options, this.field.bond.option_value),
        };
    },
    computed: {
        isGrowable() {
            return this.field.hasInput();
        },
        classList() {
            let className = this.field.className || '';
            return [(this.field.changed || this.field.touched) ? 'changed' : '', className];
        },
    },
    methods: {
        updateOptions(val) {
            if (_.isArray(this.field.bond.option_value)) {
                this.field.bond.option_value = this.checkedKeys(val);
            } else {
                let checked = this.checkedKeys(val);
                if (checked.length === 0) {
                    this.field.bond.option_value = null;
                } else if (checked.length === 1) {
                    this.field.bond.option_value = checked[0];
                } else {
                    this.field.bond.option_value = _.find(checked, (key) => key !== this.field.bond.option_value);
                    _.without(checked, this.field.bond.option_value).forEach((key) => {
                        this.checked[key] = false;
                    });
                }
            }
        },
        mapChecked(options, values) {
            let map = {};
            if (options) {
                options.forEach((option) => {
                    if (_.isArray(values)) {
                        map[option.key] = values.indexOf(option.key) !== -1;
                    } else {
                        map[option.key] = values === option.key;
                    }
                });
            }
            return map;
        },
        checkedKeys(val) {
            return Object.keys(val).filter((key) => val[key]).map((key) => Number(key));
        },
    },
    watch: {
        checked: {
            handler(val) {
                this.updateOptions(val);
            },
            deep: true,
        },
        field: {
            handler(val) {
                if(this.field.bond.option_value === null && this.checked){
                    let tempChecked = {};

                    Object.keys(this.checked).forEach((el) => {
                        tempChecked[el] = false;
                    });

                    this.checked = tempChecked;
                }

                this.$emit('data-changed');
            },
            deep: true,
        },
    },
};
</script>
