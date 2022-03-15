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
        <el-date-picker
            v-model="model"
            :format="format"
            :disabled="disabled"
            :readonly="readonly"
            :placeholder="placeholderText"
            :clearable="clearable"
            :editable="editable"
            :value-format="valueFormat"
            :type="type"
            :picker-options="options"
            :prefix-icon="prefixIcon"
            :size="controlSize" />
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';

export default {
    mixins: [
        FormElement,
    ],
    props: {
        editable: {
            type: Boolean,
            default: true,
        },
        format: {
            type: String,
            default: 'dd/MM/yyyy'
        },
        options: {
            type: Object,
            default: () => ({
                firstDayOfWeek: 1,
            }),
        },
        type: {
            type: String,
            default: 'date'
        },
        prefixIcon: {
            type: String,
            default: 'el-icon-date'
        },
        valueFormat: {
            type: String,
            default: 'yyyy-MM-dd'
        },
    },
    computed: {
        placeholderText() {
            if (this.placeholder === true) {
                return __('Выберите дату');
            }
            if (this.placeholder === false) {
                return undefined;
            }
            return this.placeholder;
        },
    },
}

</script>
