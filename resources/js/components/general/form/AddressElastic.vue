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
        <v-select
            v-if="isIos"
            ref="autocomplete"
            v-model="model"
            :title="selectionAsText"
            :placeholder="placeholderText"
            :filterable="false"
            :options="ipadVOptions"
            label="label"
            class="ipad-select"
            @search="onSearch"
        >
            <template slot="no-options">
                {{ __('Поиск...') }}
            </template>
            <template slot="option" slot-scope="option">
                <div class="d-center">
                    {{ option.label }}
                </div>
            </template>
            <template slot="selected-option" slot-scope="option">
                <div class="selected d-center">
                    {{ option.label }}
                </div>
            </template>
        </v-select>
        <el-select
            v-else
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
import translationServer from '@/services/translation';
import ElasticSearchClient from '@/services/elasticsearch';
import CONSTANTS from '@/constants';
import 'vue-select/dist/vue-select.css';

export default {
    mixins: [
        FormElement,
    ],
    props: {
        country: {
            type: String,
            default: 'UA',
        },
        contexts: {
            type: Object,
            default: () => ({
                "place": ["city", "village", "town"]
            })
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
            elasticSearchClient: new ElasticSearchClient(),
            options: [],
            ipadVOptions: [], // для айпадов используем другой компонент, формат данных должен выглядеть немного иначе
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
        suggesterIndex() {
            return this.elasticSearchClient.getIndexName(CONSTANTS.ELASTICSEARCH.INDICES.CITIES_SUGGESTER);
        },
        isIos() {
            return navigator.platform.toLowerCase().indexOf('ipad') !== -1;
        }
    },
    methods: {
        onSearch(query) {
            if(query.length) {
                this.search(query);
            }
        },
        search: _.debounce(function (query) {
            if (query && query.length >= this.minChar) {
                this.elasticSearchClient.suggest(this.suggesterIndex, query, {
                    field: translationServer.lang !== 'ua' 
                        ? 'suggestions.name_' + translationServer.lang 
                        : 'suggestions.name',
                    contexts: this.contexts,
                }).then((res) => {
                    this.autocompleteCallback(res);
                });
            }
        }, this.debounce),
        autocompleteCallback(places) {
            if (places.length !== 0) {
                this.options = places.map((city) => {
                    return {
                        id: city._id,
                        label: city._source.tags['name:' + translationServer.lang],
                        value: city._source.tags['name'],
                    };
                });
                this.ipadVOptions = places.map((city) => {
                    return city._source.tags['name:' + translationServer.lang] || city._source.tags['name']
                })
            } else {
                this.options = this.ipadVOptions = [];
            }

        },
    },
};
</script>
