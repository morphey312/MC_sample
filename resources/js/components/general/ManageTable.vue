<script>
import Vue from 'vue';
import FieldCheckbox from './table/FieldCheckbox.vue';
import Vuetable from './table/Table.vue';
import Pagination from './Pagination.vue';
import HeaderFilters from './table/HeaderFilters.vue';
import RowHeader from './table/RowHeader.vue';
import lts from '@/services/lts';
import eventHub from '@/services/event-hub';

Vue.component('vuetable-field-checkbox', FieldCheckbox);

export default {
    props: {
        fields: {
            type: Array,
            required: true,
        },
        repository: {
            type: Object,
            required: true,
        },
        filters: {
            type: Object,
            default: () => ({}),
        },
        initialSortOrder: {
            type: Array,
            default: () => [],
        },
        scopes: {
            type: Array,
            default: null,
        },
        trackBy: {
            type: String,
            default: 'id',
        },
        rowClass: {
            type: [String, Function],
        },
        tableClassList: {
            type: Object,
        },
        tableHeight: {
            type: String
        },
        selectableRows: {
            type: Boolean,
            default: false,
        },
        flexHeight: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: __('Нет данных для отображения'),
        },
        tableHeaders: {
            type: Function,
            default: null,
        },
        enableLoader: {
            type: Boolean,
            default: true,
        },
        enablePagination: {
            type: Boolean,
            default: true,
        },
        tableUid: {
            type: String,
            default: null,
        },
        initialFields: {
            type: Array,
            default: () => [],
        },
        showTableSettings: {
            type: Boolean,
            default: true,
        },
        paginationComponent: {
            type: Object,
            default: () => Pagination,
        },
        observeChanges: {
            type: String,
            default: null,
        },
        multipleSelectable: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            sortOrder: [],
            page: 1,
            perPage: this.enablePagination ? 50 : 1000,
            selectedRow: null,
            normalizedFields: null,
            totalTableWidth: null,
            focusedOnRow: -1,
            combinedScopes: null,
        };
    },
    beforeMount() {
        let {fields, totalWidth} = this.normalizeFields(this.getInitialVisibleFields());
        this.setNormalizedFields(fields);
        this.totalTableWidth = totalWidth;
    },
    mounted() {
        this.repository.watch(['filters', 'sort', 'scopes'], () => {
            this.page = 1;
            this.refresh();
        });

        this.$watch('$data.combinedScopes', () => {
            this.refresh();
        });

        if (this.observeChanges !== null) {
            eventHub.$on('broadcast.record_changed', this.changesObserver);
        }
    },
    created() {
        this.changesObserver = (data) => {
            if (data.data.saved === this.observeChanges) {
                this.updateDataRecord(data.data.attributes);
            } else if (data.data.deleted === this.observeChanges) {
                this.deleteDataRecord(data.data.attributes);
            }
        };
    },
    beforeDestroy() {
        if (this.observeChanges !== null) {
            eventHub.$off('broadcast.record_changed', this.changesObserver);
        }
    },
    watch: {
        filters() {
            this.page = 1;
            this.refresh();
        },
        scopes() {
            this.combineScopes();
        },
    },
    methods: {
        getInitialVisibleFields() {
            let uid = this.getTableUid();
            let settings = this.getFieldsSettings();
            let hiddenFields = settings[uid];
            
            if (!_.isArray(hiddenFields)) {
                return this.pickFields(this.initialFields);
            }
            
            return this.withoutFields(hiddenFields);
        },
        getTableUid() {
            return this.tableUid || this.$router.currentRoute.name;
        },
        getFieldsSettings(otherwise = {}) {
            return _.isNil(lts.hiddenTableFields) ? otherwise : lts.hiddenTableFields;
        },
        storeFieldsSettings(fieldsSettings) {
            let settings = this.getFieldsSettings();
            let uid = this.getTableUid();
            settings[uid] = fieldsSettings;
            lts.hiddenTableFields = settings;
        },
        pickFields(visibleFields) {
            if (visibleFields.length === 0) {
                return this.fields;
            }

            return this.fields.filter((field) => {
                return visibleFields.indexOf(field.name) !== -1;
            });
        },
        withoutFields(hiddenFields) {
            if (hiddenFields.length === 0) {
                return this.fields;
            }

            return this.fields.filter((field) => {
                return hiddenFields.indexOf(field.name) === -1;
            });
        },
        setHiddenFields(hiddenFields) {
            this.storeFieldsSettings(hiddenFields);
            let {fields, totalWidth} = this.normalizeFields(this.withoutFields(hiddenFields));
            this.setNormalizedFields(fields);
            this.totalTableWidth = totalWidth;
            this.setupTableWidth(this.totalTableWidth);
        },
        getScopedSlots(h) {
            let slots = { ...this.$vnode.data.scopedSlots };
            return slots;
        },
        loadData() {
            this.repository.cancelRequest();
            let loader = this.showLoader();
            return this.repository.fetch(this.filters, this.sortOrder, this.combinedScopes, this.page, this.perPage).then((response) => {
                if (this.enablePagination && response.pagination) {
                    this.$refs.pagination.setPaginationData(response.pagination);
                }
                return Promise.resolve(response.rows);
            }).finally(() => {
                if (loader) {
                    loader.close();
                }
            }).catch(() => {});
        },
        showLoader() {
            if (!this.enableLoader) {
                return false;
            }
            return this.$loading({
                fullscreen: false,
                target: this.$el,
            });
        },
        normalizeSortOrder(order) {
            return order.map((sort) => {
                return {
                    field: sort.sortField,
                    direction: sort.direction,
                };
            });
        },
        refresh() {
            this.focusedOnRow = -1;
            this.unselectAll();
            this.$refs.vuetable.loadData();
        },
        getSelectedRows() {
            if (this.selectableRows) {
                return this.selectedRow ? [this.selectedRow.data] : [];
            } 
            return this.$refs.vuetable.selectedTo;
        },
        unselectAll() {
            if (this.selectableRows) {
                return this.selectedRow = null;
            } else {
                return this.$refs.vuetable.selectedTo = [];
            }
            this.$emit('selection-changed', this.getSelectedRows());
        },
        getData() {
            return this.$refs.vuetable.tableData;
        },
        replaceRow(row, index) {
            this.$refs.vuetable.tableData.splice(index, 1, row);
        },
        getSelectedRowIndex() {
            return this.selectedRow ? this.selectedRow.index : -1;
        },
        getRowsCount() {
            return this.$refs.vuetable.countTableData;
        },
        getHeaderRows() {
            let headers = [
                RowHeader,
                ...(this.hasHeaderFilters() ? [HeaderFilters] : []),
            ];

            if(this.tableHeaders !== null) {
                headers = this.tableHeaders(headers);
            }

            return headers;
        },
        hasHeaderFilters() {
            return this.fields.some((f) => f.filter !== undefined);
        },
        rowClassFunction(data, index) {
            let classes = [];
            if (this.rowClass) {
                if (_.isFunction(this.rowClass)) {
                    classes.push(this.rowClass(data, index));
                } else {
                    classes.push(this.rowClass);
                }
            }
            if (this.selectedRow && this.selectedRow.index === index) {
                classes.push('selected-table-row');
            }
            if (this.focusedOnRow === index) {
                classes.push('focused-table-row');
            }
            return classes;
        },
        toggleRowSelection(item) {
            if (this.selectedRow && this.selectedRow.index === item.index) {
                this.selectedRow = null;
            } else {
                this.selectedRow = item;
            }
            this.$emit('selection-changed', this.getSelectedRows());
        },
        updateSelection(criteria) {
            let selected = [];
            this.getData().forEach((data, index) => {
                if (criteria(data)) {
                    selected.push({data, index});
                }
            });

            if (this.selectableRows) {
                this.selectedRow = selected.length === 0 ? null : selected[0];
            } else {
                this.$refs.vuetable.selectedTo = selected.map((item) => item.data[this.trackBy]);
            }

            this.$emit('selection-changed', this.getSelectedRows());
        },
        normalizeFields(fields) {
            let dimensions = null;
            let totalWidth = 0;
            let normalized;
            
            for (let field of fields) {
                if (!field.width) {
                    return {fields};
                }
                if (typeof field.width === 'number') {
                    if (dimensions !== null && dimensions !== 'px') {
                        return {fields};
                    }
                    dimensions = 'px';
                    totalWidth += field.width;
                } else if (field.width.indexOf('px') !== -1) {
                    if (dimensions !== null && dimensions !== 'px') {
                        return {fields};
                    }
                    dimensions = 'px';
                    totalWidth += Number(field.width.replace('px', ''));
                } else if (field.width.indexOf('%') !== -1) {
                    if (dimensions !== null && dimensions !== '%') {
                        return {fields};
                    }
                    dimensions = '%';
                    totalWidth += Number(field.width.replace('%', ''));
                } else {
                    return {fields};
                }
            }
            
            if (dimensions === '%') {
                normalized = fields.map((field) => ({
                    ...field,
                    width: (100 * Number(field.width.replace('%', '')) / totalWidth) + '%',
                }));
                totalWidth = Math.max(100, totalWidth);
            } else {
                normalized = fields;
            }
            
            return {
                fields: normalized, 
                totalWidth: totalWidth + dimensions,
            };
        },
        setupTableWidth(width) {
            let tables = this.$el.querySelectorAll('.vuetable-head-wrapper > table, .vuetable-body-wrapper > table');
            tables.forEach((table) => {
                table.style.width = width;
            });
        },
        scrollToRow(index) {
            this.focusedOnRow = -1;
            
            if (typeof index === 'function') {
                let data = this.getData();
                for (let i = 0; i < data.length; i++) {
                    if (index(data[i], i) === true) {
                        return this.scrollToRow(i);
                    }
                }
                return -1;
            }
            
            let row = this.$el.querySelector('tbody > tr[item-index="' + index + '"]');
            if (row !== null) {
                row.scrollIntoView(true);
                this.focusedOnRow = index;
                return index;
            }
            
            return -1;
        },
        updateDataRecord(attributes) {
            if (attributes.id !== undefined) {
                let target = _.findById(this.getData(), attributes.id);
                if (target !== undefined) {
                    Object.keys(attributes).forEach((key) => {
                        target.set(key, attributes[key]);
                    });
                }
            }
        },
        deleteDataRecord(attributes) {
            if (attributes.id !== undefined) {
                let data = this.getData();
                let index = _.findIndex(data, (row) => row.id === attributes.id);
                if (index !== -1) {
                    data.splice(index, 1);
                }
            }
        },
        unselectRow(item) {
            let selection = [...this.$refs.vuetable.selectedTo];
            this.$refs.vuetable.selectedTo = selection.filter(id => {
                return id != item[this.trackBy];
            });
        },
        setNormalizedFields(fields) {
            let combined = this.scopes === null ? [] : [...this.scopes];
            this.normalizedFields = fields;
            fields.forEach((field) => {
                if (field.scopes !== undefined) {
                    field.scopes.forEach((scope) => {
                        if (!this.scopeDefined(combined, scope)) {
                            combined.push(scope);
                        }
                    });
                }
            });
            if (combined.length === 0) {
                this.combinedScopes = null;
            } else if (this.combinedScopes === null 
                || !this.scopesAreEqual(this.combinedScopes, combined)) 
            {
                this.combinedScopes = combined;
            }
        },
        scopeDefined(set, scope) {
            let name = this.getScopeName(scope);
            return set.some((scope) => this.getScopeName(scope) === name);
        },
        getScopeName(scope) {
            return (typeof scope === 'object') ? scope.name : scope;
        },
        scopesAreEqual(first, second) {
            return first.length === second.length 
                && first.every((scope) => this.scopeDefined(second, scope));
        },
    },
    render(h) {
        return h('div', {
            class: ['manage-table', {'flex-height': this.flexHeight}],
        }, [
            h(Vuetable, {
                ref: 'vuetable',
                props: {
                    fields: this.normalizedFields,
                    apiMode: false,
                    sortOrder: this.initialSortOrder,
                    trackBy: this.trackBy,
                    rowClass: this.rowClassFunction,
                    css: this.tableClassList,
                    tableHeight: this.flexHeight ? '100%' : this.tableHeight,
                    headerRows: this.getHeaderRows(),
                    headerFilter: this.filters,
                    noDataTemplate: this.emptyMessage,
                    dataManager: (sortOrder) => {
                        this.sortOrder = this.normalizeSortOrder(sortOrder);
                        return this.loadData();
                    },
                },
                on: {
                    'vuetable:row-clicked': (item) => {
                        if (this.$isMobileNavigator && this.multipleSelectable) {
                            let itemSelected = this.$refs.vuetable.selectedTo.indexOf(item.data.id) !== -1;
                            this.updateSelection(row => {
                                if (row.id == item.data.id) {
                                    return itemSelected ? false : true;
                                } else if (this.$refs.vuetable.selectedTo.indexOf(row.id) !== -1) {
                                    return true;
                                }
                                return false;
                            });
                        } else {
                            this.$emit('row-clicked', item);
                            if (this.selectableRows) {
                                this.toggleRowSelection(item);
                            }
                        }
                    },
                    'vuetable:row-dblclicked': (item) => {
                        this.$emit('row-dblclicked', item);
                    },
                    'vuetable:initialized': () => {
                        if (this.totalTableWidth) {
                            this.setupTableWidth(this.totalTableWidth);
                        }
                    },
                    'vuetable:loaded': () => {
                        this.$emit('loaded');
                    },
                    'vuetable:checkbox-toggled': () => {
                        this.$emit('selection-changed', this.getSelectedRows());
                    },
                    'vuetable:checkbox-toggled-all': () => {
                        this.$emit('selection-changed', this.getSelectedRows());
                    },
                    'header-filters-updated': (updates) => {
                        this.$emit('header-filter-updated', updates);
                    },
                },
                scopedSlots: this.getScopedSlots(h),
            }),
            h('div', {
                class: 'table-footer',
            }, [
                h(this.paginationComponent, {
                    ref: 'pagination',
                    props: {
                        enablePagination: this.enablePagination,
                        visibleFields: [...this.normalizedFields],
                        fields: [...this.fields],
                        showTableSettings: this.showTableSettings,
                    },
                    on: {
                        pageChanged: (page) => {
                            if (page == 'prev') {
                                this.page--;
                            } else if (page == 'next') {
                                this.page++;
                            } else {
                                this.page = page;
                            }
                            this.refresh();
                        },
                        pageSizeChanged: (size) => {
                            this.perPage = size;
                            this.refresh();
                        },
                        fieldsChanged: (fields) => {
                            this.setHiddenFields(fields);
                        },
                    },
                }, 
                    this.$slots['footer-top'],
                ),
                this.$slots['footer-bottom'],
            ])
        ]);
    },
};
</script>