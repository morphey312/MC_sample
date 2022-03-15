<script>
import Vue from 'vue';
import Vuetable from '../table/Table.vue';
import FieldCheckbox from '../table/FieldCheckbox.vue';
import RowHeader from '../table/RowHeader.vue';
import HeaderFilters from '../table/HeaderFilters.vue';
import Buttons from './Buttons.vue';
import BaseRepository from '@/repositories/base-repository';
import CollectionFilter from '@/services/collection-filter';
import ExpandableCell from '@/components/general/table/ExpandableCell.vue';
import ExpandableMixin from '@/mixins/table/expandable';
Vue.component('vuetable-field-checkbox', FieldCheckbox);

export default {
    mixins: [
        ExpandableMixin,
    ],
    props: {
        items: {
            type: [Array, Object],
            default: () => [],
        },
        value: {
            type: Array,
            default: () => [],
        },
        valueKey: {
            type: String,
            default: 'id',
        },
        fields: {
            type: Array,
            default: () => [],
        },
        itemsFields: {
            type: Array,
            default: () => [],
        },
        height: {
            type: String,
            default: '200px',
        },
        leftTitle: {
            type: String,
            default: __('Доступно'),
        },
        leftWidth: {
            type: String,
            default: '200px',
        },
        leftFilter: {
            type: [Boolean, String, Array, Object],
            default: true,
        },
        leftClass: {
            type: String,
            default: '',
        },
        rightTitle: {
            type: String,
            default: __('Выбрано'),
        },
        rightWidth: {
            type: String,
            default: '200px',
        },
        rightFilter: {
            type: [Boolean, String, Array, Object],
            default: false,
        },
        rightClass: {
            type: String,
            default: '',
        },
        emptyOptionsMessage: {
            type: String,
            default: __('Нет элементов для выбора'),
        },
        emptySelectionMessage: {
            type: String,
            default: __('Выберите элементы слева'),
        },
        hasGroups: {
            type: Boolean,
            default: false,
        },
        hasRightGroups: {
            type: Boolean,
            default: false,
        },
        initialFilters: {
            type: Object,
            default: () => ({}),
        },
        initialSelectionFilters: {
            type: Object,
            default: () => ({}),
        },
        checkedCallback: {
            type: [Boolean, Function],
            default: false,
        },
        showExtraFields: {
            type: Boolean,
            default: false,
        }
    },
    data() {
        return {
            loadedItems: [],
            availableItems: [],
            selectedItems: [],
            visibleItems: [],
            selectionVisibleItems: [],
            filters: {...this.initialFilters},
            selectionFilters: {},
            loading: false,
        };
    },
    mounted() {
        this.loadItems();
    },
    methods: {
        loadItems() {
            if (this.items instanceof BaseRepository) {
                this.loadItemsFromRepository();
                this.items.watch(['filters', 'sort'], () => {
                    this.loadItemsFromRepository();
                });
            } else {
                this.loadedItems = this.items;
                this.initItems();
            }
        },
        loadItemsFromRepository() {
            this.loading = true;
            this.items.fetchList().then((result) => {
                this.loading = false;
                this.loadedItems = result;
                this.initItems();
            });
        },
        findValue(item) {
            return _.find(this.value, (row) => {
                if (_.isObjectLike(row)) {
                    return row[this.valueKey] == item.id;
                }
                return row == item.id;
            });
        },
        initItems() {
            this.availableItems = [];
            this.selectedItems = [];
            this.loadedItems.forEach((item) => {
                let data = this.findValue(item);
                if (data !== undefined) {
                    this.addSelectionRow(item, data);
                } else {
                    this.addAvailableRow(item);
                }
            });
            this.availableItems = _.sortBy(this.availableItems, 'value');
            this.visibleItems = this.filterItems(this.availableItems, this.filters);
            this.selectionVisibleItems = this.filterSelection(this.selectedItems, this.selectionFilters);
        },
        addSelectionRow(item, data) {
            this.selectedItems.push({...item, data});
        },
        removeSelectionRow(id) {
            this.selectedItems = this.selectedItems.filter((opt) => opt.id != id);
        },
        addAvailableRow(item) {
            this.availableItems.push(item);
        },
        hasOption(id) {
            return this.availableItems.some((opt) => opt.id == id)
                || this.selectedItems.some((opt) => opt.id == id);
        },
        findOption(id) {
            return _.find(this.availableItems, (opt) => opt.id == id)
                || _.find(this.selectedItems, (opt) => opt.id == id);
        },
        removeAvailableRow(id) {
            this.availableItems = this.availableItems.filter((opt) => opt.id != id);
        },
        addToSelection(item) {
            let option = _.find(this.availableItems, (opt) => opt.id == item);
            if (option !== undefined) {
                let data = this.newData(option);
                this.addSelectionRow(option, data);
                this.removeAvailableRow(option.id);
                this.visibleItems = this.filterItems(this.availableItems, this.filters);
                this.selectionVisibleItems = this.filterSelection(this.selectedItems, this.selectionFilters);
                this.sendData();
            }
        },
        removeFromSelection(item) {
            let value = _.find(this.selectedItems, (opt) => opt.id == item);
            if (value) {
                let availItem = _.find(this.loadedItems, (opt) => opt.id == item);
                this.removeSelectionRow(value.id);
                if (availItem !== undefined) {
                    this.addAvailableRow(availItem);
                }
                this.availableItems = _.sortBy(this.availableItems, 'value');
                this.visibleItems = this.filterItems(this.availableItems, this.filters);
                this.selectionVisibleItems = this.filterSelection(this.selectedItems, this.selectionFilters);
                this.sendData();
            }
        },
        filterItems(items, filters) {
            let filter = this.createFilter(filters, this.itemsFields, this.hasGroups);
            if (this.hasGroups) {
                return this.makeGrops(filter.filter(items, true), this.expandedGroups);
            }
            return filter.filter(items);
        },
        filterSelection(items, filters) {
            let filter = this.createFilter(filters, this.fields, this.hasRightGroups);
            if (this.hasRightGroups) {
                return this.makeGrops(filter.filter(items, true), this.expandedRightGroups);
            }
            return filter.filter(items);
        },
        createFilter(values, fields, hasGroup) {
            let filter = new CollectionFilter();
            filter.whereContains('value', values.value);
            if (hasGroup) filter.whereContains('group', values.value)
            fields.forEach((field) => {
                if (field.filter !== undefined) {
                    let key = field.filterField || field.name;
                    let value = values[key];
                    if (_.isFunction(field.filterFunction)) {
                        field.filterFunction(filter, key, value);
                    } else if (field.filter === true) {
                        filter.whereContains(key, value);
                    } else if (_.isArray(value)) {
                        filter.whereIn(key, value)
                    } else {
                        filter.where(key, value);
                    }
                }
            });
            return filter;
        },
        newData(option) {
            let data;
            let fields = this.getEditableFields();
            if (fields.length === 0) {
                data = option.id;
            } else {
                data = {};
                fields.forEach((field) => {
                    data[field.propName || field.name] = null;
                });
                data[this.valueKey] = option.id;
            }
            this.$emit('new-row', data, option);
            return data;
        },
        getEditableFields() {
            return this.fields.filter((field) => !(field.editable === false));
        },
        sendData() {
            this.$emit('input', this.selectedItems.map((item) => item.data));
        },
        applyFilter(updates) {
            this.filters = {...this.filters, ...updates};
            this.visibleItems = this.filterItems(this.availableItems, this.filters);
            this.$refs.lefttable.selectedTo = this.$refs.lefttable.selectedTo.filter((id) => {
                return _.findById(this.visibleItems, id) !== undefined;
            });
        },
        applySelectionFilter(updates) {
            this.selectionFilters = {...this.selectionFilters, ...updates};
            this.selectionVisibleItems = this.filterSelection(this.selectedItems, this.selectionFilters);
            this.$refs.righttable.selectedTo = this.$refs.lefttable.selectedTo.filter((id) => {
                return _.findById(this.selectionVisibleItems, id) !== undefined;
            });
        },
        getLeftScopedSlots(h) {
            let slots = { ...this.$vnode.data.scopedSlots };
            if (this.hasGroups) {
                slots.value = (props) => {
                    return h(ExpandableCell, {
                        props: {
                            data: props.rowData,
                        },
                        on: {
                            toggle: (groupId) => {
                                this.toggleGroup(groupId, this.expandedGroups);
                                this.visibleItems = this.filterItems(this.availableItems, this.filters);
                            },
                        },
                    });
                };
            }
            return slots;
        },
        getRightScopedSlots(h) {
            let slots = { ...this.$vnode.data.scopedSlots };
            if (this.hasRightGroups) {
                slots.value = (props) => {
                    return h(ExpandableCell, {
                        props: {
                            data: props.rowData,
                        },
                        on: {
                            toggle: (groupId) => {
                                this.toggleGroup(groupId, this.expandedRightGroups);
                                this.selectionVisibleItems = this.filterSelection(this.selectedItems, this.selectionFilters);
                            },
                        },
                    });
                };
            }
            return slots;
        },
        hasFilter(fields) {
            return fields.some((field) => !(field.filter === false || _.isNil(field.filter)));
        },
        hasLeftSelection() {
            return this.$refs.lefttable
                && this.$refs.lefttable.selectedTo.length !== 0;
        },
        hasRightSelection() {
            return this.$refs.righttable
                && this.$refs.righttable.selectedTo.length !== 0;
        },
        getRightFields() {
            if (this.showExtraFields) {
                return this.fields;
            }
            return this.fields.filter(field => field.extra !== true);
        },
    },
    watch: {
        items(val) {
            this.loadItems();
            this.sendData();
        },
    },
    render(h) {
        let leftFields = [
            {
                name: '__checkbox',
                width: '22px',
            },
            {
                name: 'value',
                title: this.leftTitle,
                width: this.leftWidth,
                filter: this.leftFilter,
                dataClass: this.leftClass,
            },
            ...this.itemsFields,
        ];

        let rightFields = [
            {
                name: '__checkbox',
                width: '22px',
            },
            {
                name: 'value',
                title: this.rightTitle,
                width: this.rightWidth,
                filter: this.rightFilter,
                dataClass: this.rightClass,
            },
            ...this.getRightFields(),
        ];

        return h('div', {
            class: 'transfer-table',
            style: {height: this.height},
        }, [
            h('div', {
                class: ['available-items', {'has-filter': this.hasFilter(leftFields)}],
            }, [
                h(Vuetable, {
                    ref: 'lefttable',
                    props: {
                        apiMode: false,
                        fields: leftFields,
                        trackBy: 'id',
                        tableHeight: 'auto',
                        data: this.visibleItems,
                        noDataTemplate: this.emptyOptionsMessage,
                        headerFilter: this.filters,
                        headerRows: [
                            RowHeader,
                            ...(this.hasFilter(leftFields) ? [HeaderFilters] : []),
                        ],
                    },
                    scopedSlots: this.getLeftScopedSlots(h),
                    on: {
                        'header-filters-updated': (updates) => {
                            this.applyFilter(updates);
                        },
                        'vuetable:checkbox-checked': (data) => {
                            if (this.hasGroups && data._is_group) {
                                this.expandGroup(data.group_id, this.expandedGroups);
                                this.visibleItems = this.filterItems(this.availableItems, this.filters);
                                this.checkGroupItems(data.group_id, this.visibleItems, this.$refs.lefttable);
                            }
                        },
                        'vuetable:checkbox-unchecked': (data) => {
                            if (this.hasGroups) {
                                if (data._is_group) {
                                    this.uncheckGroupItems(data.group_id, this.availableItems, this.$refs.lefttable);
                                } else if (data.group_id) {
                                    this.uncheckGroup(data.group_id, this.$refs.lefttable);
                                }
                            }
                        },
                        'vuetable:checkbox-checked-all': () => {
                            if (this.hasGroups) {
                                this.expandedGroups = this.getAllGroupIds(this.visibleItems);
                                this.visibleItems = this.filterItems(this.availableItems, this.filters);
                                this.checkAllItems(this.visibleItems, this.$refs.lefttable);
                            }
                        },
                        'vuetable:checkbox-unchecked-all': () => {
                            if (this.hasGroups) {
                                this.uncheckAllItems(this.$refs.lefttable);
                            }
                        },
                    },
                }),
                this.$slots['left-footer'],
            ]),
            h(Buttons, {
                props: {
                    enablePut: this.hasLeftSelection(),
                    enablePull: this.hasRightSelection(),
                },
                on: {
                    put: () => {
                        this.$refs.lefttable.selectedTo.forEach((item) => {
                            if (!item._is_group) {
                                this.addToSelection(item);
                            }
                        });
                        this.$refs.lefttable.selectedTo = [];
                    },
                    pull: () => {
                        this.$refs.righttable.selectedTo.forEach((item) => {
                            this.removeFromSelection(item);
                        });
                        this.$refs.righttable.selectedTo = [];
                    }
                }
            }),
            h('div', {
                class: ['selected-items', {'has-filter': this.hasFilter(rightFields)}],
            }, [
                h(Vuetable, {
                    ref: 'righttable',
                    props: {
                        apiMode: false,
                        fields: rightFields,
                        trackBy: 'id',
                        tableHeight: 'auto',
                        data: this.selectionVisibleItems,
                        noDataTemplate: this.emptySelectionMessage,
                        headerFilter: this.selectionFilters,
                        headerRows: [
                            RowHeader,
                            ...(this.hasFilter(rightFields) ? [HeaderFilters] : []),
                        ],
                    },
                    scopedSlots: this.getRightScopedSlots(h),
                    on: {
                        'header-filters-updated': (updates) => {
                            this.applySelectionFilter(updates);
                        },
                        'vuetable:checkbox-toggled': () => {
                            if (this.checkedCallback != false) {
                                this.checkedCallback(this.$refs.righttable);
                            }
                        },
                        'vuetable:checkbox-toggled-all': () => {
                            if (this.checkedCallback != false) {
                                this.checkedCallback(this.$refs.righttable);
                            }
                        },
                        'vuetable:checkbox-checked': (data) => {
                            if (this.hasRightGroups && data._is_group) {
                                this.expandGroup(data.group_id, this.expandedRightGroups);
                                this.selectionVisibleItems = this.filterSelection(this.selectedItems, this.selectionFilters);
                                this.checkGroupItems(data.group_id, this.selectionVisibleItems, this.$refs.righttable);
                            }
                        },
                        'vuetable:checkbox-unchecked': (data) => {
                            if (this.hasRightGroups) {
                                if (data._is_group) {
                                    this.uncheckGroupItems(data.group_id, this.selectedItems, this.$refs.righttable);
                                } else if (data.group_id) {
                                    this.uncheckGroup(data.group_id, this.$refs.righttable);
                                }
                            }
                        },
                        'vuetable:checkbox-checked-all': () => {
                            if (this.hasRightGroups) {
                                this.expandedRightGroups = this.getAllGroupIds(this.selectionVisibleItems);
                                this.selectionVisibleItems = this.filterSelection(this.selectedItems, this.selectionFilters);
                                this.checkAllItems(this.selectionVisibleItems, this.$refs.righttable);
                            }
                        },
                        'vuetable:checkbox-unchecked-all': () => {
                            if (this.hasRightGroups) {
                                this.uncheckAllItems(this.$refs.righttable);
                            }
                        },
                    }
                }),
                this.$slots['right-footer'],
            ]),
        ]);
    }
};
</script>
