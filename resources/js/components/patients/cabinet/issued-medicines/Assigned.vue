<template>
    <div class="sections-wrapper" v-loading="saving">
        <el-tabs v-model="activeTab" class="tab-group-grey">
            <el-tab-pane
                :lazy="true"
                :label="__('Платные препараты')"
                name="paylist" >
                <div v-loading="!onPaymentLoaded">
                    <div v-show="hasPayList">
                        <section class="grey-cap pr-20 pl-20 pt-0 pb-10">
                            <pay-list
                                v-if="onPaymentLoaded"
                                ref="payList"
                                :patient="patient"
                                :clinics="clinics"
                                :on-payment-list="onPaymentList"
                                @loaded="payListLoaded"
                                @selection-changed="setPayList">
                            </pay-list>
                            <div class="text-right">
                                <p class="mt-10 mb-0">
                                    <b>{{ __('Итого к оплате за медикаментозное лечение:') }} {{ $formatter.numberFormat(totalToPay) }} {{ __('грн') }}<br>
                                    {{ __('Оплачено:') }} {{ $formatter.numberFormat(payed) }} {{ __('грн') }}<br>
                                    {{ __('Долг:') }} {{ $formatter.numberFormat(debt) }} {{ __('грн') }}</b></p>
                            </div>
                        </section>
                        <div class="dialog-footer text-right">
                            <el-button
                                @click="cancel">
                                {{ __('Отменить') }}
                            </el-button>
                            <el-button
                                v-if="showButtonEdit"
                                type="primary"
                                @click="editIssues">
                                {{ __('Редактировать') }}
                            </el-button>
                            <el-button
                                v-if="!showButtonEdit"
                                type="primary"
                                :disabled="disabledButtonToPay"
                                @click="sendToPayment">
                                {{ __('На оплату') }}
                            </el-button>
                            <el-button
                                type="primary"
                                :disabled="!fullPayed"
                                @click="issuePayed">
                                {{ __('Выдать') }}
                            </el-button>
                        </div>
                    </div>
                    <div v-show="!hasPayList">
                        <section class="flex-content">
                            <div class="empty-content-wrapper with-circle">
                                <div class="empty-content top-10">
                                    <div>
                                        <div class="empty-content-img"></div>
                                        <span>
                                            <a href="#" @click.prevent="assignPayedMedicine">
                                                {{ __('Выдать платные медикаменты') }}
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Препараты в рамках курса лечения')"
                name="freelist">
                <div v-show="hasFreeList">
                    <section class="grey-cap pr-20 pl-20 pt-0  pb-0">
                        <free-list
                            ref="freeList"
                            :patient="patient"
                            :clinics="clinics"
                            @loaded="freeListLoaded"
                            @selection-changed="setFreeList"
                        />
                    </section>
                    <div class="dialog-footer text-right">
                        <el-button
                            @click="cancel">
                            {{ __('Отменить') }}
                        </el-button>
                        <el-button
                            type="primary"
                            :disabled="disabledButtonIssueFree || debt > 0"
                            @click="issueFree">
                            {{ __('Выдать') }}
                        </el-button>
                    </div>
                </div>
                <div v-show="!hasFreeList">
                    <section class="flex-content">
                        <div class="empty-content-wrapper with-circle">
                            <div class="empty-content top-10">
                                <div>
                                    <div class="empty-content-img"></div>
                                    <span>
                                        <a href="#" @click.prevent="assignFreeMedicine">
                                            {{ __('Выдать бесплатные медикаменты') }}
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>
<script>
import PayList from './AssignedPayList.vue';
import FreeList from './AssignedFreeList.vue';
import AppointmentService from '@/models/appointment/service';
import GenericServiceRespository from '@/repositories/service';
import AppointmentServiceRepository from '@/repositories/appointment/service';
import SelectClinic from './assign/SelectClinic.vue';
import SearchMedicine from './assign/Search.vue';
import BatchRequest from '@/services/batch-request';
import CONSTANTS from '@/constants';

export default {
    components: {
        PayList,
        FreeList,
    },
    props: {
        patient: Object,
    },
    data() {
        return {
            saving: false,
            activeTab: 'paylist',
            clinics: this.getAllowedClinics(),
            changedPayItems: [],
            changedFreeItems: [],
            hasPayList: true,
            hasFreeList: true,
            onPaymentLoaded: false,
            onPaymentList: [],
            onPaymentServices: [],
            editActive: true,
            batchRequest: new BatchRequest('/api/v1/appointments/services/batch'),
            medicineService: null,
        }
    },
    computed: {
        servicesExists() {
            return this.onPaymentServices.length !== 0;
        },
        showButtonEdit() {
            return this.servicesExists && this.editActive == true;
        },
        disabledButtonToPay() {
            return this.changedPayItems.length == 0;
        },
        disabledButtonIssueFree() {
            return this.changedFreeItems.length == 0;
        },
        totalToPay() {
            return Math.round(this.changedPayItems.reduce((total, item) => {
                return total + Number(item.to_pay);
            }, 0));
        },
        payed() {
            if (this.servicesExists) {
                return this.onPaymentServices.reduce((total, service) => {
                    return total + Number(service.payed);
                }, 0);
            }
            return 0;
        },
        fullPayed() {
            if (this.servicesExists) {
                return this.totalToPay <= this.payed;
            }
            return false;
        },
        debt() {
            return this.totalToPay - this.payed;
        },
    },
    mounted() {
        this.getOnPaymentService();
    },
    methods: {
        addMedicine() {
            if (this.activeTab == 'paylist') {
                return this.assignPayedMedicine();
            }
            return this.assignFreeMedicine();
        },
        getOnPaymentService() {
            let service = new AppointmentServiceRepository();
            return service.fetchList(this.getAppointmentServiceFilters(), null).then((response) => {
                this.onPaymentList = [];
                if (response.length != 0) {
                    response.forEach(service => {
                        this.onPaymentServices.push(new AppointmentService(service));
                        this.onPaymentList = [...this.onPaymentList, ...service.items];
                    });
                    this.editActive = true;
                }
                this.onPaymentLoaded = true;
                return Promise.resolve();
            });
        },
        getAppointmentServiceFilters() {
            return {
                patient: this.patient.id,
                clinic: this.getAllowedClinics(),
                issued: false,
                is_deleted: false,
                container_type: CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.MEDICINES,
                has_to_issue_medicine: true,
            }
        },
        getAllowedClinics() {
            return this.$store.state.user.clinics;
        },
        cancel() {
            this.$emit('cancel');
        },
        close() {
            this.$emit('close');
        },
        payListLoaded() {
            this.hasPayList = (this.$refs.payList.getData().length != 0);
        },
        freeListLoaded() {
            this.hasFreeList = (this.$refs.freeList.getData().length != 0);
        },
        setPayList(list) {
            this.changedPayItems = [...list];
        },
        setFreeList(list) {
            this.changedFreeItems = [...list];
        },
        editIssues() {
            if (this.fullPayed) {
                return;
            }

            let rows = this.$refs.payList.getData();
            rows.forEach(row => {
                row.onPayment = false;
            });
            this.editActive = false;
        },
        issuePayed() {
            this.performPayServicesSubmit(true, __('Медикаменты выданы'));
        },
        verifyRests(items, clinic_id) {
            let outOfStore = [];
            items.forEach(item => {
                let avalilable = item.store_rests.reduce((sum, store) => {
                    if (store.clinics.indexOf(clinic_id) !== -1) {
                        sum += Number(store.rest);
                    }
                    return sum;
                }, 0);

                if (avalilable < item.issue_quantity) {
                    outOfStore.push(item.name);
                }
            });
            return outOfStore;
        },
        getCardSpecialization(list) {
            let withSpecialization = list.find(item => _.isFilled(item.card_specialization_id));
            return withSpecialization ? withSpecialization.card_specialization_id : null;
        },
        sendToPayment() {
            if (this.changedPayItems.length != 0) {
                if (this.clinics.length > 1) {
                    return this.$modalComponent(SelectClinic, {}, {
                        cancel: (dialog) => {
                            dialog.close();
                        },
                        selected: (dialog, clinic_id) => {
                            dialog.close();
                            let outOfStore = this.verifyRests(this.changedPayItems, clinic_id);
                            if (outOfStore.length != 0) {
                                return this.$error(__('Препаратов:') + ' ' + outOfStore.join('; ') + ' ' + __('недостаточно на складах выбранной клиники'));
                            }
                            return this.performPayServicesSubmit(false, __('Медикаменты поставлены в оплату'), {clinic_id});
                        },
                    }, {
                        header: __('Выберите клинику для постановки медикаментов в оплату'),
                        width: '500px',
                    });
                }
                return this.performPayServicesSubmit(false, __('Медикаменты поставлены в оплату'), {clinic_id: this.changedPayItems[0].clinic_id});
            }
        },
        performPayServicesSubmit(issued = false, onSuccessMessage = null, serviceAttributes = {}) {
            if (this.changedPayItems.length != 0) {
                this.saving = true;
                this.getMedicineService().then(() => {
                    if (_.isVoid(this.medicineService)) {
                        this.$error("Добавьте услугу __('Медикаментозное лечение') в клинику");
                    }

                    this.batchRequest.reset();
                    let items = this.getToIssueItems();
                    let groups = _.groupBy(items, 'assigner.id');

                    Object.keys(groups).forEach(key => {
                        let service = this.makeService({
                            ...serviceAttributes,
                            items: groups[key],
                            card_specialization_id: this.getCardSpecialization(groups[key]),
                        });
                        this.onPaymentServices.push(service);
                    });

                    this.onPaymentServices.forEach(service => {
                        service = this.updatePayServiceAttributes(service, issued);
                        if (service.isNew()) {
                            this.batchRequest.create(service);
                        } else {
                            if (service.items.length === 0) {
                                this.batchRequest.delete(service);
                            } else {
                                this.batchRequest.update(service);
                            }
                        }
                    });
                    this.submitBatchRequest(onSuccessMessage);
                });
            }
        },
        submitBatchRequest(successMessage) {
            this.saving = true;
            this.batchRequest.submit().then((result) => {
                if (result.failure.length !== 0) {
                    this.$error(__('Не удалось сохранить некоторые данные'));
                }
                if (result.success.length != 0) {
                    this.$info(successMessage)
                }
                this.saving = false;
                this.close();
            }).catch((error) => {
                this.saving = false;
                if (error.invalid) {
                    this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
                }
            });
        },
        updatePayServiceAttributes(service, issued = false) {
            service.items = this.mapServiceItems(service.items);
            service.issued = issued;
            return service;
        },
        getToIssueItems() {
            let newItems = [...this.changedPayItems];
            this.onPaymentServices.forEach(service => {
                service.items = this.changedPayItems.filter(item => item.assigner.id == service.assigner);
                service.items.forEach(item => {
                    let index = newItems.findIndex(medicine => item.id == medicine.id);
                    if (index !== -1) {
                        newItems.splice(index, 1);
                    }
                });
            });
            return newItems;
        },
        makeService(attributes = {}) {
            return new AppointmentService({
                service_id: this.medicineService.id,
                clinic_id: this.clinics[0],
                patient_id: this.patient.id,
                container_type: CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.MEDICINES,
                ...attributes,
            });
        },
        issueFree() {
            if (this.changedFreeItems.length != 0) {
                if (this.clinics.length > 1) {
                    return this.$modalComponent(SelectClinic, {}, {
                        cancel: (dialog) => {
                            dialog.close();
                        },
                        selected: (dialog, clinic_id) => {
                            dialog.close();
                            let outOfStore = this.verifyRests(this.changedFreeItems, clinic_id);
                            if (outOfStore.length != 0) {
                                return this.$error(__('Препаратов:') + ' ' + outOfStore.join('; ') + ' ' + __('недостаточно на складах выбранной клиники'));
                            }
                            return this.submitFreeItems({clinic_id});
                        },
                    }, {
                        header: __('Выберите клинику для постановки медикаментов в оплату'),
                        width: '500px',
                    });
                }
                return this.submitFreeItems();
            }
        },
        submitFreeItems(serviceAttributes = {}) {
            this.saving = true;
            this.getMedicineService().then(() => {
                if (_.isVoid(this.medicineService)) {
                    this.$error("Добавьте услугу __('Медикаментозное лечение') в клинику");
                }

                this.batchRequest.reset();
                let items = this.mapServiceItems(this.changedFreeItems);
                let groups = _.groupBy(items, 'assigner.id');
                
                Object.keys(groups).forEach(key => {
                    let service = this.makeService({
                        ...serviceAttributes, 
                        items: groups[key],
                        issued: true,
                        card_specialization_id: this.getCardSpecialization(groups[key]),
                    });
                    this.batchRequest.create(service);
                });
                this.submitBatchRequest(__('Медикаменты выданы'));
                
            });
        },
        mapServiceItems(items) {
            return items.map((item) => {
                let cost = item.is_free ? 0 : Math.round(item.issue_quantity * Number(item.base_cost));
                return {
                    service_id: item.id,
                    service_type: CONSTANTS.APPOINTMENT_SERVICE.ITEM_TYPES.ASSIGNED_MEDICINE,
                    quantity: item.issue_quantity,
                    cost: cost,
                    self_cost: item.self_cost,
                    discount: item.discount || 0,
                    assigner: item.assigner,
                }
            });
        },
        getMedicineService() {
            let serviceRepo = new GenericServiceRespository();
            return serviceRepo.fetchList(this.getServiceFilter(), null, 1).then((response) => {
                if (response.length !== 0) {
                    this.medicineService = response[0];
                    return Promise.resolve();
                }
                return Promise.reject();
            });
        },
        getServiceFilter() {
            return {
                payment_destination_mark: CONSTANTS.PAYMENT_DESTINATION.ADDITIONAL_MARK.FOR_MEDICINES,
                clinic: this.clinics,
            }
        },
        assignPayedMedicine() {
            this.searchMedicines(this.$refs.payList, {isFree: false});
        },
        assignFreeMedicine() {
            this.searchMedicines(this.$refs.freeList, {isFree: true});
        },
        searchMedicines(table, props = {}) {
            if (this.clinics.length > 1) {
                return this.selectEmployeeClinic(table, props);
            }
            return this.showSearchForm(table, props);
        },
        selectEmployeeClinic(table, props = {}) {
            this.$modalComponent(SelectClinic, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, clinic_id) => {
                    dialog.close();
                    table.refresh();
                    this.showSearchForm(table, {...props, clinic: [clinic_id]});
                },
            }, {
                header: __('Выберите клинику для добавления медикаментов'),
                width: '500px',
            });
        },
        showSearchForm(table, props = {}) {
            this.$modalComponent(SearchMedicine, {
                patient: this.patient,
                employee: this.$store.state.user.employee,
                clinic: this.clinics,
                ...props,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog) => {
                    dialog.close();
                    table.refresh();
                    this.clearChangedItems();
                },
            }, {
                header: __('Выбрать медикаменты для выдачи'),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
        clearChangedItems() {
            this.changedPayItems = [];
            this.changedFreeItems = [];
        },
    },
}
</script>
