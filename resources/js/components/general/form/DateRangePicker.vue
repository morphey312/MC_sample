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
            value-format="yyyy-MM-dd"
            type="daterange"
            :unlink-panels="unlinkPanels"
            :range-separator="rangeSeparator"
            :start-placeholder="startPlaceholder"
            :end-placeholder="endPlaceholder"
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
        unlinkPanels: {
            type: Boolean,
            default: false,
        },
        startPlaceholder: {
            type: String,
            default: 'C'
        },
        endPlaceholder: {
            type: String,
            default: __('До')
        },
        rangeSeparator: {
            type: String,
            default: '-'  
        },
    },
    data() {
        return {
            format: 'dd/MM/yyyy'
        }
    }
}
</script>