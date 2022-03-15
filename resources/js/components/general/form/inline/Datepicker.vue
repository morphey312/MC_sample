<template>
    <span class="inline-datepicker" :class="{'disabled' : disabled}">
        <span
            v-if="!editMode"
            class="static"
            @click="focus">
            {{ formatValue(value) }}
        </span>
        <template v-else>
            <div 
                class="inline-picker-backdrop"
                @mousedown="revert" />
            <el-date-picker
                ref="input"
                v-model="val"
                :format="format"
                :clearable="!required"
                :picker-options="pickerOptions"
                :disabled="disabled"
                type="date"
                value-format="yyyy-MM-dd"
                @change="confirm(false)"
                @blur="revertAndMove" />
        </template>
    </span>
</template>
<script>
import InlineInputMixin from '@/mixins/inline-input';
import DatepickerMixin from '@/mixins/datepicker';

export default {
    mixins: [
        InlineInputMixin,
        DatepickerMixin,
    ],
    props: {
        value: String,
        format: {
            type: String,
            default: 'dd/MM/yyyy'
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        focus() {
            this.edit();
            this.$nextTick(() => {
                this.$refs.input.focus();
            });
        },
        focusByTab() {
            this.focus();
        },
        formatValue(value) {
            return this.formatter !== undefined 
                ? this.formatter(value) 
                : this.$formatter.dateFormat(value);
        },
        revertAndMove(e) {
            this.revert();
            this.nextTabIndex();
        }
    }
};
</script>
