<template>
    <manage-table
        v-loading="saving || loading"
        ref="table"
        :fields="fields"
        :initial-fields="exceptSelfcost(fields)"
        :filters="filters"
        :repository="proxyRepository"
        :initial-sort-order="sortOrder"
        :row-class="rowClass"
        :flex-height="true"
        :pagination-component="paginationComponent"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
        <template slot="name" slot-scope="props">
            <div class="has-icon">
                <span class="ellipsis">
                    {{ getServiceName(props.rowData) }}
                </span>
                <row-warning :message="warningMessage(props.rowData)" />
                <svg-icon
                    v-if="props.rowData.price && !props.rowData.price.isNew()"
                    name="info-alt"
                    class="icon-tiny icon-grey"
                    @click.stop="showHistory(props.rowData.price)" />
            </div>
        </template>
        <template slot="clinics" slot-scope="props">
            <template v-if="props.rowData.price">
                <form-select
                    :entity="props.rowData.price"
                    property="clinics"
                    control-size="mini"
                    :disabled="isClosed(props.rowData.price) || !canChange(props.rowData.price)"
                    :multiple="true"
                    :options="getClinicsList(props.rowData.service, props.rowData.price)" />
            </template>
            <a v-else-if="$canCreate(permissions)"
                href="#"
                @click.prevent="addPrices(props.rowData.service, props.rowIndex)">
                {{ __('Добавить тариф') }}
            </a>
        </template>
        <template slot="cost" slot-scope="props">
            <template v-if="props.rowData.price">
                <span class="inline-input disabled" v-if="isJustClosed(props.rowData.price) || !canChange(props.rowData.price)">
                    {{ $formatter.numberFormat(props.rowData.price.cost) }}
                </span>
                <inline-input
                    v-else
                    v-model="props.rowData.price.cost"
                    :formatter="$formatter.numberFormat"
                    :validator="isValidPrice"
                    :tab-index="getTabIndex(props.rowIndex)"
                    :required="true" />
            </template>
        </template>
        <template slot="self_cost" slot-scope="props">
            <template v-if="props.rowData.price">
                <span class="inline-input disabled" v-if="isJustClosed(props.rowData.price) || !canChange(props.rowData.price)">
                    {{ $formatter.numberFormat(props.rowData.price.self_cost) }}
                </span>
                <inline-input
                    v-else
                    v-model="props.rowData.price.self_cost"
                    :formatter="$formatter.numberFormat"
                    :validator="isValidPrice"
                    :tab-index="getTabIndex(props.rowIndex, 10000)"
                    :required="true" />
            </template>
        </template>
        <template slot="date" slot-scope="props">
            <template v-if="props.rowData.price">
                <inline-datepicker
                    v-if="isJustClosed(props.rowData.price)"
                    v-model="props.rowData.price.date_to"
                    :min-date="props.rowData.price.$.date_from"
                    :tab-index="getTabIndex(props.rowIndex, 20000)"
                    :required="true" />
                <span class="inline-datepicker disabled" v-else-if="isClosed(props.rowData.price) || !canChange(props.rowData.price)">
                    {{ $formatter.dateFormat(props.rowData.price.date_from) }}
                </span>
                <inline-datepicker
                    v-else
                    v-model="props.rowData.price.date_from"
                    :tab-index="getTabIndex(props.rowIndex, 20000)"
                    :required="true" />
            </template>
        </template>
        <template slot="actions" slot-scope="props">
            <template v-if="props.rowData.price">
                <el-button
                    type="text"
                    :disabled="!isRowChanged(props.rowData.price)"
                    @click="revertRow(props.rowData.service, props.rowData.price, props.rowIndex)">
                    {{ __('Отменить') }}
                </el-button>
                /
                <el-button
                    type="text"
                    :disabled="!canBeClosed(props.rowData.price) || !canChange(props.rowData.price)"
                    @click="closePrice(props.rowData.price)">
                    {{ __('Закрыть') }}
                </el-button>
            </template>
        </template>
        <div
            v-if="$canCreate(permissions) || $canUpdate(permissions) || $can(permissions+'.export')"
            class="buttons"
            slot="footer-top">
            <el-button
                v-if="$canCreate(permissions) || $canUpdate(permissions)"
                @click="selectChanged">
                {{ __('Выбрать измененные') }}
            </el-button>
            <el-button
                v-if="$canCreate(permissions) || $canUpdate(permissions)"
                :disabled="!hasSelection"
                :title="__('Задать дату начала/закрытия тарифа')"
                @click="pickDateForSelected">
                {{ __('Задать дату') }}
                <el-date-picker
                    v-model="dateForSelected"
                    :picker-options="pickerOptions"
                    ref="datepicker"
                    type="date"
                    value-format="yyyy-MM-dd"
                    @change="setDateForSelected">
                </el-date-picker>
            </el-button>
            <el-button
                v-if="$canCreate(permissions) || $canUpdate(permissions)"
                type="primary"
                @click="saveChanges">
                {{ __('Сохранить') }}
            </el-button>
            <el-dropdown class="ml-10">
                <el-button >
                    {{ __('Еще') }}
                </el-button>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item v-if="$canCreate(permissions) || $canUpdate(permissions)">
                        <el-button
                            type="text"
                            :disabled="!hasSelection"
                            @click="splitSelected">
                            {{ __('Разделить тариф') }}
                        </el-button>
                    </el-dropdown-item>
                    <el-dropdown-item v-if="$canCreate(permissions) || $canUpdate(permissions)">
                        <el-button
                            type="text"
                            :disabled="!hasSelection"
                            @click="closeSelectedPrices">
                            {{ __('Закрыть услуги') }}
                        </el-button>
                    </el-dropdown-item>
                    <el-dropdown-item v-if="$can(permissions+'.export')">
                        <el-button
                            type="text"
                            @click="exportExcel(exportFileName, exportFields)">
                            {{ __('Экспорт в Excel') }}
                        </el-button>
                    </el-dropdown-item>
                    <el-dropdown-item v-if="$canCreate(permissions) || $canUpdate(permissions)">
                        <el-button
                            type="text"
                            :disabled="!hasSelection"
                            @click="revertSelected">
                            {{ __('Отменить изменения') }}
                        </el-button>
                    </el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </div>
    </manage-table>
</template>

<script>
import History from './History.vue';
import ProxyRepository from '@/repositories/proxy-repository';
import PriceSetRepository from '@/repositories/price/set';
import Pagination from './Pagination.vue';
import RowWarning from './RowWarning.vue';
import ClinicRepository from '@/repositories/clinic';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import * as resultGenerator from "../price-list/generators/prices";

export default {
    mixins: [
        ExportXLSXMixin,
    ],
    components: {
        RowWarning,
    },
    props: {
        repository: {
            type: Object,
            required: true,
        },
        batchRequest: {
            type: Object,
            required: true,
        },
        filters: {
            type: Object,
            default: () => ({}),
        },
        sortOrder: {
            type: Array,
            default: () => [],
        },
        extraColumns: {
            type: Array,
            default: () => [],
        },
        permissions: String,
        availableClinics: {
            type: Array,
            default: null,
        },
        tabs: Array,
        priceSets: Array
    },
    computed: {
        exportFileName(){
            return this.tabs ? __('Прайс услуги') : __('Прайс услуги страховые');
        }
    },
    data() {
        let clinicsRepository = new ProxyRepository(() => {
            if (this.availableClinics !== null) {
                return Promise.resolve(this.availableClinics);
            } else {
                let repository = new ClinicRepository({
                    accessLimit: this.$isAccessLimited(this.permissions),
                });
                return repository.fetchList();
            }
        });

        let extraLang1 = this.getLangBySuffix('lc1');
        let extraLang2 = this.getLangBySuffix('lc2');
        let extraLang3 = this.getLangBySuffix('lc3');

        return {
            page: 2,
            rowIdCounter: 1,
            rows: [],
            hasSelection: false,
            invalid: [],
            activeFilters: this.filters,
            dateForSelected: null,
            minDateForSelected: null,
            pickerOptions: {
                disabledDate: this.checkDisabledDate,
                firstDayOfWeek: 1,
            },
            loading: false,
            tempQuery: null,
            fileGenerator: resultGenerator,
            saving: false,
            proxyRepository: new ProxyRepository((query) => {
                this.tempQuery = query;

                return this.fetch(query);
            }),
            exportFields: [
                {title: __('ГОРОД'), name: 'city'},
                {title: __('НАЗВАНИЕ ПРАЙСА'), name: 'price_name', width: 25},
                {title: __('СПЕЦИАЛИЗАЦИЯ'), name: 'specialization', width: 20},
                {title: __('КОД'), name: 'code'},
                {title: __('МЕДИЦИНСКАЯ УСЛУГА'), name: 'service_name', width: 45},
                ...(extraLang1 === '' ? [] : [{title: __('МЕДИЦИНСКАЯ УСЛУГА') + ` (${extraLang1})`, name: 'service_name_lc1', width: 45}]),
                ...(extraLang2 === '' ? [] : [{title: __('МЕДИЦИНСКАЯ УСЛУГА') + ` (${extraLang2})`, name: 'service_name_lc2', width: 45}]),
                ...(extraLang3 === '' ? [] : [{title: __('МЕДИЦИНСКАЯ УСЛУГА') + ` (${extraLang3})`, name: 'service_name_lc3', width: 45}]),
                {title: __('НАЗВАНИЕ УСЛУГИ ДЛЯ ЧЕКА'), name: 'service_name_ua', width: 45},
                ...(extraLang1 === '' ? [] : [{title: __('НАЗВАНИЕ УСЛУГИ ДЛЯ ЧЕКА') + ` (${extraLang1})`, name: 'service_name_ua_lc1', width: 45}]),
                ...(extraLang2 === '' ? [] : [{title: __('НАЗВАНИЕ УСЛУГИ ДЛЯ ЧЕКА') + ` (${extraLang2})`, name: 'service_name_ua_lc2', width: 45}]),
                ...(extraLang3 === '' ? [] : [{title: __('НАЗВАНИЕ УСЛУГИ ДЛЯ ЧЕКА') + ` (${extraLang3})`, name: 'service_name_ua_lc3', width: 45}]),
                {title: __('БАЗОВАЯ УСЛУГА'), name: 'is_base', width: 10},
                {title: __('НАЗНАЧЕНИЕ ПЛАТЕЖА'), name: 'payment_destination', width: 20},
                {title: __('Цена в клинике'), name: 'self_price', width: 15},
                {title: __('ЦЕНА'), name: 'price', width: 10},
                {title: __('ВАЛЮТА'), name: 'currency', width: 10},
                {title: __('ДАТА'), name: 'date', width: 15},
                {title: __('ТИП УСЛУГИ НА САЙТЕ'), name: 'site_service_type', width: 30},
                {title: __('ОНЛАЙН-ВИДЕОКОНСУЛЬТАЦИЯ'), name: 'is_online', width: 30},
            ],
            reportRepository: new ProxyRepository((query) => {
                return this.fetch(query).then((data) => {
                    return {
                        rows: this.processRowsForExport(data.rows),
                        pagination: data.pagination
                    }
                });
            }),
            paginationComponent: Pagination,
            clinicsRepository: clinicsRepository,
            fields: [
                {
                    name: '__checkbox',
                    width: '22px',
                },
                ...this.extraFields(),
                {
                    name: 'clinics',
                    title: __('Клиники'),
                    width: '15%',
                    filter: clinicsRepository,
                    filterField: 'clinic',
                    dataClass: 'clinic-column',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'cost',
                    title: __('Стоимость'),
                    width: '80px',
                    titleClass: 'text-right',
                    dataClass: 'text-right price-column',
                },
                {
                    name: 'self_cost',
                    title: __('Себестоимость'),
                    width: '100px',
                    titleClass: 'text-right',
                    dataClass: 'text-right price-column',
                },
                {
                    name: 'date',
                    title: __('Дата начала/закрытия тарифа'),
                    width: '120px',
                    dataClass: 'date-column',
                },
                ...((this.$canCreate(this.permissions) || this.$canUpdate(this.permissions)) ? [{
                    name: 'actions',
                    title: __('Операции по тарифу'),
                    width: '140px',
                    dataClass: 'actions-column',
                    configurable: false,
                }] : []),
            ],
        };
    },
    mounted() {
        let message = __('На этой странице остались несохраненные данные, вы уверены, что хотите покинуть ее?');
        let condition = () => this.hasChangedRows();
        this.$safeClose(message, condition);
        this.$confirmNavigation(message, condition);
    },
    beforeDestroy() {
        this.$unsafeClose();
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        extraFields() {
            let res = this.extraColumns.map((col) => {
                return {
                    title: col.title,
                    width: col.width,
                    filter: col.filter,
                    filterField: col.filterField,
                    formatter: (value) => {
                        return col.get(value);
                    },
                    ...(col.name ? {name: col.name} : {})
                };
            });
            res[0].configurable = false;
            return res;
        },
        getServiceName(v) {
            return this.fields[1].formatter(v);
        },
        exceptSelfcost(fields) {
            return fields.map((field) => field.name).filter((field) => field !== 'self_cost');
        },
        getClinicsList(service, price) {
            let list = service.clinics.map((clinic) => {
                return {
                    id: clinic.id,
                    value: clinic.name,
                };
            });

            if (price.isNew()) {
                return list.filter((clinic) => {
                    return this.$canManage(this.permissions + '.create', [clinic.id]);
                });
            }

            if (this.canChange(price)) {
                return list.filter((clinic) => {
                    return this.$canManage(this.permissions + '.update', [clinic.id]);
                });
            }

            return list;
        },
        warnUnsaved() {
            return new Promise((next, cancel) => {
                if (this.hasChangedRows()) {
                    this.$confirm(__('Некоторые изменения остались несохраненными, Вы уверены, что хотите продолжить?'), next, {
                        cancelled: cancel,
                    });
                } else {
                    next();
                }
            });
        },
        fetch(query) {
            return this.warnUnsaved().then(() => {
                this.rows = [];
                this.invalid = [];
                this.activeFilters = {...query.filters};
                if (this.availableClinics !== null) {
                    if (this.activeFilters.clinic === undefined || this.activeFilters.clinic.length === 0) {
                        this.activeFilters.clinic = this.availableClinics.map(c => c.id);
                    }
                }
                return this.repository.fetchPriceList(this.activeFilters, query.sort, [], query.page, query.limit).then((response) => {
                    this.rows = this.prepareRows(response.rows);
                    return {
                        rows: this.rows,
                        pagination: response.pagination,
                    }
                });
            });
        },
        orderByClinics(a, b) {
            if (a.clinic_names[0] > b.clinic_names[0]) {
                return 1;
            }
            if (a.clinic_names[0] < b.clinic_names[0]) {
                return -1;
            }
            return 0;
        },
        rowClass(row) {
            if (row.price === null) {
                return '';
            }
            if (this.isInvalid(row.price)) {
                return ['changed', 'error'];
            }
            if (this.isJustClosed(row.price)) {
                return ['changed', 'closed', 'success'];
            }
            let changes = row.price.changed();
            if (changes === false) {
                return this.wasClosed(row.price) ? 'was-closed' : '';
            }
            if (this.wasClosed(row.price)) {
                return ['changed', 'was-closed', 'warning'];
            }
            if (changes.indexOf('date_from') !== -1) {
                return ['changed', 'success'];
            }
            return ['changed', 'warning'];
        },
        warningMessage(row) {
            if (row.price === null) {
                return false;
            }
            if (this.isInvalid(row.price)) {
                return __('При сохранении возникли ошибки, проверьте данные и попробуйте повторить попытку');
            }
            if (this.isJustClosed(row.price)) {
                return false;
            }
            let changes = row.price.changed();
            if (changes === false) {
                return false;
            }
            if (this.wasClosed(row.price)) {
                return __('Вы внесли изменения в закрытый тариф');
            }
            if (changes.indexOf('date_from') !== -1) {
                return false;
            }
            return __('Вы внесли изменения в тариф, но не обновили дату начала действия тарифа');
        },
        getTabIndex(index, base = 0) {
            return index + base;
        },
        isValidPrice(val) {
            return !isNaN(Number(val)) && val >= 0;
        },
        canBeSplit(price) {
            return price.clinics.length >= 2 && !this.isClosed(price);
        },
        canBeClosed(price) {
            return !this.isClosed(price);
        },
        addPrices(service, index) {
            let rows = this.createRows(service);
            this.rows.splice(index, 1, ...rows);
        },
        splitPrice(service, price, index) {
            let dateChanged = this.isDateChanged(price);
            let rows = [];
            while (price.clinics.length > 1) {
                let clinic = price.clinics.pop();
                let assignData = {
                    cost: price.cost,
                    self_cost: price.self_cost,
                };
                let initialData = {
                    clinics: [clinic],
                };
                if (dateChanged) {
                    assignData.date_from = price.date_from;
                } else {
                    initialData.date_from = price.date_from;
                }
                let row = this.createRow(service, assignData, initialData);
                if (row !== null) {
                    rows.push(row);
                }
            }
            if (!this.isMatchingFilter(price, this.activeFilters)) {
                this.rows.splice(index, 1, ...rows);
            } else {
                this.rows.splice(index + 1, 0, ...rows);
            }
        },
        closePrice(price) {
            if (this.$moment().isBefore(price.$.date_from)) {
                price.date_to = price.$.date_from;
            } else {
                price.date_to = this.$moment().format('YYYY-MM-DD');
            }
        },
        closeSelectedPrices(){
            this.getSelectedRows().forEach((row) => {
                if (this.canBeClosed(row.price)) {
                    this.closePrice(row.price);
                }
            });

        },
        getChangedRows() {
            let changed = [];
            this.eachPrice((price, row) => {
                if (this.isRowChanged(price)) {
                    changed.push(row);
                }
            });
            return changed;
        },
        hasChangedRows() {
            return this.rows.some((row) => {
                return row.price !== null && this.isRowChanged(row.price);
            });
        },
        getServicePrices(service) {
            let result = [];
            this.eachPrice((price, row) => {
                if (row.service.id == service.id) {
                    result.push(price);
                }
            });
            return result;
        },
        eachPrice(callback) {
            this.rows.forEach((item) => {
                if (item.price !== null) {
                    callback(item.price, item);
                }
            });
        },
        createRows(service) {
            let date = this.$moment().format('YYYY-MM-DD');
            let clinicFilter = this.getFilterClinic(this.activeFilters);
            let serviceClinics = service.clinics.map((c) => c.id);
            let clinics = clinicFilter !== undefined
                ? _.intersection(clinicFilter, serviceClinics)
                : serviceClinics;
            let result = [];
            clinics
                .filter((c) => this.$canManage(this.permissions + '.create', [c]))
                .forEach((clinic) => {
                    let row = this.createRow(service, {
                        date_from: date,
                        cost: 0,
                        self_cost: 0,
                    }, {
                        clinics: [clinic],
                    });
                    if (row !== null) {
                        result.push(row);
                    }
                });

            return result;
        },
        revertRow(service, price, index) {
            if (price.isNew()) {
                if (this.getServicePrices(service).length <= 1) {
                    this.rows.splice(index, 1, {
                        id: this.rowIdCounter++,
                        service: service,
                        price: null,
                    });
                } else {
                    this.removeRow(index);
                }
            } else {
                price.reset();
            }
            this.setValid(price);
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        },
        showHistory(price) {
            this.$modalComponent(History, {
                serviceId: price.service_id,
                serviceType: price.service_type,
                setType: this.activeFilters.set_type,
                clinics: price.clinics,
            }, {}, {
                header: __('История тарифа'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        isRowChanged(price) {
            return price.changed() !== false;
        },
        isClosed(price) {
            return price.date_to !== null;
        },
        isJustClosed(price) {
            return this.isClosed(price) && !this.wasClosed(price);
        },
        wasClosed(price) {
            return price.$.date_to !== null;
        },
        isInvalid(price) {
            return this.invalid.indexOf(price) !== -1;
        },
        isDateChanged(price) {
            let changes = price.changed();
            return changes !== false && changes.indexOf('date_from') !== -1;
        },
        isMatchingFilter(price, filters) {
            let clinic = this.getFilterClinic(filters);
            if (clinic !== undefined) {
                if (_.intersection(price.clinics, clinic).length === 0) {
                    return false;
                }
            }
            return true;
        },
        getFilterClinic(filters) {
            if (filters.clinic !== undefined && filters.clinic.length !== 0) {
                return filters.clinic;
            }
            if (filters.has_price !== undefined && filters.has_price.clinic !== undefined && filters.has_price.clinic.length !== 0) {
                return filters.has_price.clinic;
            }
            if (filters.has_no_price !== undefined && filters.has_no_price.clinic !== undefined && filters.has_no_price.clinic.length !== 0) {
                return filters.has_no_price.clinic;
            }
            if (this.availableClinics !== null) {
                return this.availableClinics.map(c => c.id);
            }
            return undefined;
        },
        selectionChanged() {
            this.hasSelection = this.getVuetable().selectedTo.length !== 0;
        },
        getSelection() {
            return this.getVuetable().selectedTo;
        },
        setSelection(rows) {
            this.getVuetable().selectedTo = rows;
            this.selectionChanged();
        },
        getSelectedRows() {
            let selection = this.getSelection();
            return this.rows.filter((row) => row.price !== null && selection.indexOf(row.id) !== -1);
        },
        getRowIndex(row) {
            return this.getVuetable().tableData.indexOf(row);
        },
        getVuetable() {
            return this.$refs.table.$refs.vuetable;
        },
        revertSelected() {
            this.getSelectedRows().forEach((row) => {
                if (this.isRowChanged(row.price)) {
                    this.revertRow(row.service, row.price, this.getRowIndex(row));
                }
            });
        },
        getManageTable() {
            return this.$refs.table;
        },
        splitSelected() {
            let hasPermissionsIssue = false;
            this.getSelectedRows().forEach((row) => {
                if (this.$canManage(this.permissions + '.update', row.price.clinics)) {
                    if (this.canBeSplit(row.price)) {
                        this.splitPrice(row.service, row.price, this.getRowIndex(row));
                    }
                } else {
                    hasPermissionsIssue = true;
                }
            });
            if (hasPermissionsIssue) {
                this.$warning(__('Не удалось разделить некоторые тарифы в связи с ограничениями доступа'));
            }
        },
        pickDateForSelected() {
            this.minDateForSelected = this.$moment('1990-01-01').toDate();
            this.dateForSelected = new Date();
            this.getSelectedRows().forEach((row) => {
                if (this.isClosed(row.price)) {
                    let date = this.$moment(row.price.$.date_from);
                    if (date.isAfter(this.minDateForSelected)) {
                        this.minDateForSelected = date.toDate();
                    }
                }
            });
            this.$refs.datepicker.focus();
        },
        setDateForSelected() {
            let hasPermissionsIssue = false;
            if (this.dateForSelected) {
                this.getSelectedRows().forEach((row) => {
                    if (this.$canManage(this.permissions + '.update', row.price.clinics)) {
                        this.setOpenCloseDate(row.price, this.dateForSelected);
                    } else {
                        hasPermissionsIssue = true;
                    }
                });
            }
            if (hasPermissionsIssue) {
                this.$warning(__('Не удалось обновить/закрыть некоторые тарифы в связи с ограничениями доступа'));
            }
        },
        setOpenCloseDate(price, date) {
            if (this.isClosed(price)) {
                price.date_to = date;
            } else {
                price.date_from = date;
            }
        },
        checkDisabledDate(date) {
            return this.$moment(date).isBefore(this.minDateForSelected);
        },
        setValid(price) {
            this.invalid = this.invalid.filter((item) => item !== price);
        },
        selectChanged() {
            let selection = [];
            this.eachPrice((price, row) => {
                if (this.isRowChanged(price)) {
                    selection.push(row.id);
                }
            });
            this.setSelection(selection);
        },
        checkOldDates(rows) {
            return new Promise((next) => {
                let hasOldDate = rows.some((row) => !this.isDateChanged(row.price) && !this.isClosed(row.price));
                if (hasOldDate) {
                    this.$confirm(__('Дата для некоторых тарифов осталась неизменной. Вы уверены, что хотите обновить данные тарифы с прежней датой?'), next);
                } else {
                    next();
                }
            });
        },
        checkChangesOnClosed(rows) {
            return new Promise((next) => {
                let hasChanges = rows.some((row) => this.wasClosed(row.price) && this.isRowChanged(row.price));
                if (hasChanges) {
                    this.$confirm(__('Вы внесли изменения в уже закрытые тарифы. Вы уверены, что хотите обновить эти тарифы?'), next);
                } else {
                    next();
                }
            });
        },
        checkPriceConflicts(rows) {
            let conflicts = [];
            rows.forEach((row) => {
                if (!this.isClosed(row.price)) {
                    let prices = this.getServicePrices(row.service);
                    let hasConflict = prices.some((p) => {
                        return p !== row.price && !this.isClosed(p) && this.hasConflict(p, row.price);
                    })
                    if (hasConflict) {
                        conflicts.push(row.price);
                    }
                }
            });
            return conflicts;
        },
        hasConflict(price1, price2) {
            if (price1.date_from == price2.date_from) {
                if (price1.cost != price2.cost || price1.self_cost != price2.self_cost) {
                    let clinics1 = price1.clinics.map((clinic) => Number(clinic));
                    let clinics2 = price2.clinics.map((clinic) => Number(clinic));
                    if (_.intersection(clinics1, clinics2).length !== 0) {
                        return true;
                    }
                }
            }
            return false;
        },
        isForUpdate(price) {
            if (price.isNew()) {
                return false;
            }
            if (this.isClosed(price)) {
                return true;
            }
            return !this.isDateChanged(price);
        },
        saveChanges() {
            let changed = this.getChangedRows();
            if (changed.length !== 0) {
                this.checkOldDates(changed).then(() => {
                    return this.checkChangesOnClosed(changed);
                }).then(() => {
                    let conflicts = this.checkPriceConflicts(changed);
                    if (conflicts.length === 0) {
                        this.completeSave(changed.map((row) => row.price));
                    } else {
                        this.$error(__('Некоторые тарифы назначены на одну и ту же дату с разными ценами. Пожалуйста, проверьте правильность данных.'));
                        this.invalid = conflicts;
                    }
                });
            }
        },
        completeSave(prices) {
            this.batchRequest.reset();
            prices.forEach((price) => {
                if (this.isForUpdate(price)) {
                    this.batchRequest.update(price);
                } else {
                    this.batchRequest.create(price);
                }
            });
            this.saving = true;
            this.invalid = [];
            this.batchRequest.submit().then((result) => {
                this.saving = false;
                if (result.failure.length !== 0) {
                    this.$error(__('Не удалось сохранить некоторые данные'));
                    this.invalid = result.failure;
                } else {
                    this.$info(__('Данные успешно обновлены'));
                    this.setSelection([]);
                }
            }).catch((error) => {
                this.saving = false;
                if (error.invalid) {
                    this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
                    this.invalid = error.invalid;
                }
            });
        },
        canChange(price) {
            if (price.isNew()) {
                return true;
            }
            return this.$canManage(this.permissions + '.update', price.clinics);
        },
        processRowsForExport(rows){
            rows.forEach((row) => {
                let set = null;

                if(this.tabs){
                    set = this.tabs.filter(el => el.id === row.price.set_type)[0];
                }else if(this.priceSets && row.price){
                    set = this.priceSets.filter(el => el.id === row.price.set_id)[0];
                }

                if(set){
                    row.set_name = set.value;
                }
            });

            return rows;
        },
        getLangBySuffix(suffix) {
            return (this.$store.state.user.langBySuffix(suffix) || {}).short_name || '';
        },
    },
    watch: {
        rows(val) {
            let intersection = _.intersection(this.getSelection(), val.map((item) => item.id));
            this.setSelection(intersection);
        },
        availableClinics() {
            this.clinicsRepository.notifyWatcher('filters', {});
        },
    },
};
</script>
