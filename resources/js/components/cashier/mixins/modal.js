import TokenMoneyModal from '../modals/TokenMoney.vue';
import ExtractMoneyModal from '../modals/ExtractMoney.vue';
import TransferMoneyModal from '../modals/TransferMoney.vue';
import CheckboxReportModal from "../modals/CheckboxReport";
import CashboxMixin from './cashbox';
import CreateCheckboxSession from "../checkbox-session/CreateCheckboxSession";
import checkbox from "@/services/checkbox";
import Shift from "../../../models/checkbox/shift";
import shiftManager from "../../../services/cashier/shift-manager";
import CloseCheckboxSession from "../modals/CloseCheckboxSession";


export default {
    mixins: [
        CashboxMixin,
    ],
    methods: {
        addTokenMoney() {
            if (this.actionsDisabled) {
                return false;
            }
            this.$modalComponent(TokenMoneyModal, {
                activeShift: this.activeShift,
                cashier: this.cashier,
                checkboxCashboxes: this.checkboxCashboxes,
                cashboxes: this.cashboxes,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog, model) => {
                    this.$info(__('Разменная монета успешно внесена'));
                    dialog.close();
                },
            }, {
                header: __('Добавить разменную монету'),
                width: '355px',
            });
        },
        xReport() {
            if (this.actionsDisabled) {
                return false;
            }

            if (this.activeShift instanceof Shift) {
                this.$modalComponent(CheckboxReportModal, {
                    reportType: 'xReport',
                    checkboxCashboxes: this.checkboxCashboxes,
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    created: (dialog) => {
                        this.$info(__('X-отчет успешно сформирован'));
                        dialog.close();
                    },
                }, {
                    header: __('X-отчет'),
                    width: '355px',
                });
            }
        },
        zReport() {
            if (this.actionsDisabled) {
                return false;
            }

            if (this.activeShift instanceof Shift) {
                this.$modalComponent(CheckboxReportModal, {
                    reportType: 'zReport',
                    activeShift: this.activeShift,
                    checkboxCashboxes: this.checkboxCashboxes,
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    created: (dialog) => {
                        this.$info(__('Z-отчет успешно сформирован'));
                        this.getCheckboxes(true);

                        if (!this.checkboxCashboxes.length) {
                            shiftManager.sessionEnded().then(() => {
                                this.activeShift = null;
                            });
                        }

                        dialog.close();
                    },
                }, {
                    header: __('Z-отчет'),
                    width: '355px',
                });
            }
        },
        extractMoney() {
            if (this.actionsDisabled) {
                return false;
            }

            this.$modalComponent(ExtractMoneyModal, {
                cashier: this.cashier,
                activeShift: this.activeShift,
                checkboxCashboxes: this.checkboxCashboxes,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog) => {
                    this.$info(__('Выемка успешно произведена'));
                    dialog.close();
                },
            }, {
                header: __('Выемка денег из кассы'),
                width: '355px',
            });
        },
        transferMoney() {
            if (this.actionsDisabled) {
                return false;
            }

            this.$modalComponent(TransferMoneyModal, {
                cashier: this.cashier,
                sourceId: this.getCashNonPrinterCashboxId(this.cashboxes),
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog) => {
                    this.$info(__('Перевод успешно сохранен'));
                    dialog.close();
                },
            }, {
                header: __('Передать деньги'),
                width: '355px',
            });
        },
        loginToCheckbox(credentials) {
            checkbox.login(credentials.login,credentials.password,credentials.cashbox_key).then(() => {
                this.sessionStart()
            }).catch(() => {
                this.$error(__('Ошибка'));
                this.loading = false
            })
        },
        checkboxSessionStart() {
            this.loading = true;
            this.$modalComponent(CreateCheckboxSession, {
                shifts:  this.unActiveCheckboxCashboxes,
                checkboxCashboxes: this.checkboxCashboxes,
            }, {
                close: (dialog) => {
                    this.loading = false;
                    dialog.close();
                },
                openSession: (dialog) => {
                    if (this.checkboxCashboxes.length < 1) {
                        this.sessionStart();
                    }
                    this.loading = false;
                    this.getCheckboxes(true);
                    this.getCheckboxes(false);
                    dialog.close();
                },
            }, {
                header: __('Откройте смену в Checkbox'),
                width: '900px',
            });
        },
        showCheckboxEndSessionModal() {
            this.loading = true;
            this.$modalComponent(CloseCheckboxSession, {
                checkboxCashboxes: this.checkboxCashboxes,
            }, {
                close: (dialog) => {
                    this.loading = false;
                    dialog.close();
                },
                endSession: (dialog,selectedShift) => {
                    this.endCheckboxSession(selectedShift, false);
                    dialog.close()
                },
            }, {
                header: __('Закройте смену в Checkbox'),
                width: '900px',
            });
        },
    }
}
