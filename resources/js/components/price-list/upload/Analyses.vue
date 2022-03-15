<template>
    <page
        :title="__('Загрузка прайса для анализов')"
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
                            :title="getNewAnalysisWarning()" />
                        <template v-else>
                            <svg-icon
                                v-if="isReenabledService(props.rowData.service)"
                                name="warning"
                                class="icon-tiny"
                                :title="getDisabledAnalysisWarning()" />
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
                    <el-row>
                        <el-col>
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
                                    {{ __('новых анализов:') }} <a href="#" @click.prevent="nextNewAnalysis">{{ newServices }}</a>,
                                    {{ __('обновленных анализов:') }} <a href="#" @click.prevent="nextUpdatedAnalysis">{{ updatedServices }}</a>.
                            </div>
                        </el-col>
                    </el-row>
                </div>
            </manage-table>
        </section>
    </page>
</template>

<script>
import UploadMixin from './mixin/upload';
import TopForm from './Top.vue';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import ProxyRepository from '@/repositories/proxy-repository';
import Analysis from '@/models/analysis';
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
                    name: 'laboratory',
                    title: __('Лаборатория'),
                    index: 3,
                },
                {
                    name: 'code',
                    title: __('Код лаборатории'),
                    index: 4,
                },
                {
                    name: 'clinic_code',
                    title: __('Код клиники'),
                    index: 5,
                },
                {
                    name: 'name',
                    title: __('Название анализа'),
                    index: 6,
                    filter: (v) => this.clearString(v),
                },
                {
                    name: 'description',
                    title: __('Описание'),
                    index: 12,
                },
                {
                    name: 'days',
                    title: __('Количество дней'),
                    index: 7,
                },
                {
                    name: 'self_cost',
                    title: __('Себестоимость'),
                    index: 8,
                },
                {
                    name: 'cost',
                    title: __('Стоимость'),
                    index: 9,
                },
                {
                    name: 'currency',
                    title: __('Валюта'),
                    index: 10,
                },
                {
                    name: 'date',
                    title: __('Дата'),
                    index: 11,
                },
            ],
            fields: [{
                name: 'index',
                title: '',
                width: '3%',
            }, {
                name: 'service_name',
                title: __('Название анализа'),
                width: '30%',
                dataClass: 'no-ellipsis text-select',
            }, {
                name: 'service.description',
                title: __('Описание'),
                width: '27%',
                dataClass: 'no-ellipsis',
            }, {
                name: 'laboratory.value',
                title: __('Лаборатория'),
                width: '9%',
            }, {
                name: 'service.laboratory_code',
                title: __('Код лаборатории'),
                width: '9%',
                dataClass: 'text-select',
            }, {
                name: 'clinic_code',
                title: __('Код клиники'),
                width: '9%',
            }, {
                name: 'days',
                title: __('Кол-во дней'),
                width: '5%',
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
            laboratories: null,
            lastNewAnalysisRow: -1,
            lastUpdatedPriceRow: -1,
            lastUpdatedAnalysesRow: -1,
        };
    },
    methods: {
        getNewAnalysisWarning() {
            return __('Для данного анализа не найдено соответствия, он будет создан как новый');
        },
        getDisabledAnalysisWarning() {
            return __('Данный анализ неактивен, он будет вновь активирован');
        },
        convertRow(row, request) {
            let result = {};
            result.laboratory = this.fromDict(this.laboratories, row.laboratory, () => {
                return null;
            });

            if (result.laboratory === null) {
                this.$error(__('Не найдено соответствия для лаборатории {laboratory}. Убедитесь, что данная лаборатория существует в базе данных и корректно указана в импортируемом файле', {laboratory: row.laboratory}));
                return null;
            }

            result.clinic_code = row.clinic_code;
            result.days = row.days;

            request.search({
                    name: `=${row.name}`,
                    laboratory: result.laboratory.id,
                }, (found) => {
                    if (found.length === 0) {
                        result.service = new Analysis({
                            name: row.name,
                            description: row.description,
                            laboratory_id: result.laboratory.id,
                            laboratory_code: this.toStrCode(row.code),
                            clinics: [{
                                clinic_id: this.input.clinic,
                                code: this.toStrCode(result.clinic_code),
                                duration_days: result.days,
                            }],
                        });
                    } else {
                        result.service = new Analysis(found[0]);

                        let clinic = this.serviceGetClinic(result.service, this.input.clinic);
                        if (clinic === undefined) {
                            result.service.clinics.push({
                                clinic_id: this.input.clinic,
                                code: this.toStrCode(row.clinic_code),
                                duration_days: row.days,
                            });
                        } else if (clinic.code != row.clinic_code || clinic.duration_days != row.days) {
                            this.replaceClinic(result.service, clinic, {
                                code: this.toStrCode(row.clinic_code),
                                duration_days: row.days,
                            });
                        }

                        if (result.service.laboratory_id !== result.laboratory.id) {
                            result.service.laboratory_id = result.laboratory.id;
                        }

                        if (result.service.laboratory_code != row.code) {
                            result.service.laboratory_code = this.toStrCode(row.code);
                        }

                        if (result.service.disabled) {
                            result.service.disabled = false;
                        }

                        if (row.description && row.description !== result.service.description) {
                            result.service.description = row.description;
                        }
                    }

                    result.price = this.createPriceUpdate(row, result.service.prices);
                    if (result.price === null) {
                        this.$error(__('Вы пытаетесь закрыть тариф для анализа {name}, который уже закрыт', {name: result.service.name}));
                    }
                });

            return result;
        },
        loadDicts() {
            return this.loadLaboratories();
        },
        loadLaboratories() {
            if (this.laboratories !== null) {
                return Promise.resolve(this.laboratories);
            }
            let repository = new LaboratoryRepository();
            return repository.fetchList().then((result) => {
                this.laboratories = this.makeDict(result, 'value');
                return this.laboratories;
            });
        },
        showHistory(serviceId) {
            this.$modalComponent(History, {
                serviceId,
                serviceType: CONSTANT.PRICE.SERVICE_TYPE.ANALYSIS,
                setType: this.input.set_id,
                clinics: [this.input.clinic],
            }, {}, {
                header: __('История тарифа'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        getSaveServicesMessage() {
            return __('Сохранение анализов...');
        },
        getSaveServicesError() {
            return __('Не удалось сохранить некоторые анализы');
        },
        getServicesBatchRequest() {
            return new BatchRequest('/api/v1/analyses/batch');
        },
        getPricesBatchRequest() {
            return new BatchRequest('/api/v1/analyses/prices/batch');
        },
        getServiceSearchRequest() {
            return new MultiSearchRequest('/api/v1/analyses/multisearch', [
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
        serviceGetClinic(service, clinic) {
            return _.find(service.clinics, c => c.clinic_id === clinic);
        },
        replaceClinic(service, clinic, data) {
            let index = service.clinics.indexOf(clinic);
            service.clinics.splice(index, 1, {
                clinic_id: clinic.clinic_id,
                ...data,
            });
        },
        isUpdatedService(service) {
            return this.wereChanged(service.changed(), ['clinics', 'description', 'laboratory_id', 'laboratory_code', 'disabled']);
        },
        isReenabledService(service) {
            return service.$.disabled && !service.disabled;
        },
        nextNewAnalysis() {
            this.lastNewAnalysisRow = this.$refs.table.scrollToRow((row, index) => {
                return index > this.lastNewAnalysisRow && row.service.isNew();
            });
        },
        nextUpdatedPrice() {
            this.lastUpdatedPriceRow = this.$refs.table.scrollToRow((row, index) => {
                return index > this.lastUpdatedPriceRow && this.isUpdatedPrice(row.price);
            });
        },
        nextUpdatedAnalysis() {
            this.lastUpdatedAnalysesRow = this.$refs.table.scrollToRow((row, index) => {
                return index > this.lastUpdatedAnalysesRow && this.isUpdatedAnalyses(row.service);
            });
        },
        afterReset() {
            this.lastNewAnalysisRow = -1;
            this.lastUpdatedPriceRow = -1;
            this.lastUpdatedAnalysesRow = -1;
        },
    }
};
</script>
