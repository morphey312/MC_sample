<template>
    <form-row
        :name="property"
        :label="label"
        :required="isRequired"
        :css-class="cssClass"
        :error-prefix="errorPrefix">
        <template 
            v-if="$slots['label-addon']"
            slot="label-addon">
            <slot name="label-addon" />
        </template>
        <el-radio-group 
            v-model="model"
            :disabled="disabled"
            :readonly="readonly">
            <el-radio-button 
                v-for="option in optionsToDisplay"
                :key="option.id"
                :label="option.id">
                {{ option.value }}
            </el-radio-button>
        </el-radio-group>
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';

export default {
    mixins: [
        FormElement,
    ],
    props: {
        options: {
            type: [Array, String],
            default: () => [],
        },
    },
    data() {
        return {
            optionsToDisplay: [],
        };
    },
    mounted() {
        if (_.isArray(this.options)) {
            this.optionsToDisplay = this.options;
        } else if (_.isString(this.options)) {
            this.optionsToDisplay = this.$handbook.getOptions(this.options);
        }
    },
};
</script>