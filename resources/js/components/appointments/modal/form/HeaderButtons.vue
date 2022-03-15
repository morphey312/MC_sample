<template>
    <div class="buttons">
        <template v-if="activeLegalEntity">
            <svg-icon
                name="delete"
                class="icon-small icon-blue cursor-pointer"
                @click="removeLegalEntity"
            />
            <span class="mr-10 action-link">
                {{ __("Сотрудник компании:") }} {{ activeLegalEntity.title }}
            </span>
        </template>
        <template
            v-if="hasActiveAmbulanceCall && !missedPatient && $can('appointments.call_ambulance')"
        >
            <svg-icon
                name="delete"
                class="icon-small icon-blue cursor-pointer"
                @click="removeActiveAmbulanceCall"
            />
            <span class="mr-10 action-link" @click="editAmbulanceCall">
                {{ __("Вызов скорой") }}
            </span>
        </template>
        <template v-if="hasInsurancePolicies">
            <template v-if="activeInsurancePolicy">
                <svg-icon
                    name="delete"
                    class="icon-small icon-blue cursor-pointer"
                    @click="removeInsurancePolicy"
                />
                <span class="mr-10 action-link">
                    {{ __("Страховой полис:") }}
                    {{ activeInsurancePolicy.company.name }}
                </span>
            </template>
        </template>
        <template v-if="showCards">
            <svg-icon
                name="arrow-circle"
                class="icon-small icon-blue cursor-pointer"
                @click="reloadDiscountCard"
            />
            <svg-icon
                name="delete"
                class="icon-small icon-blue cursor-pointer"
                @click="removeDiscountCard"
            />
            <el-dropdown
                trigger="click"
                class="action-link mr-10"
                @command="setDiscountCard"
            >
                <span class="el-dropdown-link">
                    {{ __("Диск. карта:") }}
                    <el-tooltip
                        v-if="!isPatientActive(activeCard)"
                        placement="bottom"
                        effect="light"
                        :open-delay="500"
                        popper-class="light-popover-content patient-warning pl-0 pr-0"
                    >
                        <template slot="content">
                            <div class="pl-10 pr-10">
                                <span>{{
                                        __("Дисконтная карта не активна")
                                    }}</span>
                            </div>
                        </template>
                        <span class="in-active-discount-card">{{
                                activeCard.type.name
                            }}</span>
                    </el-tooltip>
                    <span v-else>{{ activeCard.type.name }}</span>
                    <i class="el-icon-caret-bottom el-icon--right"></i>
                </span>
                <el-dropdown-menu slot="dropdown">
                    <template v-for="(card, index) in addedCards">
                        <el-dropdown-item
                            :key="card.id"
                            :command="{ index, refreshDiscountType: 1 }"
                        >
                            <span
                                :class="
                                    !isPatientActive(card)
                                        ? 'in-active-discount-card'
                                        : ''
                                "
                            >{{ card.type.name }}</span
                            >
                        </el-dropdown-item>
                    </template>
                </el-dropdown-menu>
            </el-dropdown>
        </template>
        <template v-if="hasAccessActsAndAccounts">
            <el-dropdown
                trigger="click"
                class="action-link mr-10"
                @command="handleCommand"
            >
                <span class="el-dropdown-link">
                    <svg-icon name="print" class="icon-small icon-blue">
                        {{ __("Печать")
                        }}<i class="el-icon-caret-bottom el-icon--right" />
                    </svg-icon>
                </span>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item command="addProvidingServices">
                        {{ __("Акт оказания услуг") }}
                    </el-dropdown-item>
                    <el-dropdown-item command="addInvoicePayment">
                        {{ __("Счет на оплату") }}
                    </el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </template>
        <el-dropdown
            trigger="click"
            class="action-link mr-10"
            @command="handleCommand"
        >
            <span class="el-dropdown-link">
                <svg-icon name="plus-alt" class="icon-small icon-blue">
                    {{ __("Добавить в запись")
                    }}<i class="el-icon-caret-bottom el-icon--right" />
                </svg-icon>
            </span>
            <el-dropdown-menu slot="dropdown">
                <el-dropdown-item
                    :disabled="!hasInsurancePolicies"
                    command="addInsurancePolicy"
                >
                    {{ __("Полис") }}
                </el-dropdown-item>
                <el-dropdown-item
                    :disabled="!hasLegalEntity"
                    command="addLegalEntity"
                >
                    {{ __("Компанию") }}
                </el-dropdown-item>
                <el-dropdown-item
                    :disabled="discountDisabled"
                    command="addDiscountCards"
                >
                    {{ __("Диск. карту в запись") }}
                </el-dropdown-item>
                <el-dropdown-item
                    command="addAmbulanceCall"
                    :disabled="missedPatient"
                    v-if="$can('appointments.call_ambulance')"
                >
                    {{ __("Вызов скорой помощи") }}
                </el-dropdown-item>
            </el-dropdown-menu>
        </el-dropdown>
    </div>
</template>
<script>
import CONSTANTS from "@/constants";
import AddCard from "./discount-cards/AddCard.vue";
import AmbulanceCallCreate from "./ambulance-calls/FormCreate.vue";
import AmbulanceCallEdit from "./ambulance-calls/FormEdit.vue";
import AddInsurancePolicy from "./insurance-policies/AddPolicy.vue";
import Clinic from "@/models/clinic";
import Appointment from "@/models/appointment";
import AmbulanceCall from "@/models/ambulance-call";
import printer from "@/services/print";
import {multiPagesToPdf} from '@/services/pdf-blank';
import fileLoader from "@/services/file-loader";
import ProvidingService from "./prints/documents/ProvidingService";
import AppointmentsFileViews from "@/components/appointments/modal/form/prints/AppointmentsFileViews";
import FileViewerHeader from "@/components/general/FileViewerHeader";
import PaymentInvoice from "./prints/documents/PaymentInvoice";
import SpecializationRepository from '@/repositories/specialization';
import MoneyRecieverRepository from '@/repositories/clinic/money-reciever';


const APPOINTMENT_DOCUMENT_SETTINGS = {
    styles: `
        @page {
            size: A4;
        }
        html, body {
            width: 185mm;
            height: 280mm;
            padding: 0;
            margin: 0;
            font-family: Arial, sans-serif;
            font-weight: normal;
            color: #000;
        }
        table {
            width: 100%;
        }
        .flex-line {
            display: flex;
            width: 100%;
        }
        .right-content {
            margin-left: auto;
        }
        .sign-wrapper {
            margin-top: 30px;
        }
        .sign {
            width: 180px;
            border-bottom: 1px solid #000;
        }
        .ml-30 {
            margin-left: 30px;
        }
        .printable-table {
            margin-bottom: 30px;
            font-weight: normal;
            border-spacing: 0;
            border-collapse: collapse;
        }
        .printable-table thead tr {
            background: #e3e3e3;
        }
        .printable-table td, .printable-table th {
            border: 1px solid #000;
            padding: 5px;
        }
        .total div {
            margin-left: 30px;
        }
        .total {
            display: flex;
        }
        .unstyled-list {
            list-style-type: none;
            margin-left: 0;
            padding-left: 0;
            margin-top: 0;
            padding-top: 0;
        }
        .first-bold-li li:first-child {
            font-weight: bold;
        }
        .mt-20 {
            margin-top: 20px;
        }
        .bold-text {
            font-weight: bold;
        }
        .border-bottom {
            border-bottom: 1px solid #000;
        }
        .pb-20 {
            padding-bottom: 20px;
        }
        .pb-10 {
            padding-bottom: 10px;
        }
        .small-text {
            font-size: 12px;
            font-weight: light;
        }
        .border-content {
            padding: 5px 40px;
            border: 1px solid #000;
        }
        .big-text {
            font-size: 20px;
        }
        .text-center {
            text-align: center;
        }
        .w-auto {
            width: auto;
        }
    `,
};

export default {
    props: {
        appointment: Object
    },
    data() {
        return {
            activeCards: [],
            addedCards: [],
            patient: this.appointment.patient,
            previousPatient: null,
            activeCard: null,
            cardSelected: false,
            refreshDiscountType: 0,
            activeInsurancePolicy: null,
            activeLegalEntity: null,
            activeAmbulanceCall: null,
            typeDocument: null
        };
    },
    computed: {
        isNew() {
            return (
                _.isFunction(this.appointment.isNew) === false ||
                this.appointment.isNew()
            );
        },
        showCards() {
            return this.activeCard ? this.cardSelected : false;
        },
        missedPatient() {
            return _.isEmpty(this.patient);
        },
        discountDisabled() {
            return (
                this.appointment.is_deleted === true ||
                this.missedPatient ||
                this.activeCards.length == 0 ||
                this.activeCards.length === this.addedCards.length
            );
        },
        activeInsurancePolicies() {
            return this.missedPatient || !this.patient.insurance_policies
                ? []
                : this.patient.insurance_policies.filter(p => {
                    return (
                        p.expires >= this.appointment.date &&
                        p.company.clinics.findIndex(
                            el => el.id === this.appointment.clinic_id
                        ) !== -1
                    );
                });
        },
        hasInsurancePolicies() {
            return this.activeInsurancePolicies.length != 0;
        },
        hasLegalEntity() {
            return !this.missedPatient && this.patient.legal_entity_id != null;
        },
        hasAccessActsAndAccounts() {
            return this.$can("acts-and-payments.view-acts-and-payments");
        },
        hasActiveAmbulanceCall() {
            return !!this.activeAmbulanceCall;
        }
    },
    watch: {
        activeCard(val) {
            if (!_.isNull(val) && !this.isPatientActive(val)) {
                this.$discountData.disabled = true;
            } else {
                this.$discountData.disabled = false;
            }

            this.$emit("discountSelected", [val, this.refreshDiscountType]);
        },
        activeInsurancePolicy(val) {
            this.$emit("insuranceSelected", val);
        },
        activeLegalEntity(val) {
            this.$emit("legalEntitySelected", val);
        },
        activeAmbulanceCall(val) {
            this.$emit("ambulanceCall", val);
        }
    },
    mounted() {
        this.initData();
    },
    methods: {
        lastDocument(type, appointment, typeAction = "print") {
            if (type === "acts") {
                if (typeAction === "download") {
                    return this.lastAct(appointment) && this.canDownload(type);
                }
                return this.lastAct(appointment);
            } else {
                if (typeAction === "download") {
                    return (
                        this.lastReceipt(appointment) && this.canDownload(type)
                    );
                }
                return this.lastReceipt(appointment);
            }
        },
        canDownload(type) {
            if (type === "acts") {
                return this.$can("acts-and-payments.download-acts");
            }
            return this.$can("acts-and-payments.download-payments");
        },
        lastAct(appointment) {
            const lastAct = this.getLastAct(appointment);
            if (lastAct) {
                return true;
            }
            return false;
        },
        lastReceipt(appointment) {
            const lastAct = this.getLastReceipt(appointment);
            if (lastAct) {
                return true;
            }
            return false;
        },
        getLastAct(appointment) {
            if (appointment.latest_act !== null) {
                return appointment.latest_act;
            }
            return null;
        },
        getLastReceipt(appointment) {
            if (appointment.latest_payment !== null) {
                return appointment.latest_payment;
            }
            return null;
        },
        initData() {
            this.clearData();

            if (this.isNew) {
                this.onCreateAppointment();
            } else {
                this.onEditAppointment();
            }
        },
        addAllCards() {
            let cards = this.getCards();

            cards.forEach(card => {
                if (this.isPatientActive(card)) {
                    this.addedCards.push(card);
                }
            });
        },
        setPatient(patient) {
            this.previousPatient = this.patient;
            this.patient = patient;
            if (
                !_.isNull(this.previousPatient) &&
                !_.isNull(this.patient) &&
                this.patient.id !== this.previousPatient.id
            ) {
                this.removeDiscountCard();
            }
            this.$nextTick(() => this.initData());
        },
        handleCommand(command) {
            this[command]();
        },
        setDiscountCard(data) {
            this.refreshDiscountType = !data.refreshDiscountType
                ? 0
                : data.refreshDiscountType;
            if (this.addedCards.length !== 0) {
                this.activeCard = null;
                this.activeCard = this.addedCards[data.index];
            }
        },
        addLegalEntity() {
            this.activeLegalEntity = this.patient.legal_entity;
        },
        castActiveCards() {
            let cards = _.orderBy(
                this.patient.issued_discount_cards,
                "type.priority",
                "asc"
            );
            let cardsForAutoApply = [];

            cards.forEach(card => {
                if (this.cardIsInvalid(card)) {
                    return;
                }

                if (this.isPatientActive(card)) {
                    this.activeCards.push(card);

                    if (!card.type.dont_auto_add_to_appointment) {
                        cardsForAutoApply.push(card);
                    }
                }
            });

            if (cardsForAutoApply.length && !this.cardSelected) {
                this.activeCard = cardsForAutoApply[0];
                this.addedCards.push(cardsForAutoApply[0]);
                this.cardSelected = true;
            }
        },
        clearData() {
            this.activeCards = [];
            this.addedCards = [];
            this.cardSelected = false;
            this.refreshDiscountType = 0;
            this.activeInsurancePolicy = null;
            this.activeLegalEntity = null;

            this.$discountData.discountCard = null;
            this.$discountData.oldDiscountCard = null;
            this.$discountData.refreshDiscountType = 0;
            this.$discountData.disabled = false;
            this.$discountData.reload.service = false;
            this.$discountData.reload.analysis = false;
            this.$discountData.firstCalcDiscountCard.service = true;
            this.$discountData.firstCalcDiscountCard.analysis = true;
        },
        onCreateAppointment() {
            if (
                _.isEmpty(this.patient) ||
                (this.patient.issued_discount_cards &&
                    this.patient.issued_discount_cards.length === 0)
            ) {
                this.activeCard = null;
                return;
            }

            this.refreshDiscountType = 1;
            this.castActiveCards();
            this.addAllCards();
        },
        onEditAppointment() {
            this.setInsurancePolicy();
            this.setLegalEntity();
            this.setAmbulanceCall();

            if (this.patient.issued_discount_cards.length === 0) {
                this.activeCard = null;
                return;
            }

            if (!_.isEmpty(this.patient) && !_.isNull()) this.setActiveCard();
            this.castActiveCards();
            this.addAllCards();

            this.$nextTick(() =>
                this.setDiscountCard({ index: 0, refreshDiscountType: 2 })
            );
        },
        setAmbulanceCall() {
            this.activeAmbulanceCall = this.appointment.ambulance_call
                ? new AmbulanceCall(this.appointment.ambulance_call)
                : null;
        },
        setActiveCard() {
            if (this.appointment.discount_card_id) {
                let card = this.patient.issued_discount_cards.find(item => {
                    return this.appointment.discount_card_id == item.id;
                });

                if (card) {
                    this.addedCards.push(card);
                    this.cardSelected = true;
                }
            }
        },
        setInsurancePolicy() {
            if (this.appointment.insurance_policy_id) {
                let policy = this.patient.insurance_policies.find(item => {
                    return this.appointment.insurance_policy_id == item.id;
                });

                if (policy) {
                    this.activeInsurancePolicy = policy;
                }
            }
        },
        setLegalEntity() {
            if (this.appointment.legal_entity_id) {
                if (this.hasLegalEntity) {
                    this.addLegalEntity();
                }
            }
        },
        cardIsInvalid(card) {
            return (
                card.expires < this.appointment.date ||
                card.valid_from > this.appointment.date
            );
        },
        isPatientActive(card) {
            return card.patients.find(patient => {
                return (
                    patient.patient_id == this.patient.id &&
                    patient.disabled == false
                );
            });
        },
        getCards() {
            if (
                this.patient &&
                this.patient.issued_discount_cards.length != 0
            ) {
                return this.patient.issued_discount_cards.filter(card => {
                    let index = this.addedCards.findIndex(
                        added => added.id == card.id
                    );
                    return index === -1 && !this.cardIsInvalid(card);
                });
            }
        },
        reloadDiscountCard() {
            if (this.activeCard) {
                this.$discountData.reload.service = true;
                this.$discountData.reload.analysis = true;
                this.$emit("discountSelected", [
                    this.activeCard,
                    CONSTANTS.DISCOUNT_TYPE.RELOAD
                ]);
            }
        },
        removeDiscountCard() {
            this.$discountData.reload.service = true;
            this.$discountData.reload.analysis = true;
            this.addedCards = [];
            this.cardSelected = false;
            this.refreshDiscountType = 1;
            this.activeCard = null;
        },
        addDiscountCards() {
            if (this.discountDisabled) {
                return false;
            }

            this.$modalComponent(
                AddCard,
                {
                    cards: this.getCards(),
                    patient: this.patient
                },
                {
                    cancel: dialog => {
                        dialog.close();
                    },
                    selected: (dialog, card) => {
                        dialog.close();
                        this.addedCards.push(card);
                        this.$nextTick(() =>
                            this.setDiscountCard({
                                index: 0,
                                refreshDiscountType: this.isNew ? 1 : 2
                            })
                        );
                        this.cardSelected = true;
                    }
                },
                {
                    header: __("Дисконтные карты в записи пациента"),
                    width: "1034px"
                }
            );
        },
        addInsurancePolicy() {
            if (!this.hasInsurancePolicies) {
                return false;
            }

            this.$modalComponent(
                AddInsurancePolicy,
                {
                    policies: this.activeInsurancePolicies
                },
                {
                    cancel: dialog => {
                        dialog.close();
                    },
                    selected: (dialog, policy) => {
                        dialog.close();
                        this.activeInsurancePolicy = policy;
                    }
                },
                {
                    header: __("Добавить полис к записи"),
                    width: "595px"
                }
            );
        },
        removeInsurancePolicy() {
            this.activeInsurancePolicy = null;
        },
        removeLegalEntity() {
            this.activeLegalEntity = null;
        },
        removeActiveAmbulanceCall() {
            this.activeAmbulanceCall = null;
        },
        addProvidingServices() {
            this.typeDocument = "acts";
            this.addAppointmentToConstruct();
        },
        addInvoicePayment() {
            this.typeDocument = "payment";
            this.addAppointmentToConstruct();
        },
        addAppointmentToConstruct() {
            var model = new Appointment({ id: this.appointment.id });
            model
                .fetch(["appointment_services", "latest_act", "latest_payment"])
                .then(() => {
                    this.addClinicToConstruct(model);
                });
        },
        addClinicToConstruct(appointment) {
            let model = new Clinic({ id: appointment.clinic_id });
            model.fetch(["msp", "money_reciever"]).then(() => {
                this.createPDFProviding(
                    appointment,
                    model,
                    appointment.patient
                );
            });
        },
        createPDFProviding(appointment, clinic, patient) {
            this.getRecievers(appointment, clinic).then(recievers => {
                let rawHTML;
                let index = 1;
                let componentHTML;
                let documentData = [];
                let moneyReciverRepo = new MoneyRecieverRepository();
                printer.overrideSettings(APPOINTMENT_DOCUMENT_SETTINGS);
                moneyReciverRepo.fetch({id: recievers.map(item => item.reciever_id)}).then(response => {
                    recievers.forEach(reciever => {
                        let moneyReciver = response.rows.find(item => item.id == reciever.reciever_id);
                        if (this.typeDocument === "payment") {
                            componentHTML = printer.getComponentHtml(PaymentInvoice, {
                                appointment: appointment,
                                clinic: clinic,
                                patient: patient,
                                reciever: moneyReciver,
                                services: reciever.services,
                                index: index,

                            });
                        } else {
                            componentHTML = printer.getComponentHtml(ProvidingService, {
                                appointment: appointment,
                                clinic: clinic,
                                patient: patient,
                                reciever: moneyReciver,
                                services: reciever.services,
                                index: index,
                            });
                        }
                        rawHTML = printer.print(componentHTML, true);
                        let doc = new DOMParser().parseFromString(rawHTML, "text/html");
                        let preDocumentData = {};
                        preDocumentData.document = printer.getDocWindow().document;
                        preDocumentData.document.body.innerHTML = "";
                        preDocumentData.document.body.appendChild(doc.body);
                        documentData.push(preDocumentData);
                        printer.destroyIframe();

                        index++;
                    });
                     this.logAdvisoryPrint(
                        documentData,
                        this.typeDocument,
                        appointment,
                        patient
                    );

                });
            });
        },
        logAdvisoryPrint(html, type, appointment, patient) {
            multiPagesToPdf(html)
                .then((blob) => {
                    let date = this.$moment().format("YYYY-MM-DD");
                    let name;
                    if (type === "acts") {
                        name = `${__('Акт выполненных работ')} - ${date}.pdf`;
                    } else {
                        name = `${__('Счет на оплату')} - ${date}.pdf`;
                    }
                    let request = fileLoader.upload(blob, name);
                    let showDownload = this.lastDocument(type, appointment, "download");
                    let showPrint = this.lastDocument(type, appointment);
                    return request.promise()
                        .then(response => {
                            this.$modalComponent(
                                AppointmentsFileViews,
                                {
                                    file: response.data,
                                    appointment: appointment,
                                    type: type,
                                    patient: patient
                                },
                                {
                                    successCreate: (dialog, typeDocument) => {
                                        dialog.close();
                                        this.$info(__('{type} успешно создан', {type: typeDocument}));
                                    }
                                },
                                {
                                    headerAddon: {
                                        component: FileViewerHeader,
                                        eventListeners: {
                                            downloadFile: dialog => {
                                                dialog
                                                    .getTopComponent()
                                                    .downloadFile();
                                            },
                                            print: dialog => {
                                                dialog
                                                    .getTopComponent()
                                                    .print();
                                            }
                                        },
                                        props: {
                                            showPrint: showPrint,
                                            showDownload: showDownload
                                        }
                                    }
                                }
                            );
                        });
                })
                .catch((e) => {
                    console.log(e);
                    this.$error(__('Не удалось сохранить копию консультативного заключения'));
                });
        },
        addAmbulanceCall() {
            if (this.hasActiveAmbulanceCall) {
                return false;
            }

            this.$modalComponent(
                AmbulanceCallCreate,
                {
                    ambulance_call: this.activeAmbulanceCall,
                    appointment: this.appointment,
                    patient: this.patient
                },
                {
                    cancel: dialog => {
                        dialog.close();
                    },
                    created: (dialog, ambulanceCall) => {
                        dialog.close();
                        this.activeAmbulanceCall = ambulanceCall;
                    }
                },
                {
                    header: __("Вызов скорой помощи"),
                    width: "595px"
                }
            );
        },
        editAmbulanceCall() {
            this.$modalComponent(
                AmbulanceCallEdit,
                {
                    ambulance_call: this.activeAmbulanceCall,
                    appointment: this.appointment,
                    patient: this.patient
                },
                {
                    cancel: dialog => {
                        dialog.close();
                    },
                    edited: (dialog, ambulanceCall) => {
                        dialog.close();
                        this.activeAmbulanceCall = ambulanceCall;
                    }
                },
                {
                    header: __("Вызов скорой помощи"),
                    width: "595px"
                }
            );
        },
        getRecievers(appointment, clinic) {
            let specializations = _.uniq(appointment.services.map(item => item.specialization.id));
            let repo = new SpecializationRepository();
            let recievers = [];
            let data = repo.fetch({
                clinic_id: appointment.clinic_id,
                id: specializations
            }, null, ['clinics.money_reciever']).then(response => {
                response.rows.forEach(row => {
                    let moneyReciever = row.clinics.find(clinic => {
                        return (clinic.clinic_id === appointment.clinic_id && !_.isVoid(clinic.money_reciever_id));
                    });

                    let recieverID = moneyReciever? moneyReciever.money_reciever_id: clinic.money_reciever.id;
                    let index = recievers.findIndex(reciever => reciever.reciever_id === recieverID);
                    
                    if (index === -1) {
                        recievers.push({
                            reciever_id: recieverID,
                            is_clinic_reciever: _.isVoid(moneyReciever),
                            services: this.getServicesByReciever(appointment, row.id)
                        });
                    } else {
                        recievers[index].services.push(...this.getServicesByReciever(appointment, row.id));
                    }
                });
                return recievers;
            });
            return data.then(recievers => {
                return Promise.resolve(recievers);
            });

        },
        getServicesByReciever(appointment, specialization_id) {
            let services = appointment.services.filter(service => service.specialization.id === specialization_id);
            return [
                ...services.map(service => {
                    if (!_.isEmpty(service.items)) {
                        let analysis = _.head(service.items);
                        return this.makeRow(analysis.name, analysis.quantity, analysis.cost, analysis.discount);
                    } else {
                         return this.makeRow(service.name, service.quantity, service.cost, service.discount);
                    }
                })
            ];
        },
        makeRow(name, qty, cost, discount) {
            let price = (1 * discount === 100) ? 0 : 100 * cost / (100 - discount);
            let discountSum = price - cost;
            let singlePrice = price / qty;
            return {
                name,
                qty: parseInt(qty),
                price: this.$formatter.numberFormat(Math.round(singlePrice)),
                fullPrice: this.$formatter.numberFormat(Math.round(price)),
                discount: this.$formatter.numberFormat(Math.round(discountSum)),
                cost: this.$formatter.numberFormat(Math.round(cost)),
            };
        }
    }
};
</script>
