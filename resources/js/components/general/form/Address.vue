<template>
    <form-row
        :name="property"
        :label="label"
        :required="isRequired"
        :css-class="cssClass"
        :error-prefix="errorPrefix"
    >
        <template
            v-if="$slots['label-addon']"
            slot="label-addon"
        >
            <slot name="label-addon" />
        </template>
        <el-select
            ref="autocomplete"
            v-model="model"
            :title="selectionAsText"
            :placeholder="placeholderText"
            :filterable="true"
            class="filterable"
            :remote="true"
            :remote-method="search"
            :clearable="clearable || isRequired === false"
            :disabled="disabled"
            :loading="loading"
            :popper-class="popperClass"
            @change="changed"
            @remove-tag="changed"
        >
            <slot name="picker-header" />
            <el-option
                v-for="(option, index) in options"
                :key="index"
                :value="option.value"
                :label="option.value"
                :disabled="option.disabled"
            >
                <slot
                    name="picker-item"
                    :option="option"
                >
                    {{ option.label || option.value }}
                </slot>
            </el-option>
            <slot name="picker-footer" />
        </el-select>
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';

export default {
    mixins: [
        FormElement,
    ],
    props: {
        country: {
            type: String,
            default: 'UA',
        },
        types: {
            type: Array,
            default: () => ['(cities)']
        },
        components: {
            type: Array,
            default: () => ['locality']
        },
        debounce: {
            type: [Boolean, Number],
            default: 1000,
        },
        popperClass: {
            type: String,
        },
        minChar: {
            type: Number,
            default: 3,
        },
    },
    data() {
        return {
            autocomplete: null,
            options: [],
            loading: false,
        }
    },
    computed: {
        placeholderText() {
            if (this.placeholder === true) {
                return __('Выберите из списка');
            }
            if (this.placeholder === false) {
                return undefined;
            }
            return this.placeholder;
        },
        selectionAsText() {
            return this.model;
        },
    },
    mounted() {
        this.autocomplete = new google.maps.places.AutocompleteService();
    },
    methods: {
        search: _.debounce(function (query) {
            if(query && query.length >= this.minChar){
                this.autocomplete.getPlacePredictions(
                    {
                        componentRestrictions: {
                            country: this.country,
                        },
                        input: query,
                        types: this.types,
                        fields: ['address_components', 'name'],
                    }, this.autocompleteCallback);
            }
        }, this.debounce),
        autocompleteCallback(places) {
            if (places && places.length) {
                this.options = places.map((city) => {
                    return {
                        id: city.place_id,
                        label: city.description,
                        value: city.structured_formatting.main_text,
                    };
                })
            } else {
                this.options = [];
            }
        },
    },
};
</script>
