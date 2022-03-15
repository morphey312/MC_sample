<template>
    <div class="separate-form analysis-modal-form" v-loading="loading">
        <el-tabs v-model="activeTab" class="tab-group-grey">
            <el-tab-pane
                :lazy="true"
                :label="__('Доступные услуги')"
                name="ordinary" >
                <section class="pt-0 pb-0">
                    <available-services
                        :specialization="specialization"
                        :skip-id="serviceIds"
                        :appointment="appointment"
                        :featured-list="featuredList"
                        @added="refreshServices"
                        @featured-changed="toggleFeatured" />
                </section>
                <section>
                    <template v-if="emptySelected">
                        <b>{{ __('Выбранные услуги') }}</b>
                        <empty-section
                            :show-image="false"
                            list-class="text-only">
                            <b>{{ __('Чтобы заполнить протокол исследований, добавьте услугу из таблицы выше.') }}</b><br>
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
                        :appointment="appointment"
                        @added="refreshServices"
                        @featured-changed="toggleFeatured" />
                    <template v-if="emptySelected">
                        <div class="pt-20">
                            <b>{{ __('Выбранные услуги') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only">
                                <b>{{ __('Чтобы заполнить протокол исследований, добавьте услугу из таблицы выше.') }}</b><br>
                                {{ __('Чтобы добавить нажмите "выбрать услугу" в крайней правой колонке.') }}
                            </empty-section>
                        </div>
                    </template>
                </section>
            </el-tab-pane>
        </el-tabs>
        <section v-if="!emptySelected" class="pt-0">
            <b>{{ __('Выбранные услуги') }}</b>
            <selected-services
                :protocols="filledProtocols"
                :specialization="specialization"
                :take-id="serviceIds"
                :appointment="appointment"
                @select="select"
                @edit="edit"
                @attach="attach"
                @removed="refreshServices" />
        </section>
        <div class="dialog-footer text-right">
            <el-button
                type="default"
                @click="cancel">
                {{ __('Выход') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import AvailableServices from './Available.vue';
import SelectedServices from './Selected.vue';
import Blank from './Blank.vue';
import FileAttach from './FileAttach.vue';
import FeaturedTable from './FeaturedTable.vue';
import Employee from '@/models/employee';
import ServiceSearchMixin from '@/components/doctor/appointment/assignment/mixins/service-search';
import CastServiceMixin from './mixin/cast-service';
import EmptySection from '@/components/appointments/modal/form/treatments/analysis/Empty.vue';
import EditProtocolRecord from "./EditProtocolRecord";

export default {
    mixins: [
        ServiceSearchMixin,
        CastServiceMixin,
    ],
    components: {
        SelectedServices,
        AvailableServices,
        FeaturedTable,
        EmptySection,
    },
    props: {
        appointment: Object,
        card: Object,
        specialization: [Number, String],
        protocols: Array,
        modalComponent: Object,
    },
    data() {
        return {
            serviceIds: this.getAppointmentServiceIds(),
            filledProtocols: [...this.protocols],
            employee: new Employee({id: this.appointment.doctor_id}),
        };
    },
    computed: {
        emptySelected() {
            return this.serviceIds.length == 0;
        },
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        getAppointmentServiceIds() {
            return _.toArray(this.appointment.services).map((service) => service.service_id);
        },
        refreshServices() {
            this.serviceIds = this.getAppointmentServiceIds();
        },
        getFeaturedFilter() {
            return this.getDefaultFilters();
        },
        getDefaultFilters() {
            return this.getFilterValues({
                specialization: this.specialization,
                has_protocol: 1,
                skipId: this.serviceIds,
            });
        },
        getFeaturedServices() {
            let scopes = ['protocol_templates'];
            this.employee.fetchFeaturedServices(this.getFeaturedFilter(), scopes).then((response) => {
                this.featuredList = this.castServiceRows(response);
                this.loading = false;
            });
        },
        toggleFeatured(service) {
            let featuredIndex = this.featuredList.findIndex((item) => {
                return item.service.id === service.service.id;
            });

            if (featuredIndex === -1) {
                this.featuredList.push(service);
            } else {
                this.featuredList.splice(featuredIndex, 1);
            }

            this.toggleDoctorFeatured(service.service.id);
        },
        select(protocol) {
            this.modalComponent.pushComponent(Blank, {
                appointment: this.appointment,
                card: this.card,
                protocol,
            }, {
                cancel: (dialog) => {
                    this.cancel();
                },
                saved: (dialog, model) => {
                    this.$emit('saved', model);
                    dialog.popComponent();
                    this.filledProtocols = [...this.filledProtocols, model];
                },
            }, {
                backText: __('К списку бланков'),
            });
        },
        edit(protocol) {
            this.modalComponent.pushComponent(EditProtocolRecord, {
                appointment: this.appointment,
                card: this.card,
                protocol,
            }, {
                cancel: (dialog) => {
                    this.cancel();
                },
                saved: (dialog, model) => {
                    this.$emit('saved', model);
                    dialog.popComponent();
                },
            }, {
                backText: __('Отменить редактирование'),
            });
        },
        attach(protocol) {
            this.modalComponent.pushComponent(FileAttach, {
                appointment: this.appointment,
                card: this.card,
                protocol,
            }, {
                cancel: (dialog) => {
                    this.cancel();
                },
                saved: (dialog, model) => {
                    this.$emit('saved', model);
                    dialog.popComponent();
                    this.filledProtocols = [...this.filledProtocols, model];
                },
            }, {
                backText: __('К списку бланков'),
            });
        },
    },
};
</script>
