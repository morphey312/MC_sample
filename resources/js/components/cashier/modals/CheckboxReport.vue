<template>
    <div v-loading="loading">
        <form-select
            :entity="selectedShift"
            property="id"
            :options="cashierShifts"
            :label="__('Активная касса')"
        />
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                :disabled="!selectedShift.accessToken"
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import CreateMixin from '@/mixins/generic-create';
import printer from '@/services/print';
import checkbox from "@/services/checkbox";
import CONSTANTS from '@/constants';
import Check from "@/models/checkbox/check";

export default {
    mixins: [
        CreateMixin,
    ],
    props: {
        reportType: {
            type: String,
            required: true,
        },
        activeShift: Object,
        checkboxCashboxes: Array,
    },
    data() {
        return {
            loading: false,
            cashierShifts: [],
            selectedShift: {
                id: null,
                accessToken: null,
            },
        }
    },
    watch: {
        ['selectedShift.id']() {
            this.selectedShift.accessToken = this.cashierShifts.find(item => item.id === this.selectedShift.id).access_token;
            this.selectedShift.money_reciever_cashbox_id = this.cashierShifts.find(item => item.id === this.selectedShift.id).money_reciever_cashbox_id;
        },
    },
    mounted() {
        this.getShifts();
    },
    methods: {
        getShifts() {
            this.checkboxCashboxes.forEach((item) => {
                this.cashierShifts.push({
                    id: item.money_reciever_id,
                    value: item.money_reciever_cashbox_name,
                    money_reciever_cashbox_id: item.money_reciever_cashbox_id,
                    access_token: item.access_token,
                });
            });
        },
        create() {
            if (this.reportType === 'xReport') {
                this.xReport();
                this.$emit('created');
            }
            if (this.reportType === 'zReport') {
                this.zReport();
            }
        },
        xReport() {
            this.loading = true;
            checkbox.getXReport(this.selectedShift.accessToken).then((result) => {
                this.loading = false;
                this.saveCheck( CONSTANTS.CHECKBOX_CHECKS.TYPE.X_REPORT,result.body)
            }).catch(() => {
                this.$error(__('Ошибка,повторите запрос позже'));
                this.loading = false;
            })
        },
        zReport() {
            this.$confirm(__('Текущая смена закроется!Вы уверены что хотите выгрузить z-отчет?'), () => {
                this.loading = true;
                checkbox.getZReport(this.selectedShift.accessToken).then((result) => {
                    let currentCheckbox =  this.checkboxCashboxes.find((cashbox) => cashbox.money_reciever_id === this.selectedShift.id)
                    currentCheckbox.access_token = null;
                    currentCheckbox.employee_id = null;
                    currentCheckbox.save().then( () => {
                        this.checkboxCashboxes.splice(this.checkboxCashboxes.indexOf(this.checkboxCashboxes, currentCheckbox), 1);
                        this.loading = false;
                        this.$emit('created');
                    })
                    this.saveCheck( CONSTANTS.CHECKBOX_CHECKS.TYPE.Z_REPORT,result.body)
                }).catch(() => {
                    this.$error(__('Ошибка,повторите запрос позже'));
                    this.loading = false;
                })
            })
        },
        saveCheck(reportType,body) {
            new Check({
                money_reciever_cashbox_id: this.selectedShift.money_reciever_cashbox_id,
                body: body,
                employee_id : this.$store.state.user.employee.id,
                type: reportType
            }).save().then(() => {
                printer.newPrinter().printRawHtml('<pre>' + body +'</pre>');
            });
        }
    }
}
</script>
