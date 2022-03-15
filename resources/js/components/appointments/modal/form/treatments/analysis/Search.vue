<template>
    <div class="separate-form analysis-modal-form">
        <section class="grey pt-10 pb-10">
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
            <section class="grey-cap pt-10">
                <analysis-table-top
                    v-if="!emptyFilters"
                    ref="table"
                    :filters="filters"
                    :insurance-policy="insurancePolicy"
                    @loaded="refreshed"
                    @header-filter-updated="syncFilters"
                    @selection-changed="addToSelected" />
            </section>
            <section class="pt-0">
                <b>{{ __('Выбранные анализы, всего:') }} {{ totalSelected }}</b>
                <analysis-table-bottom
                    v-if="!emptySelected"
                    :rows="selectedRows"
                    :checked="true"
                    @selection-changed="removeFromSelected"
                    @cost-changed="calcModelPrice" />
                <template v-else>
                    <empty-section 
                        :show-image="false"
                        list-class="text-only">
                        <b>{{ __('Добавьте анализы из таблицы выше.') }}</b><br>
                        {{ __('Чтобы добавить нажмите "добавить анализ" в крайней правой колонке.') }}
                    </empty-section>
                </template>
            </section>
        </template>
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
import AnalysisTableTop from './TableTop.vue';
import AnalysisTableBottom from './TableBottom.vue';
import EmptySection from './Empty.vue';
import AnalysisRepository from '@/repositories/analysis';
import Result from '@/models/analysis/result';
import PriceCalculationMixin from '@/mixins/appointment/analysis/price-calculation';
import ManageMixin from '@/mixins/manage';
import CONSTANTS from '@/constants';

export default {
    mixins:[
        PriceCalculationMixin,
        ManageMixin,
    ],
    components: {
        AnalysisFilter,
        AnalysisTableTop,
        AnalysisTableBottom,
        EmptySection,
    },
    props: {
        appointmentData: {
            type: Object,
            default: () => ({})
        },
        analyses: Array,
        model: Object,
        insurancePolicy: Object,
    },
    data() {
        return {
            repository: new AnalysisRepository,
            selectedRows: this.getSelected(),
        };
    },
    computed: {
        emptyFilters() {
            return _.isVoid(this.filters.clinicCode) &&
                   _.isVoid(this.filters.laboratoryCode) &&
                   _.isVoid(this.filters.description) &&
                   _.isVoid(this.filters.name);
        },
        emptySelected() {
            return this.selectedRows.length == 0;
        },
        totalCost() {
            let total = 0;

            if (this.selectedRows.length !== 0){
                this.selectedRows.forEach(row => total += Number(row.cost));
            }
            return total.toFixed(2);
        },
        hasNoTables() {
            return this.emptyFilters && this.emptySelected;
        },
        totalSelected() {
            let total = 0;
            this.selectedRows.forEach(row => total += Number(row.quantity));
            return total;
        },
    },
    methods: {
        getSelected() {
            return this.analyses.map(model => {
                if (model instanceof Result) {
                    return new Result(model.attributes);
                } else {
                    return new Result(model);
                }
            });
        },
        changeFilters(filters) {
            this.filters = filters;
        },
        getDefaultFilters() {
            return {
                hasPrice: this.appointmentData.hasPrice,
                disabled: false,
            };
        },
        cancel() {
            this.$emit('cancel');
        },
        confirm() {
            this.$emit('selected', this.selectedRows);
        },
        addToSelected({row, index}) {
            let sameAnalysisIndex;

            if (this.selectedRows.length != 0) {
                sameAnalysisIndex = this.selectedRows.findIndex((item) => {
                    return item.analysis_id == row.analysis_id && 
                           (_.isVoid(item.status) || item.status === CONSTANTS.ANALYSIS_RESULT.STATUSES.ASSIGNED);
                });
            }

            if (sameAnalysisIndex != undefined && sameAnalysisIndex !== -1) {
                let warning = __('{name} уже присутствует в выбранном списке. Увеличить количество?', {name: row.analysis.name});
                return this.$confirm(warning, () => {
                    let analysis = this.selectedRows[sameAnalysisIndex];
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
            this.selectedRows.splice(index, 1);
        },
    },
}

</script>
