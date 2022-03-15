<template>
    <div class="el-input el-input--mini el-input-group el-input-group--prepend el-input--suffix header-input-filter search-input-wrapper">
        <div 
            v-if="searchModes"
            class="el-input-group__prepend">
            <span>
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
            </span>
        </div>
        <input 
            v-model="val"
            ref="term"
            class="el-input__inner"
            :placeholder="__('Поиск')"
            @keypress.enter.prevent="changed" />
        <span
            class="el-input__suffix"
            v-if="val && val.length !== 0">
            <span class="el-input__suffix-inner">
                <i 
                    class="el-input__icon el-icon-circle-close el-input__clear"
                    @click="clear" />
            </span>
        </span>
    </div>
</template>

<script>
import BaseRepository from '@/repositories/base-repository';

export default {
    props: {
        value: {
            type: [String, Number],
        },
        searchModes: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            val: null,
            mode: '=',
            expand: false,
        };
    },
    mounted() {
        this.setValues(this.value);
    },
    methods: {
        changed() {
            this.$nextTick(() => {
                this.$emit('change');
            });
        },
        clear() {
            this.setValues(null);
            this.changed();
        },
        setMode(mode) {
            this.mode = mode;
            this.expand = false;
            this.$refs.term.focus();
        },
        setValues(v) {
            if (this.searchModes) {
                if (typeof v === 'string') { 
                    var prefix = v.substr(0, 1);
                    if (['=', '~', '|'].indexOf(prefix) !== -1) {
                        this.mode = prefix;
                        this.val = v.substr(1);
                        return;
                    }
                }
                this.mode = '=';
                this.val = v || '';
            } else {
                this.val = v;
            }
        },
        getValue(v, m) {
            if (this.searchModes) {
                return v ? `${m}${v}` : '';
            } else {
                return v;
            }
        }
    },
    watch: {
        value(v) {
            this.setValues(v);
        },
        mode(v) {
            this.$emit('input', this.getValue(this.val, v));
        },
        val(v) {
            this.$emit('input', this.getValue(v, this.mode));
        },
    },
};
</script>