<template>
    <div class="separate-form analysis-modal-form" v-loading="loading">
        <el-tabs v-model="activeTab" class="tab-group-grey">
            <el-tab-pane
                :lazy="true"
                :label="__('Доступные услуги')"
                name="ordinary" >
                <section class="grey pt-10 pb-20">
                    <service-filter
                        ref="filter"
                        :initial-state="filters"
                        @changed="changeFilters"
                        @cleared="clearFilters" />
                </section>
                <section class="pt-0 pb-0">
                    <search-table
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
                        <b>{{ __('Выбранные услуги') }}</b>
                        <empty-section
                            :show-image="false"
                            list-class="text-only shrinked">
                            <b>{{ __('Чтобы назначить аппаратную диагностику, разверните списки услуг и выберите их из таблицы выше.') }}</b><br>
                            {{ __('Нажмите "выбрать услугу" в крайней правой колонке.') }}
                        </empty-section>
                    </template>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Избранные услуги')"
                name="featured">
                <section>
                    <featured-table
                        :featured-list="featuredList"
                        @selection-changed="addToSelected"
                        @featured-changed="toggleFeatured"
                    />
                    <template v-if="emptySelected">
                        <div class="pt-20">
                            <b>{{ __('Выбранные услуги') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only shrinked">
                                <b>{{ __('Чтобы назначить аппаратную диагностику, разверните списки услуг и выберите их из таблицы выше.') }}</b><br>
                                {{ __('Нажмите "выбрать услугу" в крайней правой колонке.') }}
                            </empty-section>
                        </div>
                    </template>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Диагностика вне клиники')"
                name="out-clinic-diagnostics">
                <section>
                    <outclinic-diagnostic-table
                        :rows="doctorOutclinicList"
                        @selection-changed="addOutclinicToSelected"
                        @specialization-deleted="getDoctorOutclinicDiagnostics"/>
                    <template v-if="outclinicList.length === 0">
                        <div class="pt-20">
                            <b>{{ __('Диагностики') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only">
                                <b>{{ __('Чтобы назначить диагносику, сначала выберите их из таблицы выше') }}</b><br>
                                {{ __('Нажмите "выбрать диагностику" в крайней правой колонке.') }}
                            </empty-section>
                        </div>
                    </template>
                    <template v-else>
                        <div class="pt-20">
                            <outclinic-selected-diagnostic-table
                                :rows="outclinicList"
                                @selection-changed="removeOutClinicService" />
                        </div>
                    </template>
                </section>
            </el-tab-pane>
        </el-tabs>
        <section v-if="!emptySelected" class="pt-0">
            <b>{{ __('Выбранные услуги') }}</b>
            <selected-table
                :model="model"
                :rows="selectedRows"
                :readonly="readonly"
                :insurance-policy="insurancePolicy"
                :filters="filters"
                @cost-changed="calcModelPrice"
                @selection-changed="removeService" />
        </section>
        <div class="dialog-footer text-right">
            <p class="inline-block input-label">{{ __('Итого:') }} {{ totalCost }}</p>
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="confirm"
                :disabled="emptySelected && outclinicList.length == 0">
                {{ __('Назначить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import ServiceFilter from './Filter.vue';
import SearchTable from './SearchTable.vue';
import FeaturedTable from './FeaturedTable.vue';
import SelectedTable from './SelectedTable.vue';
import EmptySection from '@/components/appointments/modal/form/treatments/analysis/Empty.vue';
import Employee from '@/models/employee';
import AssignedService from '@/models/patient/assigned-service';
import SearchMixin from '@/components/doctor/appointment/mixins/search';
import ServiceSearchMixin from '@/components/doctor/appointment/assignment/mixins/service-search';
import CONSTANTS from '@/constants';
import ManageMixin from '@/mixins/manage';
import DiagnosticForm from './out-clinic/DiagnosticForm.vue';
import OutclinicDiagnosticTable from './out-clinic/List.vue';
import OutclinicSelectedDiagnosticTable from './out-clinic/SelectedList.vue';
import OutclinicService from '@/models/patient/outclinic-service';

export default {
    mixins: [
        ManageMixin,
        SearchMixin,
        ServiceSearchMixin,
    ],
    components: {
        ServiceFilter,
        SearchTable,
        FeaturedTable,
        SelectedTable,
        EmptySection,
        OutclinicDiagnosticTable,
        OutclinicSelectedDiagnosticTable,
    },
    props: {
        diagnostics: Array,
        outclinicDiagnostics: Array,
    },
    data() {
        return {
            selectedRows: this.getDiagnostics(),
            doctorOutclinicList: [],
            employee: new Employee({id: this.appointment.doctor_id}),
            outclinicList: this.getOutclinicList(),
            costType: true
        };
    },
    beforeMount() {
        this.model = this.appointment
        this.getDoctorOutclinicDiagnostics()
    },
    methods: {
        getOutclinicList() {
            return this.outclinicDiagnostics.map(service => new OutclinicService(service._attributes));
        },
        getDoctorOutclinicDiagnostics() {
            this.employee.fetchOutclinicDiagnostics().then((response) => {
                this.doctorOutclinicList = response;
                this.loading = false;
            });
        },
        getFilterUid() {
            return false;
        },
        getDiagnostics() {
            return this.diagnostics.map(service => new AssignedService(service._attributes));
        },
        getFeaturedFilter() {
            return this.getDefaultFilters();
        },
        getDefaultFilters() {
            return this.getFilterValues({
                payment_destination_mark: CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_DIAGNOSTICS,
            });
        },
        removeService({row, index}) {
            this.selectedRows.splice(index, 1);
        },
        removeOutClinicService(index) {
            this.outclinicList.splice(index, 1);
        },
        assignOutClinic() {
            this.$modalComponent(DiagnosticForm, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.saveOutclinicDiagnostics(list)
                        .then(() => {
                            list.forEach((item) => {
                                this.addOutclinicToSelected(item);
                            });
                        });
                },
            }, {
                header: __('Добавить диагностику'),
                width: '350px',
                customClass: 'padding-0',
            });
        },
        confirm() {
            this.$emit('selected', {
                diagnostics: this.selectedRows,
                outclinicDiagnostics: this.outclinicList,
            });
        },
        addOutclinicToSelected(row) {
            let index = this.outclinicList.findIndex((item) => {
                return item.name === row.name;
            });

            if (index !== -1) {
                return this.$warning(__('{name} уже присутствует в выбранном списке', {name: row.name}));
            }

            row.id = null;
            this.outclinicList.push(row);
        },
        saveOutclinicDiagnostics(list) {
            let newDiagnostics = list.filter((item) => {
                return !this.doctorOutclinicList.some((featured) => featured.name === item.name);
            }).map(item => ({name: item.name}));

            if (newDiagnostics.length === 0) {
                return Promise.resolve();
            }

            this.loading = true;
            return this.employee.saveOutclinicDiagnostics(newDiagnostics).then((response) => {
                this.doctorOutclinicList = [
                    ...this.doctorOutclinicList,
                    ...response,
                ];
            }).finally(() => {
                this.loading = false;
            });
        },
    },
}
</script>
