<template>
    <div class="separate-form analysis-modal-form" v-loading="loading">
        <el-tabs v-model="activeTab" class="tab-group-grey">
            <el-tab-pane
                :lazy="true"
                :label="__('Все анализы')"
                name="ordinary" >
                <section class="grey pt-10 pb-20">
                    <analysis-filter
                        ref="filter"
                        :initial-state="filters"
                        @changed="changeFilters"
                        @cleared="clearFilters" />
                </section>
                <empty-section
                    v-if="hasNoTables"
                    wrapper-class="wrapper-large">
                    {{ __('Выберите код лаборатории, клиники или название анализа') }}
                </empty-section>
                <template v-else>
                    <section class="grey pt-0 pb-0">
                        <search-table
                            v-if="!emptyFilters"
                            ref="table"
                            :filters="filters"
                            :featured-list="featuredList"
                            :insurance-policy="insurancePolicy"
                            @loaded="refreshed"
                            @selection-changed="addToSelected"
                            @featured-changed="toggleFeatured"
                        />
                    </section>
                    <section>
                        <template v-if="emptySelected">
                            <b>{{ __('Выбранные анализы') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only">
                                <b>{{ __('Добавьте анализы из таблицы выше.') }}</b><br>
                                {{ __('Чтобы добавить нажмите "добавить анализ" в крайней правой колонке.') }}
                            </empty-section>
                        </template>
                    </section>
                </template>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Избранные анализы')"
                name="featured">
                <section class="pt-0">
                    <featured-table
                        :filters="filters"
                        :featured-list="featuredList"
                        @selection-changed="addToSelected"
                        @featured-changed="toggleFeatured"
                    />
                    <template v-if="emptySelected">
                        <div class="pt-20">
                            <b>{{ __('Выбранные анализы') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only">
                                <b>{{ __('Добавьте анализы из таблицы выше.') }}</b><br>
                                {{ __('Чтобы добавить нажмите "добавить анализ" в крайней правой колонке.') }}
                            </empty-section>
                        </div>
                    </template>
                </section>
            </el-tab-pane>
        </el-tabs>
        <section v-if="!emptySelected" class="pt-0">
            <b>{{ __('Выбранные анализы, всего:') }} {{ totalSelected }}</b>
            <selected-table
                ref="selectedTable"
                :model="model"
                :rows="selectedRows"
                :readonly="readonly"
                :insurance-policy="insurancePolicy"
                :appointment-data="appointmentData"
                :filters="filters"
                @selection-changed="removeFromSelected"
                @cost-changed="calcModelPrice" />
        </section>
        <div class="dialog-footer text-right">
            <p class="inline-block input-label">{{ __('Итоговая сумма по всем анализам:') }} {{ totalCost }}</p>
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="confirm"
                :disabled="emptySelected">
                {{ __('Выбрать') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import AnalysisFilter from './Filter.vue';
import SearchTable from './SearchTable.vue';
import FeaturedTable from './FeaturedTable.vue';
import SelectedTable from './SelectedTable.vue';
import EmptySection from '@/components/appointments/modal/form/treatments/analysis/Empty.vue';
import Employee from '@/models/employee';
import PriceCalculationMixin from '@/mixins/appointment/analysis/price-calculation';
import ManageMixin from '@/mixins/manage';
import CreateResultMixin from '@/mixins/appointment/analysis/create-result';
import SearchMixin from '@/components/doctor/appointment/mixins/search';
import Result from '@/models/analysis/result';
import AnalysisRepository from '@/repositories/analysis';
import CONSTANTS from '@/constants';

export default {
    mixins:[
        PriceCalculationMixin,
        ManageMixin,
        CreateResultMixin,
        SearchMixin,
    ],
    components: {
        AnalysisFilter,
        SearchTable,
        FeaturedTable,
        SelectedTable,
        EmptySection,
    },
    props: {
        appointment: Object,
        analyses: Array,
        readonly: Boolean,
        insurancePolicy: Object,
    },
    watch: {
        analyses(list) {
            list.forEach(row => {
                let index = this.selectedRows.findIndex(item => item.id == row.id);
                if (index > -1) {
                    let analysis = new Result({...row.attributes});
                    analysis.set('prices',  this.selectedRows[index].prices);
                    this.$set(this.selectedRows, index, analysis);
                }
            });
            this.$refs.selectedTable.refresh();
        }
    },
    data() {
        return {
            activeTab: 'ordinary',
            loading: true,
            selectedRows: [],
            appointmentData: {},
            model: {},
            doctor:  this.appointment.doctor,
            employee: new Employee({id: this.appointment.doctor_id}),
            featuredList: [],
        };
    },
    computed: {
        emptyFilters() {
            return _.isVoid(this.filters.clinicCode) &&
                   _.isVoid(this.filters.laboratoryCode) &&
                   _.isVoid(this.filters.name) &&
                   _.isVoid(this.filters.description);
        },
        hasNoTables() {
            return this.emptyFilters && this.emptySelected;
        },
    },
    beforeMount() {
        this.model = this.appointment
        this.appointmentData = this.getAppointmentData();
    },
    mounted() {
        this.getAnalysesPrices().then((results) => {
            this.getFeaturedAnalyses();
        });
    },
    methods: {
        getAppointmentData() {
            return {
                hasPrice: {
                    clinic: this.appointment.clinic_id,
                    from: this.appointment.date,
                    to: this.appointment.date,
                    set: [CONSTANTS.PRICE.SET_TYPE.BASE],
                },
                disabled: false,
            };
        },
        getFilterUid() {
            return false;
        },
        toggleDoctorFeatured(analysis_id) {
            this.employee.saveFeaturedAnalysis(analysis_id);
        },
        toggleFeatured(result) {
            let featuredIndex = this.featuredList.findIndex((item) => {
                return item.analysis_id === result.analysis_id;
            });

            if (featuredIndex === -1) {
                this.featuredList.push(result);
            } else {
                this.featuredList.splice(featuredIndex, 1);
            }

            this.toggleDoctorFeatured(result.analysis_id);
        },
        getAnalysesPrices() {
            if (this.analyses.length === 0) {
                return Promise.resolve();
            }
            let repo = new AnalysisRepository();
            let filters = {
                ...this.appointmentData,
                id: this.analyses.map(row => row.analysis_id),
            };
            let params = {};
            if (this.insurancePolicy) {
                params.withInsurer = this.insurancePolicy.insurance_company_id;
            }
            return repo.fetchListForAppointment(filters, params).then((response) => {
                this.selectedRows = response.map((row) => {
                    let analysis = this.analyses.find(item => item.analysis_id == row.analysis_id);
                    let resultModel = new Result({...analysis.attributes});
                    resultModel.set('prices', resultModel.getActualPrices(row, filters))
                    return resultModel;
                });
                return Promise.resolve();
            });
        },
        getFeaturedAnalyses() {
            let params = { sort: [{field: 'laboratory_clinic_priority', direction : 'asc'}]};
            if (this.insurancePolicy) {
                params.withInsurer = this.insurancePolicy.insurance_company_id;
            }
            this.employee.fetchFeaturedAnalyses(this.filters, [], params).then((response) => {
                this.featuredList = response.map(row => this.createResultModel(row, this.filters));
                this.loading = false;
            });
        },
        changeFilters(filters) {
            this.filters = filters;
        },
        getDefaultFilters() {
            return {
                hasPrice: {
                    clinic: this.appointment.clinic_id,
                    from: this.appointment.date,
                    to: this.appointment.date,
                    set: [CONSTANTS.PRICE.SET_TYPE.BASE],
                },
                disabled: false,
            };
        },
        addToSelected({row, index}) {
            let sameIndex;
            if (this.totalSelected != 0) {
                sameIndex = this.selectedRows.findIndex((item) => {
                    return item.analysis_id == row.analysis_id &&
                           (_.isVoid(item.status) || item.status === CONSTANTS.ANALYSIS_RESULT.STATUSES.ASSIGNED);
                });
            }

            if (sameIndex != undefined && sameIndex !== -1) {
                let warning = __('{name} уже присутствует в выбранном списке. Увеличить количество?', {name: row.analysis.name});
                return this.$confirm(warning, () => {
                    let analysis = this.selectedRows[sameIndex];
                    analysis.quantity++;
                    this.setAnalysisDiscount(analysis);
                    this.calcModelPrice(analysis);
                });
            }

            this.setAnalysisDiscount(row);
            this.calcModelPrice(row);
            this.selectedRows.splice(0, 0, row);
        },
        removeFromSelected({row, index}) {
            row.quantity = 1;
            row.cost = row.analysis.price;
            row.discount = null;
            let model = this.selectedRows[index];

            if (_.isFilled(model)) {
                if (model.isNew()) {
                    this.selectedRows.splice(index, 1);
                } else {
                    model.delete().then((response) => {
                        this.selectedRows.splice(index, 1);
                        this.$emit('deleted', index);
                        return this.$info(__('Анализ успешно удален'));
                    }).catch((error) => {
                        return this.$error(__('Не удалось удалить назначеный анализ'));
                    });
                }
            }
        },
    },
}

</script>
