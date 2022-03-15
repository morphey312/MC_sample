<template>
    <div v-loading="loading">
        <div class="add-assignments-btn">
            <svg-icon
                v-if="patientHasAssignedServices"
                name="catalogue"
                class="icon-small icon-blue mr-10">
                <a
                    href="#"
                    @click.prevent="showAssignments">
                    {{ __('Есть назначенные услуги') }}
                </a>
            </svg-icon>
            <svg-icon
                v-if="model.regular_services.length > 0 && !model.isNew() && $can('appointments.transfer-services')"
                name="arrow-right-alt"
                class="icon-small icon-blue mr-20">
                <a
                    href="#"
                    @click.prevent="transferToOtherAppointment">
                    {{ __('Перенести в другую запись') }}
                </a>
            </svg-icon>
        </div>
        <form-row name="services">
            <transfer-table
                v-if="model.loading === false"
                ref="transfer"
                key="services"
                v-model="model.regular_services"
                :items="service_list"
                :fields="fields"
                :items-fields="itemFields"
                value-key="service_id"
                :left-title="__('Доступные услуги')"
                :right-title="__('Выбранные услуги')"
                left-width="195px"
                right-width="200px"
                left-class="no-ellipsis"
                right-class="no-ellipsis text-select"
                :initial-filters="{'itemdata.specialization.id': model.specialization_id}"
                :checked-callback="checkedRowCallback"
                :show-extra-fields="showExtraFields"
                @new-row="initService">
                <template
                    slot="cost"
                    slot-scope="props">
                    {{ props.rowData.data.cost }}
                </template>
                <template
                    slot="quantity"
                    slot-scope="props">
                    <form-input-number
                        :entity="props.rowData.data"
                        property="quantity"
                        control-size="mini"
                        css-class="table-row"
                        :disabled="(props.rowData.data.cost != 0 && props.rowData.data.cost == props.rowData.data.payed)"
                        @changed="calcPrice(props.rowData.data)"
                    />
                </template>
                <template
                    slot="discount"
                    slot-scope="props">
                    <form-input-number
                        :entity="props.rowData.data"
                        property="discount"
                        control-size="mini"
                        css-class="table-row"
                        :max="maxDiscount(props)"
                        :min="0"
                        :step="0.01"
                        :disabled="props.rowData.data.by_policy == true"
                        @changed="calcPrice(props.rowData.data)"
                    />
                </template>
                <template
                    slot="is_base"
                    slot-scope="props">
                    <form-checkbox
                        :entity="props.rowData.data"
                        property="is_base"
                        css-class="table-row"
                    />
                </template>
                <template
                    slot="by_policy"
                    slot-scope="props">
                    <form-checkbox
                        :entity="props.rowData.data"
                        property="by_policy"
                        @changed="setServicePrice(props.rowData.data)"
                    />
                </template>
                <template
                    slot="franchise"
                    slot-scope="props">
                    <form-input-number
                        :entity="props.rowData.data"
                        property="franchise"
                        control-size="mini"
                        :max="100"
                        :min="0"
                        :step="0.01"
                        css-class="table-row"
                    />
                </template>
                <template
                    slot="warranter"
                    slot-scope="props">
                    <form-input
                        :entity="props.rowData.data"
                        property="warranter"
                        css-class="table-row"
                    />
                </template>
                <template
                    slot="payed"
                    slot-scope="props">
                    {{ $formatter.numberFormat(props.rowData.data.payed) }}
                </template>
                <div
                    slot="right-footer"
                    class="transfer-footer text-right">
                    <span class="input-label">
                        {{ __('Итоговая сумма по всем услугам:') }} {{ totalCost }}
                    </span>
                </div>
            </transfer-table>
        </form-row>
    </div>
</template>

<script>
import ServiceRepository from '@/repositories/service';
import SpecializationRepository from '@/repositories/specialization';
import Service from '@/models/appointment/service';
import WaitListRecord from '@/models/wait-list-record';
import AssignedService from '@/models/patient/assigned-service';
import AssignedList from './Assigned.vue';
import transferServices from '../transfer-service/transferServices.vue';
import {getServicePrice, getInsurancePrice} from '@/services/appointment/service-price';
import CONSTANTS from '@/constants';
import PriceCalculationMixin from '@/mixins/appointment/analysis/price-calculation';
import Appointment from "@/models/appointment";

export default {
    mixins: [
        PriceCalculationMixin,
    ],
    props: {
        model: Object,
        appointmentData: {
            type: Object,
            default: () => ({})
        },
        enquiry: Object,
        insurancePolicy: Object,
        showExtraFields: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            service_list: [],
            filteredData: [],
            specialization_list: [],
            totalCost: 0,
            itemFields: [
                {
                    name: 'itemdata.specialization.name',
                    title: __('Специализация'),
                    width: '110px',
                    filter: new SpecializationRepository({
                        filters: {clinic: this.model.clinic_id, id: this.appointmentData.specialization}
                    }),
                    filterField: 'itemdata.specialization.id',
                },
                {
                    name: 'itemdata.cost',
                    title: __('Стоимость, грн'),
                    width: '80px',
                    dataClass: 'text-right',
                    editable: false,
                },
            ],
            fields: [
                {
                    name: 'itemdata.specialization.name',
                    title: __('Спец-ция'),
                    width: '80px',
                    editable: false,
                },
                {
                    name: 'cost',
                    title: __('Ст-ть, грн'),
                    width: '80px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    editable: false,
                },
                {
                    name: 'quantity',
                    title: __('Кол-во'),
                    width: '60px',
                },
                {
                    name: 'discount',
                    title: __('Скидка, %'),
                    width: '56px',
                    titleClass: 'text-right',
                },
                {
                    name: 'is_base',
                    title: __('Базовая'),
                    width: '60px',
                },
                {
                    name: 'by_policy',
                    title: __('Полис'),
                    width: '48px',
                    extra: true,
                    editable: false,
                },
                {
                    name: 'franchise',
                    title: __('Фр-за, %'),
                    width: '48px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    extra: true,
                    editable: false,
                },
                {
                    name: 'warranter',
                    title: __('Гарант'),
                    width: '70px',
                    extra: true,
                    editable: false,
                },
                {
                    name: 'payed',
                    title: __('Опл-но, грн'),
                    width: '60px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
            ],
            assigned_services: [],
            payedServices: [],
            loading: false,
            enquiry_services: [],
        }
    },
    computed: {
        discountCard() {
            return this.appointmentData.discountCard;
        },
        availableAssignments() {
            return this.assigned_services.filter((item) => {
                return item.quantity > 0 && item.appointment_card_specialization == this.model.card_specialization_id;
            });
        },
        patientHasAssignedServices() {
            return this.model.patient && this.availableAssignments.length !== 0;
        },
        clinicNotRoundCost(){
            return this.appointmentData.clinic.not_round_cost;
        }
    },
    watch: {
        ['model.services']() {
            this.setTotalCost();
            this.restoreAssignments();
        },
        ['model.patient.id']() {
            this.castAssignedServices();
            this.removeAssignedFromSelection();
        },
        totalCost() {
            this.$eventHub.$emit('total-changed');
        },
        ['model.card_specialization_id']() {
            this.getServices()
        },
        ['model.specialization_id']() {
            if (this.enquiry) {
                let tempList = [...this.model.services];
                let transfer = this.getTransfer();

                this.model.services.forEach(item => {
                    if (_.isVoid(item.id)) {
                        let removeIndex = this.enquiry_services.findIndex(service => item.service_id == service.service_id);
                        if (removeIndex !== -1) {
                            transfer.removeFromSelection(item.service_id);
                        }
                    }
                    return;
                });
                this.enquiry_services.forEach(item => {
                    if (item.specialization.id == this.model.specialization_id) {
                        transfer.addToSelection(item.service_id);
                    }
                });
            }
        },
        insurancePolicy(val) {
            this.$nextTick(() => {
                this.updateInsurancePrices().then(() => {
                    this.model.services.forEach(item => {
                        if (!val) {
                            item.by_policy = false;
                        }
                        this.setServicePrice(item);
                    });
                });
            });
        },
    },
    created() {
        this.loadedInsurancePrices = [];
    },
    mounted() {
        this.$eventHub.$on('discountSelected', this.recalcServicesDiscount);

        this.getPayedServices();
        this.castAssignedServices();

        if (this.enquiry && this.enquiry.service_list && this.enquiry.service_list.length != 0) {
            this.castEnquiryServices().then(() => {
                this.initServiceData();
            });
        } else if (this.model.isNew()) {
            let processState = (this.$store.state && this.$store.state.processState) ? this.$store.state.processState : null;
            if (processState && processState.processLog && processState.processLog.wait_list_record) {
                this.getWaitListRecord(processState.processLog.wait_list_record).then(() => {
                    this.initServiceData();
                });
            } else {
                this.initServiceData();
            }
        } else {
            this.initServiceData();
        }
    },
    beforeDestroy() {
        this.$eventHub.$off('discountSelected', this.recalcServicesDiscount);
    },
    methods: {
        maxDiscount(props) {
            props.rowData.data.payed > 0 ? parseFloat((100 - (100 * props.rowData.data.payed / props.rowData.data.price)).toFixed(2)) : 100
        },
        recalcServicesDiscount() {
            this.model.services.forEach((service) => {
                if (!service.by_policy) {
                    service.discount = this.calcModelDiscount(service, 'service');
                }

                if (Number(service.cost) == 0 && Number(service.saved_cost) !== Number(service.cost)) {
                    service.cost = service.saved_cost
                }

                if (Number(service.cost > 0)) {
                    this.calcPrice(service);
                }
            });

            this.$discountData.reload.service = false;
            this.$discountData.firstCalcDiscountCard.service = false;
        },
        initServiceData() {
            this.getServices();
            this.setTotalCost();
        },
        getWaitListRecord(recordId) {
            let record = new WaitListRecord({id: recordId});
            return record.fetch([
                'prepayment_service'
            ]).then(() => {
                let prepayment = record.prepayment_service;
                if (this.model.clinic_id == record.clinic_id && prepayment.service.specialization.id == this.model.specialization_id) {
                    let service = this.createServiceModel(prepayment.service);
                    service.discount = prepayment.discount;
                    service.self_cost = prepayment.cost;
                    service.cost = prepayment.cost;
                    this.addServiceToAppointment(service);
                }
                return Promise.resolve();
            });
        },
        castEnquiryServices() {
            this.enquiry_services = this.enquiry.service_list.map(item => {
                let service = this.createServiceModel(item.service);
                if (!item.is_prepayment) {
                    service.discount = item.discount;
                    service.price = item.price;
                    service.self_cost = item.cost;
                    service.cost = item.payed_amount;
                    service.set('payed', item.payed_amount);
                }
                return service;
            });
            this.addEnquiryServicesToAppointment();
            return Promise.resolve();
        },
        addEnquiryServicesToAppointment() {
            this.enquiry_services.forEach(item => {
                if (item.specialization.id == this.model.specialization_id) {
                    this.addServiceToAppointment(item);
                }
            });
        },
        addServiceToAppointment(service) {
            let index = this.assigned_services.findIndex((row) => {
                return row.service_id == service.service_id;
            });

            if (index !== -1) {
                let data = this.getAssignmentQuantity(index, service);
                this.assigned_services[index].quantity = data.quantity;

                if (service.quantity <= data.available_quantity) {
                    service.card_assignment_id = this.assigned_services[index].card_assignment_id;
                }
            }
            this.model.services.push(service);
        },
        getPayedServices() {
            this.model.services.forEach(service => {
                if (service.payed != 0) {
                    this.payedServices.push(service.service_id);
                }
            });
        },
        castAssignedServices() {
            if (_.isEmpty(this.model.patient) || _.isEmpty(this.model.patient.assigned_services)) {
                this.assigned_services = [];
                return;
            }

            let edgeDate = this.$moment(this.model.date + ' 23:59:59');
            let results = this.model.patient.assigned_services.filter((item) => {
                // Check if assignment was made not later than appointment date
                if (edgeDate.isBefore(item.assignment_date)) {
                    return false;
                }
                // Is service was assigned in the same clinic it is available regardless
                if (item.clinic_id == this.model.clinic_id) {
                    return true;
                }
                // Is service was assigned in another clinic it is available only if has price
                if (item.clinic_group.indexOf(this.model.clinic_id) !== -1) {
                    return item.active_prices.some((price) => price.clinics.indexOf(this.model.clinic_id) !== -1);
                }
                return false;
            });

            this.assigned_services = results.map((row) => {
                row.available_quantity = row.quantity;
                row.model_id = row.id;
                return new AssignedService(row);
            });
        },
        verifyByPolicy() {
            let withPolicy = this.availableAssignments.filter(s => s.by_policy);
            return withPolicy.length != 0;
        },
        beforeAddAssignedService() {
            this.$discountData.reload.analysis = false;
            this.$discountData.firstCalcDiscountCard.analysis = false;
            this.$discountData.refreshDiscountType = 0;
        },
        afterAddAssignedService(refreshDiscountType) {
            this.$discountData.refreshDiscountType = refreshDiscountType;
        },
        showAssignments() {
            this.$modalComponent(AssignedList, {
                services: this.availableAssignments,
                withPolicy: this.verifyByPolicy(),
                insurancePolicy: this.insurancePolicy,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.addToServiceList(list);
                    this.$emit('assigned-add', list);
                },
            }, {
                header: __('Выбрать назначенные услуги'),
                width: '800px',
                customClass: 'padding-0',
            });
        },
        addToServiceList(list) {
            list.forEach((row) => {
                let service = this.model.services.find((item) => item.service_id == row.service_id);
                if (_.isFilled(service)) {
                    let warning = __('{name} уже присутствует в выбранном списке. Увеличить количество?', {name: row.name});
                    return this.$confirm(warning, () => {
                        service.quantity++;
                        this.calcPrice(service, false);
                    });
                } else {
                    this.updateServiceList(row);
                }
                row.quantity--;
            });
        },
        updateServiceList(item) {
            let itemdata = {
                ...item.attributes,
                quantity: 1,
                prices: item.prices,
                model: new AssignedService(),
            };
            let transfer = this.getTransfer();
            transfer.addAvailableRow({
                id: item.service_id,
                value: item.name,
                itemdata,
            });
            transfer.addToSelection(item.service_id);
        },
        getTransfer() {
            return this.$refs.transfer;
        },
        getServices() {
            this.loadPriceList().then((response) => {
                let removeIds = this.setPricesToAssignedServices(response);
                response = this.removeServicesByAssigned(response, removeIds);
                this.service_list = response.map((row) => {
                    return {
                        id: row.id,
                        value: row.name,
                        itemdata: this.createServiceModel(row),
                    }
                });
                this.$nextTick(() => {
                    this.$emit('services-loaded');
                });
            });
        },
        loadPriceList() {
            this.loading = true;
            let service = new ServiceRepository();
            let filters = {
                or: [
                    this.getServiceIdsFilter(),
                    this.getServiceFilters(),
                ],
            };
            let params = {};
            let insurerId = false;
            if (this.insurancePolicy) {
                insurerId = this.insurancePolicy.insurance_company_id;
                params.withInsurer = insurerId;
            }
            return service.fetchListForAppointment(filters, params).then((response) => {
                this.loading = false;
                if (insurerId) {
                    this.loadedInsurancePrices.push(insurerId);
                }
                return response;
            });
        },
        removeServicesByAssigned(list, removeList) {
            removeList.forEach(id => {
                let idx = list.findIndex(service => service.id == id);
                if (idx !== -1) {
                    list.splice(idx, 1);
                }
            });
            return list;
        },
        getServiceIdsFilter() {
            return {
                id: this.model.services
                    .filter(service => _.isVoid(service.container_type))
                    .map(service => service.service_id),
            };
        },
        getServiceFilters() {
            return {
                hasPrice: {...this.appointmentData.hasPrice},
                disabled: false,
                ...(this.$store.state.user.isOperator ? {base: 0} : {}),
            };
        },
        setPricesToAssignedServices(list) {
            let removeList = [];
            if (this.assigned_services.length !== 0) {
                this.assigned_services.forEach(assigned => {
                    if (assigned.appointment_card_specialization === this.model.card_specialization_id) {
                        let serviceIndex = list.findIndex(item => item.id == assigned.service_id);
                        if (serviceIndex !== -1) {
                            assigned.prices = list[serviceIndex].prices;
                            if (this.model.services.findIndex(item => item.service_id == assigned.service_id) === -1) {
                                removeList.push(list[serviceIndex].id);
                            }
                        }
                    }
                });
            }
            return removeList;
        },
        createServiceModel(item) {
            let service = new Service();
            service.castServiceDataToEntity(item, this.appointmentData);
            service.model = new Service();
            return service;
        },
        initService(data, option) {
            data.id = null;
            data.payment_destination_id = option.itemdata.payment_destination_id;
            data.is_base = option.itemdata.is_base;
            data.service_id = option.itemdata.service_id;
            data.model = option.itemdata.model;
            data.quantity = option.itemdata.quantity;
            data.by_policy = option.itemdata.by_policy ? true : false;
            data.warranter = option.itemdata.warranter;
            data.franchise = option.itemdata.franchise;
            data.prices = option.itemdata.prices;

            if (this.enquiry && this.enquiry_services.length != 0) {
                let service = this.enquiry_services.find(item => item.service_id == option.id);
                if (service) {
                    data.discount = service.discount;
                    data.price = service.cost;
                    data.price_id = option.itemdata.price_id;
                    data.self_cost = service.cost;
                    data.cost = service.cost;
                    data.payed = service.payed;
                } else {
                    this.castTransferServiceAttributes(data, option);
                }
            } else {
                this.castTransferServiceAttributes(data, option);
            }
        },
        castTransferServiceAttributes(data, option) {
            data.cost = option.itemdata.cost;
            data.payed = option.itemdata.payed;
            data.price = option.itemdata.price;
            data.self_cost = option.itemdata.price;
            data.price_id = option.itemdata.price_id;
            data.discount = option.itemdata.discount;

            if (this.isAssignment(option.itemdata.model)) {
                data.card_assignment_id = option.itemdata.card_assignment_id;
                data.model_id = option.itemdata.model_id;
            }

            this.$nextTick(() => {
                this.updateRowCost(option);
            });
        },
        updateRowCost(option) {
            let newAssignment = false;

            if (this.isAssignment(option.itemdata.model)) {
                newAssignment = true
            }

            let row = this.model.services.find(item => item.service_id == option.id);
            this.setServicePrice(row, newAssignment);
            this.calcPrice(row);
        },
        setTotalCost() {
            this.totalCost = _.sumOf(this.model.services, 'cost').toFixed(2);
        },
        calcPrice(service, recalcAssignment = true) {
            this.$nextTick(() => {
                if (this.isAssignment(service.model)) {
                    if (service.cost > 0) {
                        service.cost = (this.getPrice(service) * service.quantity).toFixed(2)
                    }
                    if (recalcAssignment) {
                        this.setAssignmentQuantity(service);
                    }
                } else {
                    if (service.price > 0) {
                            service.cost = (this.getPrice(service) * service.quantity).toFixed(2);
                    }
                }
                this.setTotalCost();
            });
        },
        getPrice(service, field = 'price') {
            let price;

            if (_.isFilled(service['discount'])) {
                price = Number(service[field]) - (Number(service[field]) / 100 * service['discount']);
            } else {
                price = service[field];
            }

            if (!this.clinicNotRoundCost) {
                return Math.round(price);
            }

            return price;
        },
        isAssignment(model) {
            return model instanceof AssignedService;
        },
        setAssignmentQuantity(item) {
            let index = this.getAssignedItemIndex(item);
            if (index !== -1) {
                let data = this.getAssignmentQuantity(index, item);
                this.assigned_services[index].quantity = data.quantity;
            }
        },
        getAssignmentQuantity(index, item) {
            let available_quantity = this.assigned_services[index].available_quantity;
            let quantity = 0;
            if (item.quantity <= available_quantity) {
                quantity = this.assigned_services[index].available_quantity - item.quantity;
                if (quantity <= 0) {
                    quantity = 0;
                }
            }
            return {quantity, available_quantity};
        },
        getAssignedItemIndex(item, field = 'model_id') {
            return this.assigned_services.findIndex((row) => {
                return row.id == item[field];
            });
        },
        removeAssignedFromSelection() {
            let services = [...this.model.services];
            let transfer = this.getTransfer();
            services.forEach((service) => {
                if (_.isFilled(service.card_assignment_id)) {
                    transfer.removeFromSelection(service.id);
                }
            });
        },
        restoreAssignments() {
            this.assigned_services.forEach((service) => {
                let index = this.model.services.findIndex((row) => row.service_id == service.service_id);
                if (index === -1) {
                    service.quantity = service.available_quantity;
                }
            });
        },
        checkedRowCallback(table) {
            if (table) {
                let selected = table.selectedTo;
                this.payedServices.forEach(service_id => {
                    let index = selected.findIndex(id => service_id == id);
                    if (index != -1) {
                        selected.splice(index, 1);
                    }
                });
            }
        },
        setServicePrice(service, newAssignment = false) {
            this.$nextTick(() => {
                let priceData;
                let refreshDiscountType = 0

                if (newAssignment === true) {
                    refreshDiscountType = this.$discountData.refreshDiscountType;
                    this.beforeAddAssignedService();
                }

                if (service.by_policy) {
                    priceData = getInsurancePrice(service, this.appointmentData, CONSTANTS.PRICE.SET_TYPE.INSURANCE, this.insurancePolicy.insurance_company_id);
                    service.discount = 0;
                } else {
                    let discount = this.calcModelDiscount(service, 'service');
                    priceData = getServicePrice(service, this.appointmentData, CONSTANTS.PRICE.SET_TYPE.BASE);
                    if (service.is_base === true &&
                        service.saved_cost !== priceData.price &&
                        service.saved_policy === false &&
                        service.saved_discount !== 0 &&
                        service.saved_discount !== discount) {
                        priceData.price = service.saved_cost;
                        service.discount = service.saved_discount;
                    } else {
                        if (discount > service.discount) {
                            service.discount = discount;
                        }
                    }
                }

                if (priceData && priceData.id) {
                    service.price_id = priceData.id;
                    service.price = priceData.price;
                    service.selfCost = priceData.selfCost;
                }

                if (refreshDiscountType !== 0) {
                    this.afterAddAssignedService(refreshDiscountType);
                }

                this.calcPrice(service);
            });
        },
        updateInsurancePrices() {
            if (!this.insurancePolicy ||
                this.loadedInsurancePrices.indexOf(this.insurancePolicy.insurance_company_id) !== -1) {
                return Promise.resolve();
            }
            let insurerId = this.insurancePolicy.insurance_company_id;
            return this.loadPriceList().then((response) => {
                let transfer = this.getTransfer();
                response.forEach((service) => {
                    service.prices.forEach((price) => {
                        if (price.set_type === CONSTANTS.PRICE.SET_TYPE.INSURANCE &&
                            price.set_owner.owner_id === insurerId) {
                            this.extendServicePrices(transfer, service.id, price);
                        }
                    });
                });
            });
        },
        extendServicePrices(transfer, serviceId, price) {
            let service = transfer.findOption(serviceId);
            if (service) {
                service.itemdata.prices.push(price);
            } else {
                service = this.assigned_services.find(service => service.service_id === serviceId)
                if (service) {
                    service.prices.push(price);
                }
            }
        },
        transferToOtherAppointment() {
            this.$modalComponent(transferServices, {
                serviceType: CONSTANTS.APPOINTMENT_SERVICE.SERVICE_TYPE.SERVICE,
                showExtraFields: this.showExtraFields,
                appointment: this.model,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                done: (dialog, datePass) => {
                    dialog.close();
                    this.$eventHub.$emit('updateAppointment');
                },
            }, {
                header: __('Выбирите запись'),
                width: '1200px',
            });
        }
    }
}
</script>
