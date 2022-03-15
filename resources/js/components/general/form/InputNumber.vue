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
        <el-input-number
            v-model="model"
            :placeholder="placeholderText"
            :step="step"
            :min="min"
            :max="max"
            :disabled="disabled"
            :size="controlSize"
            :controls-position="controlsPosition"
            @change="changed"
        />
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';

export default {
    mixins: [
        FormElement,
    ],
    props: {
        controlsPosition: {
            type: String,
            default: 'right',
        },
        step: {
            type: Number,
            default: 1,
        },
        min: {
            type: Number,
            default: 1,
        },
        max: {
            type: [Number, String],
        },
    },
    computed: {
        placeholderText() {
            if (this.placeholder === true) {
                return __('Укажите значение');
            }
            if (this.placeholder === false) {
                return undefined;
            }
            return this.placeholder;
        },
    },
};
</script>