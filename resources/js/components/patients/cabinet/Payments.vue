<template>
    <page
        :title="__('Оплаты: {name}', {name: patient.full_name})"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <a
                    href="#"
                    @click.prevent="showSignalRecord">
                    <svg-icon
                        name="report-alt"
                        class="icon-small icon-blue">
                        {{ __('Сигнальные обозначения') }}
                    </svg-icon>
                </a>
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey">
                <payment-filter
                    ref="filter"
                    :initial-state="filters"
                    :patient="patient"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                v-if="$can('patient-cabinet.payments-payments')"
                :lazy="true"
                :label="__('Оплаты')"
                name="payments" >
                <section class="pt-0 shrinkable">
                    <payment-list
                        ref="table"
                        :filters="filters"
                        @selection-changed="setActiveItem"
                        @header-filter-updated="syncFilters">
                        <div class="buttons" slot="buttons">
                            <el-button
                                :disabled="activeItem == null || activeItem.appointment_id === null"
                                @click="showAppointment">
                                {{ __('Показать запись') }}
                            </el-button>
                        </div>
                    </payment-list>
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('patient-cabinet.payments-debts')"
                :lazy="true"
                :label="__('Долги')"
                name="debts" >
                <section class="pt-0 shrinkable">
                    <debt-list
                        ref="debtsTable"
                        :filters="filters"
                        @selection-changed="setActiveItem" >
                        <div class="buttons" slot="buttons">
                            <el-button
                                :disabled="activeItem == null"
                                @click="showLog()">
                                {{ __('Операции') }}
                            </el-button>
                            <el-button
                                v-if="$can('payments.compensation')"
                                :disabled="activeItem == null"
                                @click="updateService">
                                {{ __('Не должник') }}
                            </el-button>
                            <el-button
                                :disabled="activeItem == null || activeItem.appointment_id === null"
                                @click="showAppointment">
                                {{ __('Показать запись') }}
                            </el-button>
                        </div>
                    </debt-list>
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('patient-cabinet.payments-not-debts')"
                :lazy="true"
                :label="__('Не должник')"
                name="not_debts" >
                <section class="pt-0 shrinkable">
                    <not-debt-list
                        ref="notDebtsTable"
                        :filters="filters"
                        @selection-changed="setActiveItem" >
                        <div class="buttons" slot="buttons">
                            <el-button
                                :disabled="activeItem == null"
                                @click="showLog()">
                                {{ __('Операции') }}
                            </el-button>
                            <el-button
                                v-if="$can('payments.compensation')"
                                :disabled="activeItem == null"
                                @click="updateServiceToDebt">
                                {{ __('Должник') }}
                            </el-button>
                            <el-button
                                v-if="$canUpdate('appointments')"
                                :disabled="activeItem == null || activeItem.appointment_id === null"
                                @click="showAppointment">
                                {{ __('Показать запись') }}
                            </el-button>
                        </div>
                    </not-debt-list>
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('patient-cabinet.payments-deposits')"
                :lazy="true"
                :label="__('Аванс')"
                name="deposits" >
                <section class="pt-0 shrinkable">
                    <deposit-list
                        ref="depositsTable"
                        :filters="filters"
                        @selection-changed="setActiveItem" >
                        <div class="buttons" slot="buttons" />
                    </deposit-list>
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('patient-cabinet.payments-prepayments')"
                :lazy="true"
                :label="__('Предоплаты')"
                name="prepayments" >
                <section class="pt-0 shrinkable">
                    <prepayments-list
                        ref="prepaymentsTable"
                        :filters="filters"
                        @selection-changed="setActiveItem" >
                        <div class="buttons" slot="buttons">
                            <el-button
                                :disabled="activeItem == null"
                                @click="editPrepayment">
                                {{ __('Редактировать') }}
                            </el-button>
                        </div>
                    </prepayments-list>
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import AppointmentManager from '@/components/appointments/mixin/manager';
import PaymentList from './payments/PaymentList.vue';
import DebtList from './payments/DebtList.vue';
import NotDebtList from './payments/NotDebtList.vue';
import DepositList from './payments/DepositList.vue';
import PrepaymentsList from './payments/PrepaymentsList.vue';
import PaymentFilter from './payments/Filter.vue';
import ManageMixin from '@/mixins/manage';
import CabinetMixin from './mixins/cabinet';
import ServiceDebtMixin from '@/mixins/payment/service-debt';
import Appointment from '@/models/appointment';
import PrepaymentEditForm from '@/components/cashier/payments/form/prepayment/FormEdit.vue';
import ServiceDebtLog from "@/components/action-log/ServiceDebt.vue";
import CONSTANTS from "@/constants";

export default {
    mixins: [
        ManageMixin,
        CabinetMixin,
        ServiceDebtMixin,
        AppointmentManager
    ],
    components: {
        PaymentList,
        PaymentFilter,
        DebtList,
        NotDebtList,
        DepositList,
        PrepaymentsList,
    },
    data() {
        return {
            activeTab: 'payments',
            displayFilter: true,
            filterClinics: this.$store.state.user.clinics,
        };
    },
    watch: {
        activeTab() {
            this.resetTablesActiveItem();
        }
    },
    methods: {
        getFilterUid() {
            return false;
        },
        getDefaultFilters() {
            return {
                patient: this.patient.id,
                clinic: this.$store.state.user.clinics,
                ...(this.$store.state.user.isDoctor ? {
                    doctor_specialization: this.$store.state.user.specializations
                } : {}),
            };
        },
        refreshDebts() {
            if(this.$refs.debtsTable){
                this.$refs.debtsTable.refresh();
            }
            if(this.$refs.notDebtsTable){
                this.$refs.notDebtsTable.refresh();
            }
        },
        resetTablesActiveItem(){
            this.activeItem = null;

            if(this.$refs.debtsTable){
                this.$refs.debtsTable.unselectAll();
            }

            if(this.$refs.notDebtsTable){
                this.$refs.notDebtsTable.unselectAll();
            }

            if(this.$refs.table){
                this.$refs.table.unselectAll();
            }

            if(this.$refs.prepaymentsTable){
                this.$refs.prepaymentsTable.unselectAll();
            }
        },
        updateService() {
            this.updateServiceDebt(this.activeItem.id, this.activeItem.name, () => this.refreshDebts());
        },
        updateServiceToDebt() {
            this.updateServiceSetDebt(this.activeItem.id, this.activeItem.name, () => this.refreshDebts());
        },
        showAppointment() {
            let appointment = new Appointment({id: this.activeItem.appointment_id});
            appointment.fetch([
                'doctor',
                'clinic',
            ]).then(() => {
                this.makeDaySheetData(appointment, true).then(() => {
                    this.editAppointment((appointment) => {
                        this.daySheetData = {};
                    }, appointment);
                });
            });
        },
        showLog() {
            this.$modalComponent(ServiceDebtLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения признака "Должник" услуги') + this.addServiceName(),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        addServiceName() {
            if (this.activeItem.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES) {
                return this.$formatter.listFormat(this.activeItem.analysis_name);
            }
            return this.activeItem.name;
        },
        editPrepayment() {
            this.$modalComponent(PrepaymentEditForm, {
                item: this.activeItem,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, payment) => {
                    dialog.close();
                },
            }, {
                header: __('Редактировать предоплату'),
                width: '355px',
            });
        },
    },
};
</script>
