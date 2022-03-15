<template>
    <page
        :title="__('Загрузка прайса для услуг')"
        v-loading="loading"
        type="flex">
        <top-form
            :input="input"
            :clinics="clinics"
            :disabled="rows.length !== 0"
            @sets-loaded="setsLoaded"
            @filepick="readFile"
            @reset="resetState" />
        <section class="darkgrey-cap shrinkable price-grid">
            <manage-table
                v-loading="saving"
                :element-loading-text="savingMessage"
                ref="table"
                :fields="fields"
                :repository="repository"
                :row-class="rowClass"
                :flex-height="true"
                :enable-pagination="false">
                <template slot="index" slot-scope="props">
                    {{ props.rowIndex + 1 }}
                </template>
                <template slot="service_name" slot-scope="props">
                    <div class="has-icon">
                        <span class="text-select flex-expand">
                            {{ props.rowData.service.name }}
                        </span>
                        <svg-icon
                            v-if="props.rowData.service.isNew()"
                            name="warning"
                            class="icon-tiny"
                            :title="getNewServiceWarning()" />
                        <template v-else>
                            <svg-icon
                                v-if="isReenabledService(props.rowData.service)"
                                name="warning"
                                class="icon-tiny"
                                :title="getDisabledServiceWarning()" />
                            <svg-icon
                                name="info-alt"
                                class="icon-tiny icon-grey"
                                @click.stop="showHistory(props.rowData.service.id)" />
                        </template>
                    </div>
                </template>
                <template slot="price_cost" slot-scope="props">
                    <span
                        class="inline-input disabled"
                        v-if="isClosed(props.rowData.price)">
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
                <template slot="price_self_cost" slot-scope="props">
                    <span
                        class="inline-input disabled"
                        v-if="isClosed(props.rowData.price)">
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
                <template slot="price_date" slot-scope="props">
                    <inline-datepicker
                        v-if="isClosed(props.rowData.price)"
                        v-model="props.rowData.price.date_to"
                        :min-date="props.rowData.price.$.date_from"
                        :tab-index="getTabIndex(props.rowIndex, 20000)"
                        :required="true" />
                    <inline-datepicker
                        v-else
                        v-model="props.rowData.price.date_from"
                        :tab-index="getTabIndex(props.rowIndex, 20000)"
                        :required="true" />
                </template>
                <div
                    class="buttons"
                    slot="footer-top">
                    <div class="float-right">
                        <el-button
                            type="primary"
                            :disabled="!canSave"
                            @click="upload">
                            {{ __('Сохранить') }}
                        </el-button>
                    </div>
                    <div class="table-summary mr-10">
                            {{ __('Всего позиций:') }} {{ totalRows }},
                            {{ __('обновленных тарифов:') }} <a href="#" @click.prevent="nextUpdatedPrice">{{ changedRows }}</a>,
                            {{ __('новых услуг:') }} <a href="#" @click.prevent="nextNewService">{{ newServices }}</a>,
                            {{ __('обновленных услуг:') }} <a href="#" @click.prevent="nextUpdatedServices">{{ updatedServices }}</a>.
                    </div>
                </div>
            </manage-table>
        </section>
    </page>
</template>

<script>
import UploadMixin from './mixin/upload';
import TopForm from './Top.vue';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import SpecializationRepository from '@/repositories/specialization';
import ProxyRepository from '@/repositories/proxy-repository';
import PaymentDestination from '@/models/service/payment-destination';
import Specialization from '@/models/specialization';
import Service from '@/models/service';
import History from '../History.vue';
import CONSTANT from '@/constants';
import BatchRequest from '@/services/batch-request';
import MultiSearchRequest from '@/services/multisearch';

export default {
    mixins: [UploadMixin],
    components: {
        TopForm,
    },
    data() {
        return {
            mapping: [
                {
                    name: 'specialization',
                    title: __('Специализация'),
                    index: 3,
                },
                {
                    name: 'code',
                    title: __('Код'),
                    index: 4,
                },
                {
                    name: 'name',
                    title: __('Название услуги'),
                    index: 5,
                    filter: (v) => this.clearString(v),
                },
                ...(this.hasLangSuffix('lc1') ? [{
                    name: 'name_lc1',
                    title: __('Название услуги') + ' (' + this.getLangBySuffix('lc1') + ')',
                    index: 6,
                }] : []),
                ...(this.hasLangSuffix('lc2') ? [{
                    name: 'name_lc2',
                    title: __('Название услуги') + ' (' + this.getLangBySuffix('lc2') + ')',
                    index: -1,
                }] : []),
                ...(this.hasLangSuffix('lc3') ? [{
                    name: 'name_lc3',
                    title: __('Название услуги') + ' (' + this.getLangBySuffix('lc3') + ')',
                    index: -1,
                }] : []),
                {
                    name: 'name_ua',
                    title: __('Название для чека'),
                    index: 7,
                },
                ...(this.hasLangSuffix('lc1') ? [{
                    name: 'name_ua_lc1',
                    title: __('Название для чека') + ' (' + this.getLangBySuffix('lc1') + ')',
                    index: -1,
                }] : []),
                ...(this.hasLangSuffix('lc2') ? [{
                    name: 'name_ua_lc2',
                    title: __('Название для чека') + ' (' + this.getLangBySuffix('lc2') + ')',
                    index: -1,
                }] : []),
                ...(this.hasLangSuffix('lc3') ? [{
                    name: 'name_ua_lc3',
                    title: __('Название для чека') + ' (' + this.getLangBySuffix('lc3') + ')',
                    index: -1,
                }] : []),
                {
                    name: 'is_base',
                    title: __('Базовая'),
                    index: 8,
                },
                {
                    name: 'payment_destination',
                    title: __('Назначение платежа'),
                    index: 9,
                },
                {
                    name: 'self_cost',
                    title: __('Себестоимость'),
                    index: 10,
                },
                {
                    name: 'cost',
                    title: __('Стоимость'),
                    index: 11,
                },
                {
                    name: 'currency',
                    title: __('Валюта'),
                    index: 12,
                },
                {
                    name: 'date',
                    title: __('Дата'),
                    index: 13,
                },
                {
                    name: 'site_service_type',
                    title: __('Тип услуги на сайте'),
                    index: 14,
                },
                {
                    name: 'is_online',
                    title: __('Онлайн-видеоконсультация'),
                    index: 15,
                }
            ],
            fields: [{
                name: 'index',
                title: '',
                width: '3%',
            }, {
                name: 'service.name_i18n',
                title: __('Название услуги'),
                width: '18%',
                dataClass: 'no-ellipsis text-select',
            }, {
                name: 'service.name_lc1',
                title: __('Название услуги') + ' (' + this.getLangBySuffix('lc1') + ')',
                width: '18%',
                dataClass: 'no-ellipsis text-select',
            }, {
                name: 'service.name_ua_i18n',
                title: __('Название для чека'),
                width: '17%',
                dataClass: 'no-ellipsis text-select',
            }, {
                name: 'specialization.value',
                title: __('Специализация'),
                width: '11%',
            }, {
                name: 'service.is_base',
                title: __('Базовая'),
                width: '6%',
                dataClass: 'no-dash',
                formatter: (value) => {
                    return this.$formatter.boolToString(value, '<span class="check-yes" />');
                },
            }, {
                name: 'payment_destination.value',
                title: __('Назначение платежа'),
                width: '10%',
            }, {
                name: 'price_cost',
                title: __('Стоимость'),
                width: '9%',
                titleClass: 'text-right',
                dataClass: 'text-right',
            }, {
                name: 'price_self_cost',
                title: __('Себестоимость'),
                width: '10%',
                titleClass: 'text-right',
                dataClass: 'text-right',
            }, {
                name: 'price.currency',
                title: __('Валюта'),
                width: '6%',
                formatter: (value) => {
                    return this.$handbook.getOption('currency', value);
                },
            }, {
                name: 'price_date',
                title: __('Дата открытия/закрытия тарифа'),
                width: '10%',
            }],
            paymentDestinations: null,
            specializations: null,
            lastNewServiceRow: -1,
            lastUpdatedPriceRow: -1,
            lastUpdatedServiceRow: -1,
            siteServiceTypes: null,
            isOnlineVideoConsultations: null,
        };
    },
    methods: {
        getNewServiceWarning() {
            return __('Для данной услуги не найдено соответствия, она будет создана как новая');
        },
        getDisabledServiceWarning() {
            return __('Данная услуга неактивна, она будет вновь активирована');
        },
        convertRow(row, request) {
            let result = {};
            result.payment_destination = this.fromDict(this.paymentDestinations, row.payment_destination, () => {
                return null;
            });

            if (result.payment_destination === null) {
                this.$error(__('Не найдено соответствия для назначения платежа {destination}. Убедитесь, что данное назначение существует в базе данных и корректно указано в импортируемом файле', {destination: row.payment_destination}));
                return null;
            }

            result.specialization = this.fromDict(this.specializations, row.specialization, () => {
                return null;
            });

            if (result.specialization === null) {
                this.$error(__('Не найдено соответствия для специализации {specialization}. Убедитесь, что данная специализация существует в базе данных и корректно указана в импортируемом файле', {specialization: row.specialization}));
                return null;
            }

            if (row.site_service_type !== "" && row.site_service_type !== undefined) {
                result.site_service_type = this.fromDict(this.siteServiceTypes, row.site_service_type, () => {
                    return null;
                })

                if (result.site_service_type === null) {
                    this.$error(__('Не найдено соответствия для типа услуги на сайте {serviceType}. Убедитесь, что данный тип услуги сущействует в базе данных и корректно указан в импортируемом файле', {serviceType: row.site_service_type}));
                    return null;
                }
            }

            if (row.is_online !== "" && row.is_online !== undefined) {
                result.is_online = this.fromDict(this.isOnlineVideoConsultations, row.is_online, () => {
                    return null;
                });

                if (result.is_online === null) {
                    this.$error(__('Не найдено соответствия для пометки онлайн-видеоконсультации {isOnline}. Убедитесь, что данная пометка существует в базе данных и корректно указана в импортируемом файле', {isOnline: row.is_online}));
                    return null;
                } else {
                    result.is_online.id = Boolean(result.is_online.id);
                }
            }

            result.code = row.code;

            request.search({
                    name: `=${row.name}`,
                    specialization: result.specialization.id,
                    payment_destination: result.payment_destination.id,
                }, (found) => {
                    if (found.length === 0) {
                        result.service = new Service({
                            name: row.name,
                            name_lc1: row.name_lc1 || null,
                            name_lc2: row.name_lc2 || null,
                            name_lc3: row.name_lc3 || null,
                            name_ua: row.name_ua || row.name,
                            name_ua_lc1: row.name_ua_lc1 || null,
                            name_ua_lc2: row.name_ua_lc2 || null,
                            name_ua_lc3: row.name_ua_lc3 || null,
                            clinics: [{
                                clinic_id: this.input.clinic,
                                code: this.toStrCode(row.code),
                            }],
                            specialization_id: result.specialization.id,
                            payment_destination_id: result.payment_destination.id,
                            is_base: row.is_base == 1,
                            site_service_type: (row.site_service_type !== "" && row.site_service_type !== undefined) ? result.site_service_type.id : null,
                            is_online: (row.is_online !== "" && row.is_online !== undefined) ?  result.is_online.id : false,
                        });
                    } else {
                        result.service = new Service(found[0]);

                        if (!this.serviceHasClinic(result.service, this.input.clinic)) {
                            result.service.clinics.push({
                                clinic_id: this.input.clinic,
                                code: this.toStrCode(row.code),
                            });
                        }

                        if (_.isFilled(row.name_lc1) && result.service.name_lc1 !== row.name_lc1) {
                            result.service.name_lc1 = row.name_lc1;
                        }

                        if (_.isFilled(row.name_lc2) && result.service.name_lc2 !== row.name_lc2) {
                            result.service.name_lc2 = row.name_lc2;
                        }

                        if (_.isFilled(row.name_lc3) && result.service.name_lc3 !== row.name_lc3) {
                            result.service.name_lc3 = row.name_lc3;
                        }

                        if (_.isFilled(row.name_ua) && result.service.name_ua !== row.name_ua) {
                            result.service.name_ua = row.name_ua;
                        }

                        if (_.isFilled(row.name_ua_lc1) && result.service.name_ua_lc1 !== row.name_ua_lc1) {
                            result.service.name_ua_lc1 = row.name_ua_lc1;
                        }

                        if (_.isFilled(row.name_ua_lc2) && result.service.name_ua_lc2 !== row.name_ua_lc2) {
                            result.service.name_ua_lc2 = row.name_ua_lc2;
                        }

                        if (_.isFilled(row.name_ua_lc3) && result.service.name_ua_lc3 !== row.name_ua_lc3) {
                            result.service.name_ua_lc3 = row.name_ua_lc3;
                        }

                        if (result.service.is_base !== (row.is_base == 1)) {
                            result.service.is_base = row.is_base == 1;
                        }

                        if (result.service.disabled) {
                            result.service.disabled = false;
                        }

                        if (_.isFilled(row.site_service_type) && result.service.site_service_type !== result.site_service_type.id) {
                            result.service.site_service_type = result.site_service_type.id;
                        } else if (row.site_service_type === "") {
                            result.service.site_service_type = null;
                        }

                        if (_.isFilled(row.is_online) && result.service.is_online !== result.is_online.id) {
                            result.service.is_online = result.is_online.id;
                        }
                    }

                    result.price = this.createPriceUpdate(row, result.service.prices);
                    if (result.price === null) {
                        this.$error(__('Вы пытаетесь закрыть тариф для услуги {name}, который уже закрыт', {name: result.service.name}));
                    }
                });

            return result;
        },
        loadDicts() {
            return this.loadPaymentDestinations().then(() => {
                return this.loadSpecializations().then(() => {
                    this.loadSiteServiceTypes();
                    return this.loadIsOnlineVideoConsultations();
                });
            });
        },
        loadPaymentDestinations() {
            if (this.paymentDestinations !== null) {
                return Promise.resolve(this.paymentDestinations);
            }
            let repository = new PaymentDestinationRepository();
            return repository.fetchList().then((result) => {
                this.paymentDestinations = this.makeDict(result, 'value');
                return this.paymentDestinations;
            });
        },
        loadSpecializations() {
            if (this.specializations !== null) {
                return Promise.resolve(this.specializations);
            }
            let repository = new SpecializationRepository();
            return repository.fetchList().then((result) => {
                this.specializations = this.makeDict(result, 'value');
                return this.specializations;
            });
        },
        loadSiteServiceTypes() {
            if (this.siteServiceTypes !== null) {
                return Promise.resolve(this.siteServiceTypes);
            }
            let types = this.$handbook.getOptions('site_service_type');
            this.siteServiceTypes = this.makeDict(types, 'value');

            return this.siteServiceTypes;
        },
        loadIsOnlineVideoConsultations() {
            if (this.isOnlineVideoConsultations !== null) {
                return Promise.resolve(this.isOnlineVideoConsultations);
            }
            let bool = this.$handbook.getOptions('yes_no');
            this.isOnlineVideoConsultations = this.makeDict(bool, 'value');

            return this.isOnlineVideoConsultations;
        },
        showHistory(serviceId) {
            this.$modalComponent(History, {
                serviceId,
                serviceType: CONSTANT.PRICE.SERVICE_TYPE.SERVICES,
                set_id: this.input.set_id,
                clinics: [this.input.clinic],
            }, {}, {
                header: __('История тарифа'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        getSaveServicesMessage() {
            return __('Сохранение услуг...');
        },
        getSaveServicesError() {
            return __('Не удалось сохранить некоторые услуги');
        },
        getServicesBatchRequest() {
            return new BatchRequest('/api/v1/services/batch');
        },
        getPricesBatchRequest() {
            return new BatchRequest('/api/v1/services/prices/batch');
        },
        getServiceSearchRequest() {
            return new MultiSearchRequest('/api/v1/services/multisearch', [
                'clinics',
                {
                    name: 'prices',
                    params: {
                        clinic: this.input.clinic,
                        set_id: this.input.set_id,
                        recent: 1,
                    },
                },
            ]);
        },
        saveNewService(request, row) {
            if (row.service.isNew()) {
                request.create(row.service);
            } else if (this.isUpdatedService(row.service)) {
                request.update(row.service);
            }
        },
        serviceHasClinic(service, clinic) {
            return service.clinics.some(c => c.clinic_id === clinic);
        },
        isUpdatedService(service) {
            return this.wereChanged(service.changed(), ['clinics', 'name_lc1',
                'name_lc2', 'name_lc3', 'name_ua', 'name_ua_lc1', 'name_ua_lc2',
                'name_ua_lc3', 'is_base', 'disabled', 'site_service_type', 'is_online']);
        },
        isReenabledService(service) {
            return service.$.disabled && !service.disabled;
        },
        nextNewService() {
            this.lastNewServiceRow = this.$refs.table.scrollToRow((row, index) => {
                return index > this.lastNewServiceRow && row.service.isNew();
            });
        },
        nextUpdatedPrice() {
            this.lastUpdatedPriceRow = this.$refs.table.scrollToRow((row, index) => {
                return index > this.lastUpdatedPriceRow && this.isUpdatedPrice(row.price);
            });
        },
        nextUpdatedServices() {
            this.lastUpdatedServiceRow = this.$refs.table.scrollToRow((row, index) => {
                return index > this.lastUpdatedServiceRow && this.isUpdatedService(row.service);
            });
        },
        afterReset() {
            this.lastNewServiceRow = -1;
            this.lastUpdatedPriceRow = -1;
            this.lastUpdatedServiceRow = -1;
        },
    }
};
</script>
