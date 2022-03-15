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
        <div class="input-wrapper search-input-wrapper">
            <el-input
                ref="term"
                v-model="term"
                :placeholder="placeholderText"
                type="text"
                :disabled="disabled"
                :readonly="readonly"
                :clearable="clearable"
                :size="controlSize"
                tabindex="tabIndex"
                @change="changed">
                <el-popover
                    slot="prepend"
                    trigger="manual"
                    v-model="expand">
                    <div 
                        class="context-menu">
                        <a href="#" @click.prevent="setMode('=')">{{ __('Точное совпадение (=)') }}</a>
                        <a href="#" @click.prevent="setMode('|')">{{ __('Начинается с ... (|)') }}</a>
                        <a href="#" @click.prevent="setMode('~')">{{ __('Содержит ... (~)') }}</a>
                    </div>
                    <span 
                        slot="reference"
                        class="search-mode"
                        @focus="expand=true"
                        @blur="expand=false">
                        {{ mode }}
                    </span>
                </el-popover>
            </el-input>
        </div>
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';

export default {
    mixins: [
        FormElement,
    ],
    data() {
        return {
            mode: '=',
            term: '',
            expand: false,
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
    },
    mounted() {
        this.setValues(this.model);
    },
    methods: {
        setMode(mode) {
            this.mode = mode;
            this.expand = false;
            this.$refs.term.focus();
        },
        setValues(v) {
            if (typeof v === 'string') { 
                var prefix = v.substr(0, 1);
                if (['=', '~', '|'].indexOf(prefix) !== -1) {
                    this.mode = prefix;
                    this.term = v.substr(1);
                    return;
                }
            }
            this.mode = '=';
            this.term = v || '';
        },
    },
    watch: {
        mode(v) {
            this.model = this.term ? `${v}${this.term}` : '';
        },
        term(v) {
            this.model = v ? `${this.mode}${v}` : '';
        },
        model(v) {
            this.setValues(v);
        },
    }
};
</script>
