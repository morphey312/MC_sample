<template>
    <div :class="[cssClass, {'invalid': errors.length !== 0}]">
        <div
            v-if="label || $slots['label-addon']"
            class="label-wrapper">
            <label
                v-if="label"
                :for="name"
                class="input-label">
                <span v-html="label"></span>
                <em
                    v-if="required === true"
                    class="required"
                    :title="__('Это поле обязательно для заполнения')">
                    *
                </em>
            </label>
            <template v-if="$slots['label-addon']">
                <span class="label-addon">
                    <slot name="label-addon" />
                </span>
            </template>
        </div>
        <slot />
        <div
            v-if="errors.length !== 0"
            class="error-messages">
            <p v-for="(error, index) in errors" :key="index">
                {{ error }}
            </p>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        label: {
            type: String,
        },
        name: {
            type: String,
            required: true,
        },
        required: {
            type: Boolean,
            default: null,
        },
        cssClass: {
            type: [String, Array, Object],
            default: 'form-input',
        },
        errorPrefix: {
            type: String,
            default: ''
        },
        suppressErrors: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            errors: [],
            isFilter: false,
        };
    },
    mounted() {
        this.isFilter = this.detectFilter();
        if (!this.isFilter) {
            this.$eventHub.$on('validationErrors', this.showErrors);
        }
    },
    beforeDestroy() {
        if (!this.isFilter) {
            this.$eventHub.$off('validationErrors', this.showErrors);
        }
    },
    methods: {

        detectFilter() {
            let p = this.$parent;
            while (p) {
                if (p.$options._componentTag === 'search-filter') {
                    return true;
                }
                p = p.$parent;
            }
            return false;
        },
    },
};
</script>

