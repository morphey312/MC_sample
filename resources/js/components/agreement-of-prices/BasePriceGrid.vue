<template>
    <manage-table
        v-loading="saving || loading"
        ref="table"
        :fields="fields"
        :initial-fields="exceptSelfcost(fields)"
        :filters="filters"
        :scopes="scopes"
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
            </div>
        </template>
        <template slot="clinics" slot-scope="props">
            <template v-if="props.rowData.price">
                <form-select
                    :entity="props.rowData.price"
                    property="clinics"
                    :options="clinics"
                    control-size="mini"
                    :disabled="true"
                    :multiple="true"/>
            </template>
             <a v-else-if="canCreatePrice(props.rowData)"
                href="#"
                @click.prevent="addPrices(props.rowData.service, props.rowIndex)">
                {{ __('Добавить тариф') }}
            </a>
        </template>
        <template slot="cost" slot-scope="props">
            <template v-if="props.rowData.price">
                <span class="inline-input disabled">
                    {{ $formatter.numberFormat(props.rowData.price.cost) }}
                </span>
            </template>
        </template>
        <template slot="_recommended_cost" slot-scope="props">
            <template v-if="props.rowData.price">
                <inline-input
                    v-if="checkInAllowedClinics(props.rowData.price.clinics) || $can('price-agreement-acts.create-' + serviceType)"
                    v-model="props.rowData.price._recommended_cost"
                    :formatter="$formatter.numberFormat"
                    :validator="isValidPrice"
                    :tab-index="getTabIndex(props.rowIndex)"
                    :required="true" />
                <span v-else class="inline-input disabled">
                    {{ $formatter.numberFormat(props.rowData.price._recommended_cost) }}
                </span>
            </template>
        </template>
        <template slot="date_from" slot-scope="props">
            <template v-if="props.rowData.price">
                <span>
                    {{ $formatter.dateFormat(props.rowData.price.date_from) }}
                </span>
            </template>
        </template>
        <template slot="date_to" slot-scope="props">
            <template v-if="props.rowData.price">
                <span>
                    {{ $formatter.dateFormat(props.rowData.price.date_to) }}
                </span>
            </template>
        </template>
        <div
            v-if="$can('price-agreement-acts.create-' + serviceType) || $can('price-agreement-acts.create-' + serviceType + '-clinic')"
            class="buttons"
            slot="footer-top">
            <el-button
                @click="selectChanged">
                {{ __('Выбрать измененные') }}
            </el-button>
            <el-button
                type="primary"
                @click="saveChanges">
                {{ __('Сохранить изменения') }}
            </el-button>
            <el-button
                :disabled="changes.length === 0"
                @click="formationOfAnAct">
                {{ __('Выбранные тарифы') }}
            </el-button>
            <el-dropdown class="ml-10">
                <el-button >
                    {{ __('Еще') }}
                </el-button>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item>
                        <el-button
                            type="text"
                            :disabled="!hasSelection"
                            @click="splitSelected">
                            {{ __('Разделить тариф') }}
                        </el-button>
                    </el-dropdown-item>
                    <el-dropdown-item>
                        <el-button
                            type="text"
                            :disabled="!hasSelection"
                            @click="closeSelectedPrices">
                            {{ __('Закрыть услуги') }}
                        </el-button>
                    </el-dropdown-item>
                    <el-dropdown-item>
                        <el-button
                            type="text"
                            :disabled="!hasSelection"
                            @click="openInOtherClinic">
                            {{ __('Открыть в своей клинике') }}
                        </el-button>
                    </el-dropdown-item>
                    <el-dropdown-item>
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
import ProxyRepository from '@/repositories/proxy-repository';
import Pagination from './Pagination.vue';
import FormCreate from './FormCreate.vue';
import OpenInOtherClinic from './OpenInOtherClinic.vue';
import ClinicRepository from '@/repositories/clinic';
import CONSTANTS from '@/constants';
import Analysis from "@/models/analysis";
import Service from "@/models/service";

export default {
    props: {
        serviceType: {
            type: String,
            required: true,
        },
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
        permission: String,
        priceSets: Array
    },
    data() {
        let clinicsRepository = new ClinicRepository();
        return {
            changes: [],
            scopes: ['has_active_acts'],
            page: 1,
            rowIdCounter: 1,
            rows: [],
            hasSelection: false,
            invalid: [],
            activeFilters: this.filters,
            dateForSelected: null,
            minDateForSelected: null,
            loading: false,
            tempQuery: null,
            saving: false,
            clinics: [],
            proxyRepository: new ProxyRepository((query) => {
                this.tempQuery = query;

                return this.fetch(query);
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
                    name: '_recommended_cost',
                    title: __('Стоимость рекомендованная'),
                    width: '120px',
                    titleClass: 'text-right',
                    dataClass: 'text-right price-column',
                },
                {
                    name: 'date_from',
                    title: __('Дата начала тарифа'),
                    width: '120px',
                    dataClass: 'date-column',
                },
                {
                    name: 'date_to',
                    title: __('Дата закрытия тарифа'),
                    width: '120px',
                    dataClass: 'date-column',
                },
            ],
        };
    },
    mounted() {
        let message = __('На этой странице остались несохраненные данные, вы уверены, что хотите покинуть ее?');
        let condition = () => this.hasChangedRows();
        this.getClinics();
        this.$safeClose(message, condition);
        this.$confirmNavigation(message, condition);
    },
    beforeDestroy() {
        this.$unsafeClose();
    },
    methods: {
        saveChanges() {
            let fullAccess = this.$can('price-agreement-acts.create-' + this.serviceType)
            this.getSelectedRows().forEach((row) => {
                if ((!fullAccess && this.checkInAllowedClinics(row.price.clinics)) || fullAccess) {
                    if (this.isRowChanged(row.price)) {
                        let service = (row.service instanceof Service || row.service instanceof Analysis) ? row.service.clone().attributes : _.cloneDeep(row.service);
                        let exist = this.changes.find(item => {
                            return (item.price.service_id === row.price.service_id && 
                                _.isEqual(item.price.clinics, row.price.clinics));
                        });
                    
                        if (exist) {
                            this.$warning(__('Тариф, который вы сохраняете, уже находится в выбранных'), {
                                position: 'bottom-left'
                            });
                        } else {
                            let newRow = this.createRow(service, row.price.clone());
                            newRow.price._recommended_cost = row.price._recommended_cost
                            newRow.price._change_type = row.price._change_type
                            this.changes.push(newRow);
                        }
                    }
                }
            });
            this.$info(__('Изменения сохранены успешно!'));
        },
        getClinics() {
            this.clinicsRepository.fetchList().then((response) => {
                this.clinics = response;
            });
        },
        canCreatePrice(row) {
            return this.$canManageAny(this.permission, row.service.clinics.map(c => c.id));
        },
        checkInAllowedClinics(clinics) {
            return _.intersection(clinics, this.$store.state.user.clinics).length > 0
        },
        openInOtherClinic() {
            let selectRows = this.getSelectedRows();

            if (selectRows.length > 1) {
                return this.$warning(__('Выберите одну услугу для использования этой функции'))
            }
            if (this.isNewPrice(selectRows[0].price)
                || this.isJustClosed(selectRows[0].price)
                || selectRows[0].price._change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.ADD_CLINIC) {
                return this.$warning(__('Для выбранной услуги данная операция невозможна'))
            }

            let serviceClinics = selectRows[0].service.clinics;

            this.$modalComponent(OpenInOtherClinic, {
                clinicsWithCodes: serviceClinics,
                serviceType: this.serviceType,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                addClinics: (dialog, [serviceClinics, clinics]) => {
                    dialog.close();
                    this.createRowsWithNewClinics(this.getSelectedRows()[0], serviceClinics, clinics);
                }
            }, {
                header: __('Открыть в своей клинике'),
                width: '500',
            });
        },
        changeCost(price) {
            if(_.isNull(price._change_type) && price._recommended_cost > 0)
                price._change_type = CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CHANGE_COST
        },
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
                return this.repository.fetchPriceList(this.activeFilters, query.sort, this.scopes, query.page, query.limit).then((response) => {
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
            if (this.isClinicAdd(row.price)) {
                return ['new-clinic', 'success'];
            }
            if (this.isNewPrice(row.price)) {
                return ['new-price', 'success'];
            }
            if (this.isJustClosed(row.price)) {
                return ['changed', 'closed', 'success'];
            }
            if (this.recommendedCostChanged(row.price)) {
                return ['changed', 'warning'];
            }
            if (row.price.in_price_agreement_act === true) {
                return ['in-active-act', 'warning'];
            }
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
        createRowsWithNewClinics(service, serviceClinics, newClinics) {
            let index = this.getRowIndex(service);
            let rows = [];
            let priceClinics = newClinics.map((c) => c.id);
            let assignData = {
                date_from: this.$moment().format('YYYY-MM-DD'),
                cost: 0,
                _change_type: CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.ADD_CLINIC,
                _recommended_cost: 0,
                self_cost: 0,
            };
            let initialData = {
                clinics: priceClinics,
            };
            let row = this.createRow(service.service.clone().attributes, assignData, initialData);
            if (row !== null) {
                row.service.clinics = newClinics;
                rows.push(row);
            }
            this.rows.splice(index + 1, 0, ...rows);
        },
        splitPrice(service, price, index) {
            let rows = [];
            while (price.clinics.length !== 0) {
                let clinic = price.clinics.pop();
                let assignData = {
                    cost: price.cost,
                    date_from: price.date_from,
                    id: price.id,
                    _change_type: CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CHANGE_COST,
                    self_cost: price.self_cost,
                };
                let initialData = {
                    clinics: [clinic],
                };
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
        closePrice(row) {
            row.price._recommended_cost = 0;
            row.price._change_type = CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CLOSE_PRICE;
            this.rows.splice(this.getRowIndex(row), 1, ...[row]);
        },
        closeSelectedPrices(){
            let hasPermissionsIssue = false;
            this.getSelectedRows().forEach((row) => {
                if (this.$can('price-agreement-acts.create-' + this.serviceType) || this.checkInAllowedClinics(row.price.clinics)) {
                    if (this.canBeClosed(row.price) && (!this.isClinicAdd(row.price) || !this.isNewPrice(row.price))) {
                        this.closePrice(row);
                    }
                } else {
                    hasPermissionsIssue = true;
                }
            });
            if (hasPermissionsIssue) {
                this.$warning(__('Не удалось закрыть некоторые тарифы в связи с ограничениями доступа'));
            }
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
                .filter((c) => this.$canManage(this.permission, [c]))
                .forEach((clinic) => {
                    let row = this.createRow(service, {
                        date_from: date,
                        cost: 0,
                        _recommended_cost: 0,
                        _change_type: CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.NEW_PRICE,
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
                price._recommended_cost = 0;
                if (this.isJustClosed(price)) {
                    price._change_type = null;
                }
            }
            this.setValid(price);
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        },
        isRowChanged(price) {
            return (price.changed() !== false || !_.isEmpty(price._recommended_cost) || this.isJustClosed(price));
        },
        isClosed(price) {
            return price._change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CLOSE_PRICE;
        },
        isJustClosed(price) {
            return this.isClosed(price) && !this.wasClosed(price);
        },
        recommendedCostChanged(price) {
            return price._change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CHANGE_COST || price._recommended_cost > 0;
        },
        isClinicAdd(price) {
            return price._change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.ADD_CLINIC;
        },
        isNewPrice(price) {
            return price._change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.NEW_PRICE;
        },
        wasClosed(price) {
            return price.$._change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CLOSE_PRICE;
        },
        isInvalid(price) {
            return this.invalid.indexOf(price) !== -1;
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
                if (this.$can('price-agreement-acts.create-' + this.serviceType) || this.checkInAllowedClinics(row.price.clinics)) {
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
        setValid(price) {
            this.invalid = this.invalid.filter((item) => item !== price);
        },
        selectChanged() {
            let selection = [];
            this.eachPrice((price, row) => {
                if (this.recommendedCostChanged(row.price) || this.isNewPrice(row.price) || this.isClinicAdd(row.price) || this.isJustClosed(row.price)) {
                    selection.push(row.id);
                }
            });
            this.setSelection(selection);
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
        formationOfAnAct() {
            let rows = this.changes;

            if (rows.length !== 0) {
                this.$modalComponent(FormCreate, {
                    rows: rows,
                    serviceType: this.serviceType,
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    created: (dialog) => {
                        dialog.close();
                        this.getManageTable().refresh();
                        this.changes = [];
                    }
                }, {
                    header: __('Начать согласование прайсов'),
                    width: '1300px',
                });
            } else {
                this.$warning(__('Нет доступных изменений для создания акта согласования цен в связи с ограничениями доступа'));
            }
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

                if(this.priceSets && row.price){
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
    },
};
</script>
