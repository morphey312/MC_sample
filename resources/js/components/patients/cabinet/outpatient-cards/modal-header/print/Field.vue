<template>
    <div 
        class="card-record-field"
        :class="{growable: isGrowable}"
        :key="field.key"
        :style="{width: space}">
        <div 
            v-if="field.label"
            class="prefix">
            {{ field.label }}
        </div>
        <el-checkbox 
            v-if="field.options" 
            v-for="option in field.options"
            v-model="checked[option.key]"
            :key="option.key"
            :label="option.key">
            {{ option.label }}
        </el-checkbox>
        <template v-if="field.hasInput()">
            <div class="border-bottom field-input">
                {{ field.bond.value }}
            </div>
        </template>
        <ds-input 
            v-else-if="field.hasDS()"
            v-model="field.bond.value" />
        <div 
            v-if="field.suffix"
            class="suffix">
            {{ field.suffix }}
        </div>
    </div>
</template>
<script>
import DsInput from './DS.vue';

export default {
    components: {
        DsInput,
    },
    props: {
        field: Object,
        space: String,
    },
    computed: {
        isGrowable() {
            return this.field.hasInput();
        },
    },
    data() {
        return {
            checked: this.mapChecked(this.field.options, this.field.bond.option_value),
        };
    },
    methods: {
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
    },
};
</script>