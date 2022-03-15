<template>
    <form 
        :class="['search-filter', {collapsed}]"
        @submit.prevent="sendFilters">
        <drawer 
            v-if="showCollapseButton"
            :open="!collapsed">
            <div class="filter-body">
                <slot></slot>
            </div>
        </drawer>
        <div 
            v-else
            class="filter-body">
            <slot></slot>
        </div>
        
        <div class="filter-buttons">
            <el-button
                v-if="collapsed"
                @click="collapsed = false">
                {{ buttonExpandText }}
            </el-button>
            <template v-else>
                <el-button
                    v-show="showSubmitButton && !collapsed"
                    type="primary"
                    native-type="submit">
                    {{ buttonSubmitText }}
                </el-button>
                <el-button
                    v-if="showClearButton && !collapsed"
                    @click="clearFilters">
                    {{ buttonClearText }}
                </el-button>
                <el-button
                    v-if="showCollapseButton"
                    @click="collapsed = true">
                    {{ buttonCollapseText }}
                </el-button>
            </template>
            <slot name="extra-buttons" />
        </div>
    </form>
</template>

<script>
import lts from '@/services/lts';

export default {
    props: {
        model: {
            type: Object,
            required: true,
        },
        showSubmitButton: {
            type: Boolean,
            default: false,
        },
        showClearButton: {
            type: Boolean,
            default: false,
        },
        showCollapseButton: {
            type: Boolean,
            default: false,
        },
        startCollapsed: {
            type: Boolean,
            default: false,
        },
        buttonSubmitText: {
            type: String,
            default: __('Поиск')
        },
        buttonClearText: {
            type: String,
            default: __('Очистить')
        },
        buttonCollapseText: {
            type: String,
            default: __('Свернуть фильтр')
        },
        buttonExpandText: {
            type: String,
            default: __('Показать фильтр')
        },
        debounce: {
            type: [Boolean, Number],
            default: 1000,
        },
        autoSearch: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            collapsed: this.showCollapseButton 
                && this.startCollapsed,
        };
    },
    watch: {
        model: {
            handler() {
                if (this.autoSearch) {
                    this.lateSendFilters();    
                }
            },
            deep: true,
        },
    },
    created() {
        if (this.debounce !== false) {
            this.lateSendFilters = _.debounce(this.sendFilters, this.debounce);
        } else {
            this.lateSendFilters = this.sendFilters;
        }
    },
    methods: {
        sendFilters() {
            if (_.isFunction(this.lateSendFilters.cancel)) {
                this.lateSendFilters.cancel();
            }
            this.$emit('changed', _.onlyFilled(this.model));
        },
        clearFilters() {
            this.$emit('cleared');
        },
    },
}
</script>
