<template>
    <div class="separate-form analysis-modal-form" v-loading="loading">
        <el-tabs v-model="activeTab" class="tab-group-grey">
            <el-tab-pane
                :lazy="true"
                :label="__('Все услуги')"
                name="ordinary" >
                <section class="grey pt-0 pb-0">
                    <search-table
                        ref="table"
                        :featured-list="featuredList"
                        :appointment-data="appointmentData"
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
                            list-class="text-only">
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
                        :appointment-data="appointmentData"
                        @selection-changed="addToSelected"
                        @featured-changed="toggleFeatured"
                    />
                    <template v-if="emptySelected">
                        <div class="pt-20">
                            <b>{{ __('Выбранные услуги') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only">
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
                :model="model"
                :rows="selectedRows"
                :readonly="readonly"
                :insurance-policy="insurancePolicy"
                :appointment-data="appointmentData"
                :filters="appointmentData"
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
                {{ __('Выбрать') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import SearchTable from './SearchTable.vue';
import FeaturedTable from './FeaturedTable.vue';
import SelectedTable from './SelectedTable.vue';
import EmptySection from '@/components/appointments/modal/form/treatments/analysis/Empty.vue';
import Employee from '@/models/employee';
import SearchMixin from '@/components/doctor/appointment/mixins/search';
import Service from '@/models/appointment/service';
import CONSTANTS from '@/constants';
import ManageMixin from '@/mixins/manage';
import TreatmentServiceMixin from './mixin/treatment-service';
import PriceCalculationMixin from '@/mixins/appointment/analysis/price-calculation';

export default {
    mixins: [
        PriceCalculationMixin,
        ManageMixin,
        SearchMixin,
        TreatmentServiceMixin,
    ],
    components: {
        SearchTable,
        FeaturedTable,
        SelectedTable,
        EmptySection,
    },
    props: {
        appointment: Object,
        insurancePolicy: Object,
        readonly: Boolean,
        doctorSpecializations: Array,
    },
    data() {
        return {
            activeTab: 'ordinary',
            selectedRows: [],
            model: {},
            employee: new Employee({id: this.appointment.doctor_id}),
            loading: true,
            appointmentData: {},
            featuredList: [],
        };
    },
    beforeMount() {
        this.model = this.appointment
        this.appointmentData = this.getAppointmentData();
    },
    mounted() {
        this.getSelectedRows();
        this.getFeaturedServices();
        this.$eventHub.$on('updated:Appointment', () => {
            this.loading = false;
        });
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
        getSelectedRows() {
            if (this.appointment.services.length === 0) {
                return;
            }

            this.appointment.services.forEach((service) => {
                if (service.is_base == true) {
                    this.selectedRows.push(service);
                }
            });
        },
        getFeaturedServices() {
            let filters = this.appointmentData;
            let params = {};
            if (this.insurancePolicy) {
                params.withInsurer = this.insurancePolicy.insurance_company_id;
            }
            this.employee.fetchFeaturedServices(filters, [], params).then((response) => {
                this.featuredList = response.map(row => this.createServiceModel(row));
                this.loading = false;
            });
        },
        createServiceModel(item) {
            let service = new Service();
            service.castServiceDataToEntity(item, this.appointmentData);
            return service;
        },
        getAppointmentData() {
            return {
                specialization: this.doctorSpecializations,
                base: true,
                disabled: false,
                hasPrice: {
                    clinic: this.appointment.clinic_id,
                    from: this.appointment.date,
                    to: this.appointment.date,
                    set: [CONSTANTS.PRICE.SET_TYPE.BASE],
                },
            };
        },
        toggleDoctorFeatured(service_id) {
            this.employee.saveFeaturedService(service_id);
        },
        toggleFeatured(service) {
            let featuredIndex = this.featuredList.findIndex((item) => {
                return item.service_id === service.service_id;
            });

            if (featuredIndex === -1) {
                this.featuredList.push(service);
            } else {
                this.featuredList.splice(featuredIndex, 1);
            }

            this.toggleDoctorFeatured(service.service_id);
        },
        addToSelected({row, index}) {
            let sameIndex;

            if (this.selectedRows.length != 0) {
                sameIndex = this.selectedRows.findIndex((item) => {
                    return item.service_id == row.service_id;
                });
            }

            if (sameIndex != undefined && sameIndex !== -1) {
                let warning = __('{name} уже присутствует в выбранном списке. Увеличить количество?', {name: row.name});
                return this.$confirm(warning, () => {
                    let service = this.selectedRows[sameIndex];
                    service.quantity++;
                    this.calcModelPrice(service);
                });
            }

            this.calcModelPrice(row);
            let discount = this.calcModelDiscount(row, 'service');
            if (discount > row.discount) {
                row.discount = discount;
            }
            this.selectedRows.splice(0, 0, row);
        },
        removeFromSelected({row, index}) {
            this.selectedRows.splice(index, 1);
            if (!(row instanceof Service)) {
                this.loading = true;
                this.$emit('deleted', row);
            }
        },
        calcModelPrice(service) {
            this.$nextTick(() => {
                if (service.price > 0) {
                    service.cost = (this.getPrice(service) * service.quantity).toFixed(2);
                }
            });
        },
        getPrice(service) {
            let price;

            if (_.isFilled(service.discount) && service.price > 0) {
                price = service.price - (service.price / 100 * service.discount);
            } else{
                price = service.price;
            }
            return price;
        },
        setServiceCost(service, price) {
            service.cost = (price * service.quantity).toFixed(2);
        },
    }
}
</script>
