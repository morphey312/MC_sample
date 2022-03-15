<template>
    <el-select
        v-model="val"
        :clearable="true"
        size="mini"
        :placeholder="__('Все')"
        :multiple="multiple"
        :collapse-tags="multiple"
        :filterable="true"
        :reserve-keyword="reserveKeyword"
        :remote="isRemote"
        :remote-method="lateFetchItems"
        :loading="loading"
        @change="changed"
        @focus="focused">
        <el-option
            v-if="!multiple"
            :value="null"
            :label="__('Все')" />
        <el-option
            v-for="option in optionsToDisplay"
            :key="option.id"
            :value="option.id"
            :label="option.value" />
    </el-select>
</template>

<script>
import BaseRepository from '@/repositories/base-repository';

export default {
    props: {
        value: {
            type: [Array, String, Number, Object],
        },
        options: {
            type: [Array, String, Object],
            default: () => [],
        },
        multiple: {
            type: Boolean,
            default: false,
        },
        reserveKeyword: {
            type: Boolean,
            default: false,
        },
        limit: {
            type: Number,
            default: 30,
        },
        debounce: {
            type: [Boolean, Number],
            default: 750,
        },
    },
    data() {
        return {
            val: [],
            optionsToDisplay: [],
            loading: false,
            itemsLoaded: false,
        };
    },
    computed: {
        isRemote() {
            return this.options instanceof BaseRepository;
        },
    },
    created() {
        if (this.debounce !== false) {
            this.lateFetchItems = _.debounce(this.fetchItems, this.debounce);
        } else {
            this.lateFetchItems = this.fetchItems;
        }
    },
    mounted() {
        if (this.multiple) {
            this.val = this.value || [];
        } else {
            this.val = this.value;
        }

        if (_.isArray(this.options)) {
            this.optionsToDisplay = this.options;
        } else if (_.isString(this.options)) {
            this.optionsToDisplay = this.$handbook.getOptions(this.options);
        } else if (this.isRemote) {
            this.fetchMissingItems(this.val);
            this.options.watch(['filters', 'sort'], () => {
                this.itemsLoaded = false;
                this.optionsToDisplay = [];
            });
        }
    },
    methods: {
        changed() {
            this.$nextTick(() => {
                this.$emit('change');
            });
        },
        fetchItems(query = '') {
            let filter = query ? {query} : {}
            this.loading = true;
            let repositoryOptions = this.options.getOptions();
            return this.options.fetchList(filter, repositoryOptions.sort, this.limit).then((data) => {
                this.loading = false;
                this.itemsLoaded = true;
                this.optionsToDisplay = data;
            });
        },
        fetchMissingItems(val) {
            if (!_.isArray(val)) {
                val = val ? [val] : [];
            }
            let missing = val.filter((v) => {
                return _.find(this.optionsToDisplay, (opt) => opt.id == v) === undefined;
            });
            if (missing.length !== 0) {
                this.options.fetchList({id: val}).then((data) => {
                    this.optionsToDisplay = data;
                });
            }
        },
        focused() {
            if (!this.itemsLoaded && this.isRemote) {
                this.fetchItems();
            }
        },
    },
    watch: {
        value(v) {
            if (this.multiple && !_.isArray(v)) {
                v = v ? [v] : [];
            }

            this.val = v;

            if (this.isRemote) {
                this.fetchMissingItems(v);
            }
        },
        val(v) {
            this.$emit('input', v);
        },
    },
};
</script>
