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
        <el-input
            v-model="model"
            :rows="autosize ? undefined : rows"
            :autosize="autosize"
            :placeholder="placeholderText"
            :disabled="disabled"
            :readonly="readonly"
            type="textarea"
            @change="changed" />
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';

export default {
    mixins: [
        FormElement,
    ],
    props: {
        rows: {
            type: Number,
            default: 2,
        },
        autosize: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        placeholderText() {
            if (this.placeholder === true) {
                return __('Дополнительное описание');
            }
            if (this.placeholder === false) {
                return undefined;
            }
            return this.placeholder;
        },
    },
};
</script>