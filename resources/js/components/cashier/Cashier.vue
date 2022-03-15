<template>
    <div v-loading="loading">
        <page
            :title="getActiveShift"
            type="flex">
            <template slot="header-addon">
                <div class="buttons">
                    <a
                        v-if="$can('cashier-session-logs.update')"
                        href="#"
                        :disabled="!isCashier"
                        @click.prevent="callSessionAction">
                        <svg-icon :name="sessionIcon" class="icon-small icon-blue" :class="iconClass">
                            {{ sessionBtnText }}
                        </svg-icon>
                    </a>
                    <el-dropdown  @command="handleCommand" class="action-link">
                        <svg-icon name="report-alt" class="icon-small icon-blue">
                            {{ __('Кассы Сheckbox') }}<i class="el-icon-caret-bottom el-icon--right"></i>
                        </svg-icon>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item
                                v-for="cashbox in checkboxCashboxes"
                                :key="cashbox.id"
                                command="setActiveCashbox"
                                :payload="cashbox"
                            >
                                {{ cashbox.money_reciever_cashbox_name }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                command="checkboxSessionStart"
                            >
                                {{ __('Открыть кассу Checkbox') }}
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                    <a
                        v-if="$can('cash-transfers.update')"
                        href="#"
                        @click.prevent="addTokenMoney"
                        :disabled="actionsDisabled">
                        <svg-icon name="plus-alt" class="icon-small icon-blue">
                            {{ __('Добавить разменную монету') }}
                        </svg-icon>
                    </a>
                    <el-dropdown @command="handleCommand" class="action-link">
                        <svg-icon name="report-alt" class="icon-small icon-blue">
                            {{ __('РМК форма') }}<i class="el-icon-caret-bottom el-icon--right"></i>
                        </svg-icon>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item
                                v-if="$can('cash-transfers.update')"
                                command="extractMoney"
                                :disabled="actionsDisabled">
                                {{ __('Выемка денег из кассы') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="$can('cash-transfers.update')"
                                command="transferMoney"
                                :disabled="actionsDisabled">
                                {{ __('Передать деньги') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="$can('cashier-session-logs.update')"
                                command="zeroBill"
                                :disabled="actionsDisabled">
                                {{ __('Нулевой чек') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="$can('cashier-session-logs.update')"
                                command="xReport"
                                :disabled="actionsDisabled">
                                {{ __('X-отчет') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="$can('cashier-session-logs.update')"
                                command="zReport"
                                :disabled="actionsDisabled">
                                {{ __('Z-отчет') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                command="serviceReport"
                                :disabled="actionsDisabled">
                                {{ __('Отчет по услугам') }}
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                    <toggle-link
                        v-if="sessionActive"
                        v-model="displayFilter">
                        <svg-icon name="filter-alt" class="icon-small icon-blue">
                            {{ __('Фильтр') }}
                        </svg-icon>
                    </toggle-link>
                </div>
            </template>
            <template v-if="sessionActive">
                <drawer :open="displayFilter">
                    <section class="grey">
                        <payment-filter
                            ref="filter"
                            :initial-state="filters"
                            :cashier="cashier"
                            :payment-methods="paymentMethods"
                            @changed="syncListFilters"
                            @cleared="clearFilters" />
                    </section>
                </drawer>
            </template>
            <payments
                v-if="sessionActive"
                :filter="filters"
                :cashboxes="cashboxes"
                :checkbox-cashboxes="checkboxCashboxes"
                :cashier="cashier"
                :payment-methods="paymentMethods"
                :active-shift="activeShift"
                @header-filter-updated="syncFilters" />
            <template v-if="!sessionActive">
                <start-screen
                    v-if="$can('cashier-session-logs.update')"
                    :is-cashier="isCashier"
                    @session-start="sessionStart"
                    @checkbox-session-start="checkboxSessionStart"/>
            </template>
        </page>
    </div>
</template>
<script>
import PaymentFilter from './payments/Filter.vue';
import Payments from './payments/Payments.vue';
import StartScreen from './payments/StartScreen.vue';
import ShiftManager from '@/services/cashier/shift-manager';
import Employee from '@/models/employee';
import printer from '@/services/print';
import checkbox from "@/services/checkbox";
import CahierModalMixin from './mixins/modal';
import CheckboxShiftRepository from "@/repositories/cashbox/shift";
import lts from '@/services/lts';


export default {
    mixins: [
        CahierModalMixin,
    ],
    components: {
        PaymentFilter,
        Payments,
        StartScreen,
    },
    data() {
        return {
            currentCashbox: {},
            activeShift: null,
            displayFilter: true,
            filters: {},
            shiftManager: ShiftManager,
            loading: false,
            cashier: null,
            isCashier: this.$store.state.user.isCashier,
            cashboxes: [],
            checkboxCashboxes: [],
            unActiveCheckboxCashboxes: [],
            paymentMethods: [],
        }
    },
    computed: {
        getActiveShift() {
            return this.activeShift ? this.activeShift.money_reciever_cashbox_name : "Касса";
        },
        sessionActive() {
            return this.shiftManager.id !== null;
        },
        sessionBtnText() {
            return this.sessionActive === true ? __('Закрыть смену') : __('Открыть смену');
        },
        sessionIcon() {
            return this.sessionActive === true ? 'switch-off-alt' : 'play-alt';
        },
        iconClass() {
            return this.sessionActive === true ? 'icon-red' : 'icon-blue';
        },
        actionsDisabled() {
            return this.sessionActive === false || !this.isCashier;
        },
    },
    beforeMount() {
        this.initCashier();
        this.clearFilters();
        this.getCashboxes();
        this.getCheckboxes(false);
        this.getCheckboxes(true);
    },
    mounted() {
        this.$eventHub.$on('broadcast.cashbox_updated', ({data}) => {
            this.updateCashboxBalances(data);
        });

    },
    beforeDestroy() {
        this.$eventHub.$off('broadcast.cashbox_updated');
    },
    methods: {
        initCashier() {
            if (this.isCashier) {
                this.cashier = new Employee({
                    id: this.$store.state.user.employee_id,
                    full_name: this.$store.state.user.full_name,
                    clinic_id: this.$store.state.user.cashierClinicId,
                    is_cashier: this.$store.state.user.isCashier,
                });
            } else {
                this.$error(__('Пользователь не является кассиром! Зайдите в приход'));
            }
        },
        getCashboxes() {
            if (this.cashier === null) {
                return;
            }
            this.cashier.fetchCashboxes({cashbox_clinic: this.cashier.clinic_id, enabled_method: true}).then((response) => {
                this.cashboxes = response;
                this.paymentMethods = this.getPaymentMethods();
            });
        },
        getCheckboxes(isActive) {
            new CheckboxShiftRepository().fetch({
                user: this.$store.state.user.employee.id,
                employee: isActive ? this.$store.state.user.employee.id : null,
                isActive: isActive,
            }, [], ['money_reciever_cashbox']).then((res) => {
                if (isActive) {
                    this.checkboxCashboxes = res.rows;
                    lts.cashierCheckboxCashboxes = res.rows;
                    this.activeShift = res.rows.length !== 0 ? res.rows[0] : null;
                } else {
                    this.unActiveCheckboxCashboxes = res.rows;
                }
            });
        },
        setActiveCashbox(shift) {
            this.activeShift = shift;
        },
        getPaymentMethods() {
            let list = [];
            this.cashboxes.forEach((box) => {
                list.push({
                    id: box.payment_method_id,
                    value: box.payment_method.name,
                })
            });
            return list;
        },
        callSessionAction() {
            return this.sessionActive ? this.sessionEnd() : this.sessionStart();
        },
        sessionStart() {
            if (!this.isCashier) {
                return this.$error(__('Смену может открыть только кассир'));
            }

            if (!this.sessionActive) {
                this.loading = true;
                this.shiftManager.sessionStarted().then(() => {
                    this.getCheckboxes(true);
                    this.loading = false;
                });
            }
        },
        sessionEnd() {
            this.$confirm(__('Вы уверены что хотите закрыть смену?'),
                () => {
                    if(this.checkboxCashboxes.length > 1) {
                        this.showCheckboxEndSessionModal();
                    } else if(this.checkboxCashboxes.length === 1) {
                        this.endCheckboxSession(this.checkboxCashboxes[0]);
                    } else {
                        this.shiftManager.sessionEnded().then(() => {
                            this.activeShift = null;
                            this.loading = false;
                        })
                    }
                })
        },
        endCheckboxSession(cashbox, isLastCashbox = true) {
            this.loading = true;
            checkbox.closeShifts(cashbox.access_token).then(() => {
                cashbox.access_token = null;
                cashbox.employee_id = null;
                cashbox.save().then(() => {
                    let index = this.checkboxCashboxes.indexOf(cashbox);

                    if (index !== -1) {
                        this.checkboxCashboxes.splice(index,1);
                        this.activeShift = this.checkboxCashboxes.length ? this.checkboxCashboxes[0] : null;
                    }

                    this.getCheckboxes(false);
                    this.loading = false;
                }).catch((e) => {
                    this.loading = false;
                    this.$displayErrors(e);
                })
            }).catch((e) => {
                cashbox.access_token = null;
                cashbox.save();
                this.loading = false;
                this.$error(e.response.data.message);
            });

            if (isLastCashbox) {
                this.shiftManager.sessionEnded().then(() => {
                    delete lts.cashierCheckboxCashboxes;
                });
            }
        },
        updateCashboxBalances(data) {
            if (!_.isEmpty(data) && data.id && data.cashier_id) {
                let index = this.cashboxes.findIndex((box) => {
                    return data.id == box.id && data.cashier_id == box.cashier_id;
                });

                if (index !== -1) {
                    let cashbox = {...this.cashboxes[index]};
                    cashbox.expense = this.$formatter.numberFormat(data.expense);
                    cashbox.income = this.$formatter.numberFormat(data.income);
                    cashbox.initial_amount = this.$formatter.numberFormat(data.initial_amount);
                    this.cashboxes.splice(index, 1, cashbox);
                }
            }
        },
        handleCommand(command,payload = null) {
            if (_.isFunction(this[command])) {
                return payload.$attrs.payload ? this[command](payload.$attrs.payload) : this[command]();
            }
            return false;
        },
        syncListFilters(updates) {
            this.changeFilters(updates);
        },
        changeFilters(filters) {
            this.filters = filters;
        },
        clearFilters() {
            this.filters = this.getDefaultFilters();
        },
        syncFilters(updates) {
            this.$refs.filter.sync(updates);
        },
        getDefaultFilters() {
            let today = this.$moment().format('YYYY-MM-DD');

            return {
                cashier: this.cashier.id,
                createdStart: today,
                createdEnd: today,
            }
        },
    }
}
</script>
