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
            v-loading="loading"
            :title="selectionAsText"
            :placeholder="placeholderText"
            :filterable="false"
            :options="ipadVOptions"
            :allow-create="allowCreate"
            label="label"
            class="ipad-select"
            @search="onSearch"
        >
            <template slot="no-options">
                {{ __('Поиск...') }}
            </template>
            <template
                slot="option"
                slot-scope="option"
            >
                <div class="d-center">
                    {{ option.label }}
                </div>
            </template>
            <template
                slot="selected-option"
                slot-scope="option"
            >
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
            :options="options"
            :remote="true"
            :remote-method="search"
            :clearable="clearable || isRequired === false"
            :disabled="disabled"
            :loading="loading"
            :allow-create="allowCreate"
            :popper-class="popperClass"
            @change="changed"
            @remove-tag="changed"
        >
            <slot name="picker-header" />
            <el-option
                v-for="(option, index) in options"
                :key="index"
                :value="option.value"
                :label="option.label"
                :disabled="option.disabled"
            >
                <slot
                    name="picker-item"
                    :option="option"
                >
                    <div style="display:flex; justify-content: space-between;">
                        <div style="max-width: 260px;text-overflow: ellipsis;overflow: hidden;">
                            <span :title="option.label ? option.label : option.value">
                                {{
                                    option.label || option.value
                                }}
                            </span>
                        </div>
                        <svg-icon
                            v-show="option.value.is_apteka24"
                            name="apteka24"
                            class="icon-small icon-blue"
                        ></svg-icon>
                    </div>
                </slot>
            </el-option>
            <slot name="picker-footer" />
        </el-select>
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';
import 'vue-select/dist/vue-select.css';

export default {
    mixins: [
        FormElement,
    ],
    props: {
        debounce: {
            type: [Boolean, Number],
            default: 1000,
        },
        popperClass: {
            type: String,
        },
        minQueryLen: {
            type: Number,
            default: 1,
        },
        allowCreate: {
            type: Boolean,
            default: false,
        },
        clinicWorksWithApteka24: {
            type: Boolean,
            default: false,
        },
        featuredList: {
            type: Array,
            default: () => []
        },
    },
    data() {
        return {
            options: this.featuredList,
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
        isIos() {
            return navigator.platform.toLowerCase().indexOf('ipad') !== -1;
        },
    },
    methods: {
        onSearch(query) {
            if (query.length) {
                this.search(query);
            }
        },
        search: _.debounce(function (query) {
            if (this.clinicWorksWithApteka24 && query && query.length >= 3) {
                axios.get('/api/v1/apteka24/search/autocomplete',
                    {
                        before: (xhr) => {
                            this.loading = true;
                        },
                        params: {
                            query: query,
                        },
                    }).then((response) => {
                    this.options = this.autocompleteCallback(response, query)
                },
                (error) => {
                    this.loading = false;
                });
            } else {
                this.options = this.searchFeaturedList(query);
            }
        }, this.debounce),
        changed(option) {
            if (typeof option === 'object') {
                this.entity.is_apteka24 = this.model.is_apteka24;
                this.entity.name = this.model.value;
                this.entity.apteka24_id = option.apteka24_id;
            } else {
                this.entity.name = option;
            }

        },
        /**
         * @param {*[]} query
         */
        searchFeaturedList(query) {
            return this.featuredList.filter((featured) => {
                return featured.name.toLowerCase().includes(query.toLowerCase());
            });
        },
        autocompleteCallback(response, query) {
            this.loading = false;
            let medicines = response.data;

            if (medicines && medicines.searchItems.length) {
                let apteka24Items = medicines.searchItems.filter((medicine) => {
                    return medicine.status.type !== 'unavailable';
                }).map((medicine) => {
                    return {
                        id: medicine.id,
                        label: medicine.title,
                        value: {
                            value: medicine.title,
                            is_apteka24: true,
                            apteka24_id: medicine.id,
                        },

                    };
                })
                return [...this.searchFeaturedList(query), ...apteka24Items];
            }
        },
    },
};
</script>
