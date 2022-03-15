<template>
    <form-row
        :name="property"
        :label="label"
        :required="isRequired"
        :css-class="complexCssClass"
        :error-prefix="errorPrefix">
        <template 
            v-if="$slots['label-addon']"
            slot="label-addon">
            <slot name="label-addon" />
        </template>
        <div class="input-wrapper">
            <el-input
                v-model="model"
                :placeholder="placeholderText"
                :type="activeType"
                :step="step"
                :min="min"
                :max="max"
                :disabled="disabled"
                :readonly="readonly"
                :clearable="clearable"
                :size="controlSize"
                :show-password="type === 'password'"
                @change="changed"
                @input="input"
            />
            <div 
                v-if="type === 'password'"
                class="password-toggle"
                :title="__('Показать/скрыть пароль')"
                @click="togglePassword">
                <svg-icon 
                    class="icon-tiny"
                    :name="activeType === 'password' ? 'lock' : 'unlock'" />
            </div>
        </div>
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';

export default {
    mixins: [
        FormElement,
    ],
    props: {
        type: {
            type: String,
            default: 'text'
        },
        step: {
            type: Number,
        },
        min: {
            type: Number,
        },
        max: {
            type: [Number, String],
        },
    },
    data() {
        return {
            activeType: this.type,
        };
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
        complexCssClass() {
            return [
                this.cssClass,
                `input-type-${this.type}`,
            ];
        },
    },
    methods: {
        togglePassword() {
            this.activeType = this.activeType == 'password' ? 'text' : 'password';
        },
    }
};
</script>