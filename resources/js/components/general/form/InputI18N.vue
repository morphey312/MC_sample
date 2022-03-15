<template>
    <form-row
        :name="property"
        :label="label"
        :required="isRequired"
        :css-class="cssClass"
        :error-prefix="errorPrefix">
        <template slot="label-addon">
            <el-popover
                placement="top"
                width="400"
                trigger="click">
                <div 
                    v-for="input in otherInputs" 
                    :key="input.key">
                    <form-row
                        :name="input.property"
                        :label="`${label} (${input.name})`"
                        :css-class="cssClass"
                        :error-prefix="errorPrefix">
                        <div class="input-wrapper">
                            <el-input
                                v-model="locales[input.property]"
                                :placeholder="placeholderText"
                                type="text"
                                :disabled="disabled"
                                :readonly="readonly"
                                :clearable="input.property === property ? clearable : true"
                                :size="controlSize" />
                        </div>
                    </form-row>
                </div>
                <span 
                    :title="__('Варианты переводов')"
                    class="ancor"
                    slot="reference">舌</span>
            </el-popover>
            <template v-if="$slots['label-addon']">
                <slot name="label-addon" />
            </template>
        </template>
        <div class="input-wrapper">
            <el-input
                v-model="locales[mainInput]"
                :placeholder="placeholderText"
                type="text"
                :disabled="disabled"
                :readonly="readonly"
                :clearable="clearable"
                :size="controlSize"
                @change="changed" />
        </div>
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';
import translationServer from '@/services/translation';

export default {
    mixins: [
        FormElement,
    ],
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
    data() {
        let locales = {};
        let userLocales = this.$store.state.user.locales;
        let mainLocale = translationServer.lang;
        let mainInput = this.property;
        let otherInputs = [];

        Object.keys(userLocales).forEach((key) => {
            let suffix = userLocales[key].suffix;
            let property = suffix ? `${this.property}_${suffix}` : this.property;
            locales[property] = this.frontToBack(this.deepGet(this.entity, property));
            if (key === mainLocale) {
                mainInput = property;
            } else {
                otherInputs.push({
                    key,
                    property,
                    name: userLocales[key].name,
                });
            }
        });

        return {
            locales,
            userLocales,
            mainInput,
            otherInputs,
        };
    },
    mounted() {
        Object.keys(this.userLocales).forEach((key) => {
            let suffix = this.userLocales[key].suffix;
            let property = suffix ? `${this.property}_${suffix}` : this.property;
            this.$watch('$props.entity.' + property, (val) => {
                this.locales[property] = this.frontToBack(val);
            });
            this.$watch('$data.locales.' + property, (val) => {
                this.deepSet(this.entity, property, this.backToFront(val));
            });
        });
    }
};
</script>