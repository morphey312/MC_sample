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
        <v-select
            v-if="isIos && !multiple"
            ref="autocomplete"
            v-model="model"
            :title="selectionAsText"
            :placeholder="placeholderText"
            :filterable="false"
            :options="ipadVOptions"
            class="ipad-select"
            style="background-color: #fff"
            @search="onSearch"
        >
            <template slot="no-options">
                {{ __('Поиск...') }}
            </template>
            <template slot="option" slot-scope="option">
                <div class="d-center">
                    {{ getIosOption(option.label || option.id) }}
                </div>
            </template>
            <template slot="selected-option" slot-scope="option">
                <div class="selected d-center">
                    {{ getIosOption(option.label || option.id) }}
                </div>
            </template>
        </v-select>
        <el-select
            v-else
            v-model="model"
            :title="selectionAsText"
            :placeholder="placeholderText"
            :filterable="filterable || repository !== undefined"
            :remote="repository !== undefined"
            :remote-method="lateFetchItems"
            :multiple="multiple"
            :collapse-tags="collapseTags"
            :clearable="clearable || isRequired === false"
            :disabled="disabled"
            :loading="loading"
            :allow-create="allowCreate"
            :size="controlSize"
            :popper-class="popperClass"
            :class="{'filterable': isFilterable, 'collapse-tags' : multiple && collapseTags}"
            @change="changed"
            @remove-tag="changed">
            <slot name="picker-header" />
            <template v-if="groupBy">
                <el-option-group
                    v-for="(groupopts, group) in optionsToDisplay"
                    :key="group"
                    :label="group">
                    <el-option
                        v-for="opt in groupopts"
                        :key="opt.id"
                        :value="opt.id"
                        :label="opt.value">
                        {{ opt.label || opt.value }}
                    </el-option>
                </el-option-group>
            </template>
            <template v-else>
                <el-option
                    v-for="option in optionsToDisplay"
                    :key="option.id"
                    :value="option.id"
                    :label="option.value"
                    :disabled="option.disabled">
                    <slot name="picker-item" :option="option">
                        {{ option.label || option.value }}
                    </slot>
                </el-option>
            </template>
            <slot name="picker-footer" />
        </el-select>
    </form-row>
</template>

<script>
import FormElement from '@/mixins/form-element';
import BaseRepository from '@/repositories/base-repository';

export default {
    mixins: [
        FormElement,
    ],
    props: {
        options: {
            type: [Array, String, Object],
            default: () => [],
        },
        groupBy: {
            type: String,
        },
        multiple: {
            type: Boolean,
            default: false,
        },
        filterable: {
            type: Boolean,
            default: false,
        },
        repository: {
            type: Object,
        },
        debounce: {
            type: [Boolean, Number],
            default: 500,
        },
        limit: {
            type: [Number],
            default: 30,
        },
        collapseTags: {
            type: Boolean,
            default: true,
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
            if (this.multiple && this.collapseTags) {
                let set = _.isArray(this.model) ? this.model : [];
                if (_.isArray(this.optionsToDisplay)) {
                    return this.optionsToDisplay.filter((item) => {
                        return set.indexOf(item.id) !== -1;
                    }).map((item) => item.value).join(', ');
                } else if (_.isPlainObject(this.optionsToDisplay)) {
                    let result = [];
                    Object.keys(this.optionsToDisplay).forEach((key) => {
                        this.optionsToDisplay[key].forEach((item) => {
                            if (set.indexOf(item.id) !== -1) {
                                result.push(item.value);
                            }
                        })
                    });
                    return result.join(', ');
                }
            }
            return '';
        },
        isFilterable(){
            return this.filterable || this.repository !== undefined
        },
        isIos() {
            return navigator.platform.toLowerCase().indexOf('ipad') !== -1;
        }
    },
    data() {
        return {
            optionsToDisplay: [],
            ipadVOptions: [], // для айпадов используем другой компонент, формат данных должен выглядеть немного иначе
            loading: false,
        };
    },
    mounted() {
        if (this.repository !== undefined) {
            this.fetchItems().then(() => {
                this.fetchMissingItems(this.model);
            });
            this.repository.watch(['filters', 'sort'], () => {
                this.fetchItems().then(() => {
                    this.fetchMissingItems(this.model);
                });
            });
        } else {
            if (_.isArray(this.options)) {
                this.setOptionsToDisplay(this.options);
            } else if (_.isString(this.options)) {
                this.setOptionsToDisplay(this.$handbook.getOptions(this.options));
            } else if (this.options instanceof BaseRepository) {
                this.loadItemsFromRepository();
                this.options.watch(['filters', 'sort'], () => {
                    this.loadItemsFromRepository();
                });
            }
        }
    },
    created() {
        if (this.debounce !== false) {
            this.lateFetchItems = _.debounce(this.fetchItems, this.debounce);
        } else {
            this.lateFetchItems = this.fetchItems;
        }
    },
    methods: {
        removed() {
            this.$emit('removed', this.model);
        },
        fetchItems(query = '') {
            if (String(query).length >= this.minQueryLen) {
                this.loading = true;
                return this.repository.fetchList({query}, null, this.limit).then((data) => {
                    this.loading = false;
                    this.setOptionsToDisplay(data);
                });
            } else {
                this.setOptionsToDisplay([]);
                return Promise.resolve();
            }
        },
        fetchMissingItems(val, old) {
            if (!_.isArray(val)) {
                val = val ? [val] : [];
            }
            if (!_.isArray(old)) {
                old = old ? [old] : [];
            }
            let missing = val.filter((v) => {
                return old.indexOf(v) === -1
                    && _.find(this.optionsToDisplay, (opt) => opt.id == v) === undefined;
            });
            if (missing.length !== 0) {
                this.repository.fetchList({id: val}).then((data) => {
                    this.setOptionsToDisplay(data);
                });
            }
        },
        setOptionsToDisplay(data) {
            if (this.groupBy) {
                this.optionsToDisplay = _.groupBy(data, this.groupBy);
            } else {
                this.optionsToDisplay = data;
            }
        },
        loadItemsFromRepository() {
            this.setOptionsToDisplay([]);
            this.loading = true;
            this.options.fetchList().then((result) => {
                this.loading = false;
                this.setOptionsToDisplay(result);
                if (this.multiple) {
                    this.model = this.model.filter((id) => {
                        return _.findById(result, id);
                    });
                } else if (_.findById(result, this.model) === undefined) {
                    this.model = null;
                }
            });
        },
        getAvailableOptions() {
            return this.optionsToDisplay;
        },
        onSearch(query) {
            if(query.length) {
                this.lateFetchItems(query);
            }
        },
        getIosOption(label) {
            return _.find(this.optionsToDisplay, ['id', label]) ? _.find(this.optionsToDisplay, ['id', label]).value : label;
        },
    },
    watch: {
        options(val) {
            this.optionsToDisplay = val;
        },
        model(val, old) {
            if (this.repository !== undefined) {
                this.fetchMissingItems(val, old);
            }
        },
        optionsToDisplay(val) {
            if (val) {
                this.ipadVOptions = val.map(item => {
                    return item.id;
                })
            }
        }
    },
};
</script>
