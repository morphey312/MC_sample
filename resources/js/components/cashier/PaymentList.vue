<template>
    <page
        :title="__('Приход')"
        type="flex"
        v-loading="loading"
        :element-loading-text="__('Генерация отчета...')">
        <template slot="header-addon">
            <div class="buttons">
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
                    :primary-clinic-id="primaryClinicId"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                :label="__('Приход')"
                name="payments" >
                <section class="pt-0 shrinkable">
                    <payment-list
                        ref="table"
                        :filters="filters"
                        @selection-changed="setActiveItem"
                        @loaded="loaded"
                        @header-filter-updated="syncFilters"
                        @show-patient-payments="showPatientPayments">
                        <template slot="buttons">
                            <div class="buttons">
                                <form-button 
                                    :text="__('Экспорт в Excel')"
                                    icon="download"
                                    @click="exportExcel" />
                                <form-button 
                                    v-if="$canAccess('payments')"
                                    :disabled="activeItem === null"
                                    :text="__('Операции')"
                                    icon="menu-marketing"
                                    @click="showLog" />
                                <form-button 
                                    v-if="$canAccess('patient-cabinet')"
                                    :disabled="activeItem === null"
                                    :text="__('Кабинет пациента')"
                                    icon="catalogue"
                                    @click="goToPatientCabinet" />
                                <el-dropdown class="ml-10">
                                    <el-button>
                                        {{ __('Еще') }}
                                    </el-button>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item v-if="$can('payments.compensation')">
                                            <el-button
                                                type="text"
                                                :disabled="activeItem === null || isIncome()"
                                                @click="updateService">
                                                {{ __('Не должник') }}
                                            </el-button>
                                        </el-dropdown-item>
                                        <el-dropdown-item v-if="$can('payments.operations')">
                                            <el-button
                                                type="text"
                                                @click="showOperations">
                                                {{ __('Операции по средствам') }}
                                            </el-button>
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </el-dropdown>
                            </div>
                            <div class="table-summary">
                                <b>{{ __('Итого сумма:') }} {{ $formatter.numberFormat(periodDifference) }} {{ __('грн') }}</b>
                            </div>
                        </template>
                    </payment-list>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Предоплаты')"
                name="prepayments">
                <section class="darkgrey-cap shrinkable pt-0">
                <prepayment-list
                    ref="prepayments"
                    :filters="filters"
                    :payment-methods="paymentMethods"
                    @loaded="getPrepaymentTotal"
                    @selection-changed="setActiveItem"
                    @header-filter-updated="syncFilters"
                    @show-patient-payments="showPatientPayments" >
                    <template slot="buttons">
                        <div class="buttons" >
                            <el-button
                                v-if="$canUpdate('payments')"
                                :disabled="activeItem === null || !$canManage('payments.update', [activeItem.clinic_id])"
                                @click="editPrepayment">
                                {{ __('Редактировать') }}
                            </el-button>
                            <el-button v-if="$canAccess('patient-cabinet')"
                                :disabled="activeItem === null"
                                @click="goToPatientCabinet">
                                {{ __('Перейти в личный кабинет') }}
                            </el-button>
                            <el-button
                                :disabled="activeItem === null"
                                @click="showPrepaymentLog">
                                {{ __('Операции') }}
                            </el-button>
                        </div>
                        <div class="table-summary">
                            {{ __('Оплачено:') }} {{ totalPrepaid }} / {{ __('Остаток:') }} {{ totalPrepaidRest }}
                        </div>
                    </template>
                </prepayment-list>
            </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('checkbox-checks.access')"
                :lazy="true"
                :label="__('Чеки')"
                name="checks">
                <section class="darkgrey-cap shrinkable pt-0">
                    <ChecksList
                        ref="checks"
                        :filters="filters"
                        @selection-changed="setActiveItem"
                        @header-filter-updated="syncFilters"
                    >
                        <template slot="buttons">
                            <div class="buttons" >
                                <el-button
                                    v-if="$canUpdate('payments')"
                                    :disabled="activeItem === null || !$canManage('payments.update', [activeItem.clinic_id])"
                                    @click="editPrepayment">
                                    {{ __('Редактировать') }}
                                </el-button>
                            </div>
                        </template>
                    </ChecksList>
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import PaymentFilter from './payments-list/Filter.vue';
import PaymentList from './payments-list/List.vue';
import PrepaymentList from './payments-list/Prepayments.vue';
import ChecksList from './checkbox-checks/Checks'
import PatientPaymentList from './payments/form/PatientPayments.vue';
import ToggleModalFilter from '@/components/patients/search/ToggleFilter.vue';
import PaymentLog from '@/components/action-log/Payment.vue';
import MoneyTransferLog from '@/components/action-log/MoneyTransfer.vue';
import PrepaymentLog from '@/components/action-log/patient/Prepayment.vue';
import PrepaymentEditForm from '@/components/cashier/payments/form/prepayment/FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import CONSTANTS from '@/constants';
import PaymentRepository from '@/repositories/payment';
import * as paymentGenerator from './payments-list/generators/payments';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import ServiceDebtMixin from '@/mixins/payment/service-debt';

export default {
    mixins: [
        ManageMixin,
        ExportXLSXMixin,
        ServiceDebtMixin,
    ],
    components: {
        PaymentFilter,
        PaymentList,
        PrepaymentList,
        ChecksList
    },
    data() {
        return {
            activeTab: 'payments',
            displayFilter: true,
            paymentMethods: [],
            repo: new PaymentRepository(),
            periodDifference: 0,
            reportRepository: new PaymentRepository(),
            loading: false,
            fileGenerator: paymentGenerator,
            primaryClinicId: this.getUserClinics(),
            totalPrepaid: 0,
            totalPrepaidRest: 0,
        }
    },
    watch: {
        activeTab() {
            this.resetTablesActiveItem();
        },
    },
    methods: {
        getUserClinics() {
            return this.$store.state.user.primaryClinicId ? [this.$store.state.user.primaryClinicId] : this.$store.state.user.clinics
        },
        resetTablesActiveItem() {
            this.activeItem = null;

            if(this.$refs.table) {
                this.$refs.table.unselectAll();
            }

            if(this.$refs.prepayments) {
                this.$refs.prepayments.unselectAll();
            }

            if(this.$refs.checks) {
                this.$refs.checks.unselectAll();
            }
        },
        loaded() {
            this.getTotal();
            this.refreshed();
        },
        getTotal() {
            this.repo.getTotal(this.filters).then(response => {
                if (response && response.total != undefined) {
                    this.periodDifference = (response.total);
                } else {
                    this.periodDifference = 0;
                }
            })
        },
        getPrepaymentTotal() {
            let totalPrepaid = 0;
            let totalPrepaidRest = 0;
            if (this.$refs.prepayments) {
                let table = this.$refs.prepayments.getManageTable(); 
                let rows = table.getData();
                rows.forEach(row => {
                    totalPrepaid += Number(row.payment.payed_amount);
                    totalPrepaidRest += (row.used ? 0 : Number(row.amount));
                });
            }
            this.totalPrepaid = totalPrepaid;
            this.totalPrepaidRest = totalPrepaidRest;
        },
        getData() {
            let table = this.getManageTable();
            return table ? table.getData() : [];
        },
        getDefaultFilters() {
            let today = this.$moment().format('YYYY-MM-DD');
            return {
                clinic: this.getUserClinics(),
                createdStart: today,
                createdEnd: today,
            }
        },
        showLog() {
            this.$modalComponent(PaymentLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения платежа'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        showOperations(){
            this.$modalComponent(MoneyTransferLog, {
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История выемки и передачи денежных средств'),
                width: '1000px',
                customClass: 'no-footer',
            });

        },
        showPatientPayments(patient) {
            this.$modalComponent(PatientPaymentList, {
                patient_id: patient.id,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    dialog.close();
                    this.getCreatePaymentForm(patient);
                },
            }, {
                header: __('Платежи пациента:') + ' ' + patient.name,
                width: '1270px',
                headerAddon: {
                    component: ToggleModalFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        goToPatientCabinet() {
            let routeData = this.$router.resolve({name: 'patient-cabinet', params: {patientId: this.activeItem.patient.id}});
            window.open(routeData.href, '_blank');
        },
        isIncome() {
            return this.activeItem.type === CONSTANTS.PAYMENT.TYPES.INCOME;
        },
        updateService() {
            this.updateServiceDebt(this.activeItem.service_id, this.activeItem.service.name, () => this.refresh());
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
        showPrepaymentLog() {
            this.$modalComponent(PrepaymentLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения предоплаты'),
                width: '900px',
                customClass: 'no-footer',
            });
        }
    }
}
</script>
