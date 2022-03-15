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
                        :appointment-data="appointmentData"
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
                :model="model"
                :rows="selectedRows"
                :readonly="readonly"
                :insurance-policy="insurancePolicy"
                :appointment-data="appointmentData"
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
                @click="save"
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
import SaveForm from './SaveForm.vue';
import EmptySection from '@/components/appointments/modal/form/treatments/analysis/Empty.vue';
import Employee from '@/models/employee';
import PriceCalculationMixin from '@/mixins/appointment/analysis/price-calculation';
import ManageMixin from '@/mixins/manage';
import CreateResultMixin from '@/mixins/appointment/analysis/create-result';
import SearchMixin from '@/components/doctor/appointment/mixins/search';
import Result from '@/models/analysis/result';
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
        insurancePolicy: Object,
        readonly: Boolean,
    },
    data() {
        return {
            activeTab: 'ordinary',
            loading: true,
            selectedRows: this.getAnalyses(),
            doctor: this.appointment.doctor,
            employee: new Employee({id: this.appointment.doctor_id}),
            appointmentData: {},
            model: {},
            featuredList: [],
            isEmployee: (this.appointment.doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE),
        };
    },
    computed: {
        emptyFilters() {
            return _.isVoid(this.filters.clinicCode) &&
                   _.isVoid(this.filters.laboratoryCode) &&
                   _.isVoid(this.filters.description) &&
                   _.isVoid(this.filters.name);
        },
        hasNoTables() {
            return this.emptyFilters && this.emptySelected;
        },
    },
    beforeMount() {
        this.model = this.appointment;
        this.appointmentData = this.getAppointmentData();
    },
    mounted() {
        this.$eventHub.$on('updated:Appointment', () => {
            this.loading = false;
        });
        this.getFeaturedAnalyses();
    },
    beforeDestroy() {
        this.$eventHub.$off('updated:Appointment', () => {
            this.loading = false;
        });
    },
    methods: {
        getFilterUid() {
            return false;
        },
        getAnalyses() {
            return this.appointment.analyses_results.map((item) => {
                if (item instanceof Result) {
                    return new Result(item._attributes);
                }
                return new Result(item);
            });
        },
        toggleDoctorFeatured(analysis_id) {
            if (this.isEmployee) {
                this.employee.saveFeaturedAnalysis(analysis_id, this.appointmentData);
            }
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
        getFeaturedAnalyses() {
            let filters = this.appointmentData;
            let params = { sort: [{field: 'laboratory_clinic_priority', direction : 'asc'}]};
            if (this.insurancePolicy) {
                params.withInsurer = this.insurancePolicy.insurance_company_id;
            }

            if (!this.isEmployee) {
                this.loading = false;
                return;
            }

            this.employee.fetchFeaturedAnalyses(filters, [], params).then((response) => {
                this.featuredList = response.map(row => this.createResultModel(row, filters));
                this.loading = false;
            });
        },
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
        changeFilters(filters) {
            this.filters = filters;
        },
        getDefaultFilters() {
            return this.getAppointmentData();
        },
        addToSelected(row) {
            let sameIndex;
            if (this.totalSelected != 0) {
                sameIndex = this.selectedRows.findIndex((item) => {
                    return item.analysis_id == row.analysis_id;
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
            if (this.isEmployee) {
                row.assigner_id = this.employee.id;
            } else {
                row.assigner_id = this.$store.state.user.employee_id;
            }
            this.selectedRows.splice(0, 0, row);
        },
        removeFromSelected({row, index}) {
            row.quantity = 1;
            row.cost = row.analysis.price;
            row.discount = null;
            let model = this.selectedRows[index];
            if (!model.isNew()) {
                this.loading = true;
                this.$emit('deleted', model);
            }
            this.selectedRows.splice(index, 1);
        },
        checkAllAnalysisDatePass() {
            return this.selectedRows.every((item) => item.date_pass !== null);
        },
        save() {
            if (!this.checkAllAnalysisDatePass()) {
                this.$modalComponent(SaveForm, {
                    maxDate: this.appointment.date,
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    save: (dialog, date_pass) => {
                        dialog.close();
                        this.selectedRows.forEach((row) => {
                            row.date_pass = date_pass;
                        });
                        this.confirm();
                    },
                }, {
                  header: __('Поставить дату сдачи всем анализам'),
                  width: '450px',
                });
            } else {
                this.confirm();
            }
        },
    },
}

</script>
