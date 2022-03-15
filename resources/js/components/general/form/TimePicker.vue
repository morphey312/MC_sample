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
        <component
            :is="componentName"
            v-model="model"
            :placeholder="placeholderText"
            :picker-options="options"
            :disabled="disabled"
            :readonly="readonly"
            :clearable="clearable"
            :size="controlSize"
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
        pickerOptions: {
            type: Object,
        },
        start: {
            type: String,
        },
        end: {
            type: String,
        },
        step: {
            type: String,
        },
        minTime: {
            type: String,
        },
        maxTime: {
            type: String,
        },
        format: {
            type: String,
            default: 'HH:mm:ss',
        },
        mode: {
            type: String,
            default: 'select',
        },
    },
    computed: {
        componentName() {
            return `el-time-${this.mode}`;
        },
        options() {
            let options;
            
            if (this.mode === 'select') {
                options = _.pick(this, [
                    'start',
                    'end',
                    'step',
                    'minTime',
                    'maxTime',
                ]);

                // TODO: get rid of this
                if(this.pickerOptions) {
                    options = JSON.parse(JSON.stringify(this.pickerOptions));

                    if(this.pickerOptions.min){
                        options.minTime = this.entity[this.pickerOptions.min];
                    }
                }
            } else {
                options = _.pick(this, [
                    'format',
                ]);
                
                if (this.minTime && this.maxTime) {
                    options.selectableRange = `${this.minTime} - ${this.maxTime}`;
                } else if (this.minTime) {
                    options.selectableRange = `${this.minTime} - 23:59:59`;
                } else if (this.maxTime) {
                    options.selectableRange = `00:00:00 - ${this.maxTime}`;
                } else {
                    options.selectableRange = `00:00:00 - 23:59:59`;
                }
            }
                
            return options;
        },
        placeholderText() {
            if (this.placeholder === true) {
                return __('Выберите время');
            }
            if (this.placeholder === false) {
                return undefined;
            }
            return this.placeholder;
        },
    },

    methods: {
        frontToBack(val) {
            if (this.mode === 'picker') {
                return this.$moment(val, this.format).toDate();
            }
            
            return val;
        },
        backToFront(val) {
            if (this.mode === 'picker') {
                return this.$moment(val).format(this.format);
            }
            
            return val;
        },
        changed(val) {
            this.$emit('changed', val);
        },
    }
}
</script>