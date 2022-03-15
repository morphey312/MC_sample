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
                        :is-base="isBase"
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
                            <b>{{ __('Добавьте услуги из таблицы выше.') }}</b><br>
                            {{ __('Чтобы добавить нажмите "выбрать услугу" в крайней правой колонке.') }}
                        </empty-section>
                    </template>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Избранные услуги')"
                name="featured">
                <section class="pt-0">
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
                                <b>{{ __('Добавьте услуги из таблицы выше.') }}</b><br>
                                {{ __('Чтобы добавить нажмите "выбрать услугу" в крайней правой колонке.') }}
                            </empty-section>
                        </div>
                    </template>
                </section>
            </el-tab-pane>
        </el-tabs>
        <section v-if="!emptySelected" class="pt-0">
            <b>{{ __('Выбранные услуги') }}</b>
            <selected-table
                :rows="selectedRows"
                :readonly="readonly"
                :insurance-policy="insurancePolicy"
                :filters="filters"
                @selection-changed="removeFromSelected"
                @cost-changed="calcModelPrice" />
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
import EmptySection from '@/components/appointments/modal/form/treatments/analysis/Empty.vue';
import Employee from '@/models/employee';
import AssignedService from '@/models/patient/assigned-service';
import ServiceSearchMixin from '@/components/doctor/appointment/assignment/mixins/service-search';
import ManageMixin from '@/mixins/manage';
import SearchMixin from '@/components/doctor/appointment/mixins/search';
import CONSTANTS from '@/constants';

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
    },
    props: {
        surgeryServices: Array,
        isBase: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            costType: true,
            selectedRows: this.getServices(),
            employee: new Employee({id: this.appointment.doctor_id}),
            model: {},
            appointmentData: {},
        };
    },
    beforeMount() {
        this.model = this.appointment
        this.appointmentData = this.getAppointmentData();
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
        getServices() {
            return this.surgeryServices.map(service => new AssignedService(service.attributes));
        },
        getFeaturedFilter() {
            return this.getDefaultFilters();
        },
        getDefaultFilters() {
            return this.getFilterValues({
                specialization_group: CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY,
                base: this.isBase,
            });
        },
        removeFromSelected({row, index}) {
            let model = this.selectedRows[index];

            if (model !== undefined && !model.isNew()) {
                model.delete().then((response) => {
                    this.selectedRows.splice(index, 1);
                    return this.$info(this.isBase ? __('Операция успешно удалена') : __('Услуга успешно удалена'));
                }).catch((error) => {
                    return this.$error(this.isBase ? __('Не удалось удалить назначенную операцию') : __('Не удалось удалить назначенную услугу'));
                });
            } else {
                this.selectedRows.splice(index, 1);
            }
        },
    },
}
</script>
