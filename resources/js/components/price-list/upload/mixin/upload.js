import Excel from 'exceljs';
import CONSTANT from '@/constants';
import ClinicRepository from '@/repositories/clinic';
import Mapping from '../Mapping.vue';
import handbook from '@/services/handbook';
import Price from '@/models/price';
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    computed: {
        totalRows() {
            return this.rows.length;
        },
        changedRows() {
            return this.rows.filter((r) => this.isUpdatedPrice(r.price)).length;
        },
        newServices() {
            return this.rows.filter((r) => r.service.isNew()).length;
        },
        updatedServices() {
            return this.rows.filter((r) => this.isUpdatedService(r.service)).length;
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('service-prices') || this.$isAccessLimited('analysis-prices')
            }),
            loading: false,
            saving: false,
            canSave: false,
            savingMessage: __('Сохранение...'),
            input: {
                clinic: null,
                set_id: null,
            },
            rows: [],
            invalid: [],
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.rows,
                });
            }),
        };
    },
    methods: {
        setsLoaded(setId) {
            this.input.set_id = setId;
        },
        readFile(file) {
            let workbook = new Excel.Workbook();
            let reader = new FileReader();
            this.loading = true;
            reader.onload = () => {
                workbook.xlsx.load(reader.result).then(() => {
                    this.loadDicts().then(() => {
                        this.loading = false;
                        this.confirmMapping(workbook);
                    });
                }).catch((e) => {
                    this.$error(__('Не удалось прочитать файл, убедитесь, что он сохранен в формате XLSX'));
                    this.loading = false;
                });
            }
            reader.onerror = () => {
                this.$error(__('Не удалось прочитать файл'));
            }
            reader.readAsArrayBuffer(file);
        },
        confirmMapping(workbook) {
            this.$modalComponent(Mapping, {
                workbook,
                mapping: this.mapping,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                confirm: (dialog, {data, mapping}) => {
                    dialog.close();
                    this.mapData(data, mapping);
                },
            }, {
                header: __('Задайте соответствие полей'),
                width: '600px',
            });
        },
        mapData(data, mapping) {
            let rows = [];
            let request = this.getServiceSearchRequest();
            this.resetData();
            data.forEach((row) => {
                let mapped = {};
                mapping.forEach((map) => {
                    if (map.index >= 0 && map.index < row.length) {
                        let v = this.parseValue(row[map.index]);
                        if (typeof v === 'string') {
                            v = _.trim(v);
                        }
                        if (map.filter !== undefined) {
                            v = map.filter(v);
                        }
                        mapped[map.name] = v;
                    }
                });
                let convertedRow = this.convertRow(mapped, request);
                if (convertedRow !== null) {
                    rows.push(convertedRow);
                }
            });
            this.loading = true;
            request.submit().then(() => {
                this.rows = rows.filter(r => r.price !== null);
                this.$refs.table.refresh();
                this.canSave = true;
                this.loading = false;
            }).catch(() => {
                this.$error(__('При загрузке списка услуг произошла ошибка'));
                this.loading = false;
            });
        },
        parseValue(v) {
            if (typeof v === 'object') {
                if (v instanceof Date) {
                    return v;
                }
                if (v.richText !== undefined) {
                    return v.richText.map(p => p.text).join('');
                }
                if (v.hyperlink !== undefined) {
                    return v.text;
                }
                return v.result;
            }
            return v;
        },
        formatDate(v, isCloseDate = false) {
            let moment = (v instanceof Date)
                ? this.$moment(v)
                : this.$moment(v, [
                    'YYYY-MM-DD',
                    'DD.MM.YYYY',
                    'MM/DD/YYYY',
                ]);

            if (isCloseDate) {
                moment = moment.subtract(1, 'days');
            }

            return moment.format('YYYY-MM-DD');
        },
        resetState() {
            this.resetData();
            this.$refs.table.refresh();
        },
        resetData() {
            this.rows.forEach((row) => {
                if (row.service.prices !== undefined) {
                    row.service.prices.forEach((price) => {
                        price.reset();
                    });
                }
            });
            this.rows = [];
            this.invalid = [];
            this.canSave = false;
            this.afterReset();
        },
        afterReset() {
        },
        makeDict(items, keyProp = 'name') {
            let dict = {};
            items.forEach((item) => {
                dict[String(item[keyProp]).toLowerCase()] = item;
            });
            return dict;
        },
        fromDict(dict, key, factory) {
            let lckey = key.toLowerCase();
            let entry = dict[lckey];
            if (entry === undefined) {
                return dict[lckey] = factory();
            }
            return entry;
        },
        getCurrency(name) {
            let currency = handbook.getOptionKey('currency', name);
            if (currency === undefined) {
                currency = CONSTANT.CURRENCY.UAH;
            }
            return currency;
        },
        getLangBySuffix(suffix) {
            return (this.$store.state.user.langBySuffix(suffix) || {}).short_name || '';
        },
        hasLangSuffix(suffix) {
            return this.$store.state.user.langBySuffix(suffix) !== null;
        },
        createPriceUpdate(data, prices) {
            let oldPrice = prices === undefined ? undefined : prices[0];
            if (data.cost >= 0) {
                if (oldPrice !== undefined && !this.priceWasChanged(data, oldPrice)) {
                    oldPrice.date_from = this.formatDate(data.date);
                    return oldPrice;
                }
                return new Price({
                    cost: data.cost,
                    self_cost: data.self_cost,
                    date_from: this.formatDate(data.date),
                    set_id: this.input.set_id,
                    currency: this.getCurrency(data.currency),
                    clinics: [this.input.clinic],
                });
            } else if (oldPrice !== undefined) {
                if (oldPrice.clinics.length === 1) {
                    oldPrice.date_to = this.formatDate(data.date, true);
                    return oldPrice;
                }
                return new Price({
                    cost: oldPrice.cost,
                    self_cost: oldPrice.self_cost,
                    date_from: oldPrice.date_from,
                    date_to: this.formatDate(data.date, true),
                    set_id: oldPrice.set_id,
                    currency: oldPrice.currency,
                    clinics: [this.input.clinic],
                });
            }
            return null;
        },
        priceWasChanged(data, price) {
            return data.cost !== undefined && Number(data.cost).toFixed(2) !== Number(price.cost).toFixed(2)
                || data.self_cost !== undefined && Number(data.self_cost).toFixed(2) !== Number(price.self_cost).toFixed(2);
        },
        isValidPrice(val) {
            return !isNaN(Number(val)) && val >= 0;
        },
        getTabIndex(index, base = 0) {
            return index + base;
        },
        isClosed(price) {
            return price.date_to !== null;
        },
        isInvalid(row) {
            return this.invalid.indexOf(row) !== -1;
        },
        isUpdatedPrice(price) {
            if (price.isNew()) {
                return true;
            }
            if (this.isClosed(price)) {
                return true;
            }
            if (this.wereChanged(price.changed(), ['cost', 'self_cost'])) {
                return true;
            }
            return false;
        },
        isUpdatedService(service) {
            if (this.wereChanged(service.changed(),
                [
                    'name_lc1',
                    'name_lc2',
                    'name_lc3',
                    'name_ua',
                    'name_ua_lc1',
                    'name_ua_lc2',
                    'name_ua_lc3',
                    'is_base',
                    'disabled',
                    'site_service_type',
                    'is_online',
                ])) {
                return true;
            }
            return false;
        },
        isUpdatedAnalyses(service) {
            if (this.wereChanged(service.changed(),
                [
                    'clinics',
                    'laboratory_id',
                    'laboratory_code',
                    'disabled',
                    'description',
                ])) {
                return true;
            }
            return false;
        },
        rowClass(row) {
            if (this.isInvalid(row)) {
                return ['changed', 'error'];
            }
            if (row.service.isNew()) {
                return ['changed', 'warning'];
            }
            if (this.isClosed(row.price)) {
                return ['changed', 'closed', 'success'];
            }
            if (row.price.isNew()) {
                return ['changed', 'success'];
            }
            if (this.wereChanged(row.price.changed(), ['cost', 'self_cost'])) {
                return ['changed', 'success'];
            }
            return '';
        },
        upload() {
            this.saving = true;
            this.savingMessage = __('Сохранение...');
            this.saveNewServices().then(() => {
                return this.savePrices();
            }).then(() => {
                this.saving = false;
                this.resetState();
                this.$info(__('Новые тарифы были успешно сохранены'));
            }).catch(() => {
                this.saving = false;
                this.$error(__('Не удалось сохранить новые тарифы'));
            });
        },
        saveNewServices() {
            let request = this.getServicesBatchRequest();
            this.rows.forEach((row) => {
                this.saveNewService(request, row);
            });
            if (request.isNotEmpty) {
                this.savingMessage = this.getSaveServicesMessage();
                return request.submit().then((result) => {
                    if (result.failure.length !== 0) {
                        return Promise.reject({
                            invalid: result.failure,
                        });
                    }
                }).catch((error) => {
                    this.$error(this.getSaveServicesError());
                    if (error.invalid !== undefined) {
                        this.setFailedServices(error.invalid);
                    }
                    return Promise.reject(error);
                });
            } else {
                return Promise.resolve();
            }
        },
        savePrices() {
            let request = this.getPricesBatchRequest();
            this.savingMessage = __('Обновление тарифов...');
            this.rows.forEach((row) => {
                if (row.price.isNew()) {
                    row.price.service_id = row.service.id;
                    request.create(row.price);
                } else if (this.isClosed(row.price)) {
                    request.update(row.price);
                } else if (this.isUpdatedPrice(row.price)) {
                    let oldPrice = row.price;
                    row.price = new Price({
                        cost: row.price.cost,
                        self_cost: row.price.self_cost,
                        date_from: row.price.date_from,
                        set_id: row.price.set_id,
                        currency: row.price.currency,
                        clinics: [this.input.clinic],
                    });
                    row.price.service_id = row.service.id;
                    oldPrice.reset();
                    request.create(row.price);
                }
            });
            if (request.isNotEmpty) {
                return request.submit().then((result) => {
                    if (result.failure.length !== 0) {
                        return Promise.reject({
                            invalid: result.failure,
                        });
                    }
                }).catch((error) => {
                    this.$error(__('Не удалось сохранить некоторые тарифы'));
                    if (error.invalid !== undefined) {
                        this.setFailedPrices(error.invalid);
                    }
                    return Promise.reject(error);
                });
            } else {
                return Promise.resolve();
            }
        },
        setFailedServices(list) {
            let invalid = [];
            this.rows.forEach((row) => {
                if (list.indexOf(row.service) !== -1) {
                    invalid.push(row);
                }
            });
            this.invalid = invalid;
        },
        setFailedPrices(list) {
            let invalid = [];
            this.rows.forEach((row) => {
                if (list.indexOf(row.price) !== -1) {
                    invalid.push(row);
                }
            });
            this.invalid = invalid;
        },
        wereChanged(changes, attributes) {
            if (changes !== false) {
                for (let attr of attributes) {
                    if (changes.indexOf(attr) !== -1) {
                        return true;
                    }
                }
            }
            return false;
        },
        toStrCode(code) {
            return String(code || '');
        },
        clearString(str) {
            return _.trim(
                    String(str)
                        // replace various single quotes to basic single quote
                        .replace(/[\u2018-\u201B]/g, '\'')
                        // replace various double quotes to basic double quote
                        .replace(/[\u201C-\u201F]/g, '"')
                        // filter all unwanted characters, i.e. all except:
                        // \u0020-\u007E\u00AB\u00BB\u2116  - space, numbers, latin letters and general symbols
                        // \u0391-\u03A9\u03B1-\u03C9  - greek letters
                        // \u0401\u0403\u0404\u0406\u0407\u0451\u0453\u0454\u0456\u0457\u0410-\u044F  - cyrillic letters
                        // \u00B0\u00B2\u00B3\u00B9\u2070\u2074-\u208E\u2090-\u209C  - Superscripts and subscripts
                        .replace(/[^\u0020-\u007E\u00AB\u00BB\u2116\u0391-\u03A9\u03B1-\u03C9\u0401\u0403\u0404\u0406\u0407\u0451\u0453\u0454\u0456\u0457\u0410-\u044F\u00B0\u00B2\u00B3\u00B9\u2070\u2074-\u208E\u2090-\u209C]/g, ' ')
                )
                // replace 2+ spaces with single one
                .replace(/\s{2,}/g, ' ')
                // replace double quote with single quote (apostrophe)
                .replace(/[^\s,.:;()]["][^\s,.:;()]/g, (m) => {
                    return m.substr(0, 1) + '\'' + m.substr(2, 1);
                })
                // remove space before punctuations
                .replace(/\s+[,.;:]/g, (m) => {
                    return _.trim(m);
                })
                // remove space after open brackets and before close brackets
                .replace(/[(]\s+/g, '(')
                .replace(/\s+[)]/g, ')');
        },
    },
};
