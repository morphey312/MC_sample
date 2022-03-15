<template>
    <manage-table
        v-loading="loading"
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :enable-pagination="true"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        @selection-changed="selectionChanged" >
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
        <template
            slot="amount"
            slot-scope="props"
        >
            {{ getAmount(props.rowData) }}
        </template>
        <template
            slot="check_type"
            slot-scope="props"
        >
            <a @click="showCheck(props.rowData)">
                {{ props.rowData.check_type }}
            </a>
        </template>
        <template
            slot="check.checkbox_check_id"
            slot-scope="props"
        >
            <a @click="downloadCheck(props.rowData)"
               v-if="props.rowData.check && props.rowData.check.checkbox_check_id">
                {{ props.rowData.check.checkbox_check_id }}
            </a>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import CONSTANTS from '@/constants';
import PaymentRepository from "@/repositories/payment";
import CheckRepository from "@/repositories/cashbox/check";
import printer from '@/services/print';
import CheckboxService from '@/services/checkbox';
import Payment from "../../../models/payment";

export default {
    props: {
        filters: Object,
        checkboxCashboxes: Array,
    },
    data() {
        return {
            loading: false,
            repository:  new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                return this.getChecks(filters, sort, scopes, page, limit);
            }),
            fields: [
                {
                    name: 'created',
                    title: __('Дата'),
                    width: '12%',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.datetimeFormat(val);
                    },
                },
                {
                    name: 'patient.name',
                    title: __('Пациент'),
                    width: '10%',
                },
                {
                    name: 'amount',
                    title: __('Сумма'),
                    width: '8%',
                },
                {
                    name: 'cashbox.name',
                    title: __('Форма оплаты'),
                    width: '8%',
                },
                {
                    name: 'money_reciever_cashbox_name',
                    title: __('Касса'),
                    width: '10%',
                },
                {
                    name: 'payment_destination.name',
                    title: __('Назначение'),
                    width: '8%',
                    dataClass: 'no-dash',
                },
                {
                    name: 'type',
                    title: __('Возврат'),
                    width: '8%',
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.boolToString((val === CONSTANTS.PAYMENT.TYPES.EXPENSE), '<span class="check-yes" />');
                    },
                },
                {
                    name: 'money_reciever_cashbox.money_reciever_name',
                    title: __('Получатель средств'),
                    width: '12%',
                },
                {
                    name: 'check.checkbox_check_id',
                    title: __('ID чека Checkbox'),
                    width: '10%',
                },
                {
                    name: 'check_type',
                    title: __('Тип'),
                    width: '10%',
                },
                {
                    name: 'cashier.name',
                    title: __('Кассир'),
                    width: '10%',
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                    width: '10%',
                },
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'cashier',
                'money_reciever_cashbox',
                'cashbox',
                'check',
                'service',
                'payment_destination',
                'money_reciever',
                'employee',
                'patient',
            ],
        };
    },
    methods: {
        getChecks(filters, sort, scopes, page) {
            let filter = _.cloneDeep(filters);
            filter.check_body = true;

            let paymentRepository = new PaymentRepository();
            let checkRepository = new CheckRepository();
            return paymentRepository.fetch(filter,sort,scopes,page,1000).then((payments) => {
                this.setPaymentsType(payments);
                return checkRepository.fetch(filter,sort,scopes,page,1000).then((checks) => {
                    this.setChecksType(checks.rows);
                    let rows = [
                        ...payments.rows,
                        ...checks.rows,
                    ]
                    rows = rows.sort((a,b) => {
                        return new Date(b.created) - new Date(a.created);
                    })
                    return Promise.resolve({
                        rows: rows
                    })
                });
            })
        },
        setPaymentsType(payments) {
            payments.rows.forEach((payment) => {
                payment.money_reciever_cashbox_name = payment.money_reciever_cashbox.name;
                payment.check_type = 'Чек';
            });
        },

        setChecksType(checks) {
            checks.forEach((check) => {
                switch (check.type) {
                case CONSTANTS.CHECKBOX_CHECKS.TYPE.Z_REPORT:
                    check.check_type = __('Z-отчет');
                    break;
                case CONSTANTS.CHECKBOX_CHECKS.TYPE.X_REPORT:
                    check.check_type = __('X-отчет');
                    break;
                case CONSTANTS.CHECKBOX_CHECKS.TYPE.EXTRACT:
                    check.check_type = __('Служебное изъятие');
                    break;
                case CONSTANTS.CHECKBOX_CHECKS.TYPE.TOKEN:
                    check.check_type = __('Служебное внесение');
                    break;
                default:
                    check.check_type = __('Чек');
                    break;
                }
            })
        },
        showCheck(check) {
            if (check.check && check.check.body) {
                printer.newPrinter().printRawHtml("<div style='margin-top: 0; width: 277px'>" + check.check.body + "</div>");
            }

            if (check.type === CONSTANTS.CHECKBOX_CHECKS.TYPE.TOKEN || check.type === CONSTANTS.CHECKBOX_CHECKS.TYPE.EXTRACT) {
                printer.newPrinter().printRawHtml("<div style='margin-top: 0; width: 277px'>" + check.body + "</div>");
            }

            if (check.type === CONSTANTS.CHECKBOX_CHECKS.TYPE.X_REPORT || check.type === CONSTANTS.CHECKBOX_CHECKS.TYPE.Z_REPORT) {
                printer.newPrinter().printRawHtml("<pre>" + check.body + "</pre>");
            }

            this.clearIframes();
        },
        clearIframes() {
            printer.deleteAllIframes();
        },
        getAmount(check) {
            if(check.type === CONSTANTS.PAYMENT.TYPES.EXPENSE || check.type === CONSTANTS.CHECKBOX_CHECKS.TYPE.EXTRACT) {
                return '-' + check.amount;
            }

            return check.amount
        },
        downloadCheck(payment) {
            this.loading = true;
            let paymentShift = this.checkboxCashboxes.length ? this.checkboxCashboxes[0] : null;
            if (paymentShift && paymentShift.access_token) {
                CheckboxService.downloadCheck(payment.id, null, paymentShift.access_token, payment.check.checkbox_check_id).then((res) => {
                    this.loading = false;
                    printer.newPrinter().printRawHtml("<div style='margin-top: 0; width: 277px'>" + res.body + "</div>");

                    this.clearIframes();
                }).catch((e) => {
                    this.loading = false;
                    this.$displayErrors(e);
                })
            } else {
                this.loading = false
                this.$error(__('Откройте кассу'));
            }
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        unselectAll() {
            this.$refs.table.unselectAll();
        }
    },
}
</script>
