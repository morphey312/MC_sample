<template>
    <div class="separate-form analysis-modal-form" v-loading="loading">
        <el-tabs v-model="activeTab" class="tab-group-grey">
            <el-tab-pane
                :lazy="true"
                :label="__('Специализации врачей')"
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
                        :appointment="appointment"
                        @loaded="refreshed"
                        @selection-changed="addToSelected"
                        @featured-changed="toggleFeatured" />
                </section>
                <section>
                    <template v-if="emptySelected">
                        <b>{{ __('Специализации врачей') }}</b>
                        <empty-section
                            :show-image="false"
                            list-class="text-only">
                            <b>{{ __('Чтобы назначить консультацию врачей, сначала выберите их из таблицы выше') }}</b><br>
                            {{ __('Нажмите "выбрать специализацию" в крайней правой колонке.') }}
                        </empty-section>
                    </template>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Избранные специализации врачей')"
                name="featured">
                <section class="pt-0">
                    <featured-table
                        :featured-list="featuredList"
                        @selection-changed="addToSelected"
                        @featured-changed="toggleFeatured"
                    />
                    <template v-if="emptySelected">
                        <div class="pt-20">
                            <b>{{ __('Специализации врачей') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only">
                                <b>{{ __('Чтобы назначить консультацию врачей, сначала выберите их из таблицы выше') }}</b><br>
                                {{ __('Нажмите "выбрать специализацию" в крайней правой колонке.') }}
                            </empty-section>
                        </div>
                    </template>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Избранные врачи вне клиники')"
                name="outclinic">
                <section class="pt-0">
                    <outclinic-table
                        :specializations="doctorOutclinicList"
                        @selection-changed="addOutclinicToSelected"
                        @specialization-deleted="getDoctorOutclinicSpecializations"/>
                    <template v-if="emptySelected">
                        <div class="pt-20">
                            <b>{{ __('Специализации врачей') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only">
                                <b>{{ __('Чтобы назначить консультацию врачей, сначала выберите их из таблицы выше') }}</b><br>
                                {{ __('Нажмите "выбрать специализацию" в крайней правой колонке.') }}
                            </empty-section>
                        </div>
                    </template>
                </section>
            </el-tab-pane>
        </el-tabs>
        <section v-if="!emptySelected" class="pt-0">
            <b>{{ __('Специализации врачей') }}</b>
            <selected-table
                :rows="selectedRows"
                :readonly="readonly"
                @selection-changed="removeFromSelected" />
        </section>
        <div class="dialog-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="confirm"
                :disabled="emptySelected">
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
import OutclinicForm from './OutclinicForm.vue';
import OutclinicTable from './OutclinicTable.vue';
import EmptySection from '@/components/appointments/modal/form/treatments/analysis/Empty.vue';
import Employee from '@/models/employee';
import DoctorConsultation from '@/models/patient/card/doctor-consultation';
import ManageMixin from '@/mixins/manage';
import SearchMixin from '@/components/doctor/appointment/mixins/search';
import CONSTANTS from '@/constants';
import OutclinicSpecialization from "@/models/employee/outclinic-specialization";

export default {
    mixins: [
        ManageMixin,
        SearchMixin,
    ],
    components: {
        ServiceFilter,
        SearchTable,
        FeaturedTable,
        SelectedTable,
        EmptySection,
        OutclinicTable
    },
    props: {
        appointment: Object,
        readonly: Boolean,
        consultations: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            activeTab: 'ordinary',
            selectedRows: this.getRows(),
            employee: new Employee({id: this.appointment.doctor_id}),
            loading: true,
            featuredList: [],
            doctorOutclinicList: [],
        };
    },
    mounted() {
        this.getFeaturedList();
    },
    methods: {
        getDefaultFilters() {
            return {
                skip_id: this.appointment.specialization_id,
                clinic: this.appointment.clinic_id,
            };
        },
        getRows() {
            return this.consultations.map(row => new DoctorConsultation(row));
        },
        getFeaturedList() {
            this.employee.fetchFeaturedSpecializations().then((response) => {
                this.featuredList = this.getModelsFromRawFeatures(response);
                return this.getDoctorOutclinicSpecializations();
            });
        },
        getModelsFromRawFeatures(featuredList) {
            return featuredList.map(row => {
                return new DoctorConsultation({
                    specialization_id: row.id,
                    specialization_name: row.value,
                });
            })
        },
        assignOutClinic() {
            this.$modalComponent(OutclinicForm, {
                consultations: this.doctorOutclinicList,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.saveOutclinicSpecializations(list);
                },
            }, {
                header: __('Добавить врача вне клиники'),
                width: '350px',
                customClass: 'padding-0',
            });
        },
        getOutclinicList() {
            return this.selectedRows.filter(item => _.isVoid(item.specialization_id));
        },
        addOutclinicList(list) {
            list.forEach((item) => {
                if (_.isFilled(item.id)) {
                    this.addOutclinicToSelected(item);
                }
            });
        },
        toggleDoctorFeatured(featured_id) {
            this.employee.saveFeaturedSpecialization(featured_id);
        },
        toggleFeatured(row) {
            let featuredIndex = this.featuredList.findIndex((item) => {
                return item.specialization_id === row.specialization_id;
            });

            if (featuredIndex === -1) {
                this.featuredList.push(row);
            } else {
                this.featuredList.splice(featuredIndex, 1);
            }

            this.toggleDoctorFeatured(row.specialization_id);
        },
        addToSelected(row) {
            let sameIndex = this.findSelectedRowIndex(row, 'specialization_id');

            if (sameIndex != undefined && sameIndex !== -1) {
                return this.$warning(__('{name} уже присутствует в выбранном списке', {name: row.specialization_name}));
            }
            this.selectedRows.splice(0, 0, row);
        },
        removeFromSelected(index) {
            this.selectedRows.splice(index, 1);
        },
        saveOutclinicSpecializations(list) {
            let specializations = list.filter(item => _.isVoid(item.id))
                .map(item => ({name: item.name}));
            this.employee.saveOutclinicSpecializations(specializations).then((response) => {
                if (response && response.length != 0) {
                    list = list.map((item) => {
                        if (_.isFilled(item.id)) {
                            return item;
                        }
                        let match = response.find(row => item.name == row.name);
                        if (match) {
                            item.id = match.id;
                        }
                        return item;
                    });
                    this.addOutclinicList(list);
                } else {
                    if (list.length != 0) {
                        this.addOutclinicList(list);
                    }
                }
            })
        },
        getDoctorOutclinicSpecializations() {
            this.employee.fetchOutclinicSpecializations().then((response) => {
                this.doctorOutclinicList = response;
                this.loading = false;
            });
        },
        addOutclinicToSelected(row) {
            let sameIndex = this.findSelectedRowIndex(row, 'id');

            if (sameIndex != undefined && sameIndex !== -1) {
                return this.$warning(__('{name} уже присутствует в выбранном списке', {name: row.name}));
            }
            let specialization = this.getOutclinicItem(row);
            this.selectedRows.push(specialization);
        },
        findSelectedRowIndex(row, field = 'specialization_id') {
            return this.selectedRows.findIndex((item) => {
                if(item.specialization_id !== null) {
                    return item.specialization_id == row[field];
                } else {
                    return item.outclinic_specialization_id == row[field] || item.specialization_name == row['name'];
                }
            });
        },
        getOutclinicItem(item) {
            return new DoctorConsultation({
                consultation_record_id: this.consultations,
                specialization_id: item.is_outclinic ? null : item.id,
                specialization_name: item.name,
                comment: item.comment,
                outclinic_specialization_id: item.is_outclinic ? item.id : null,
            });
        },
    },
}
</script>
