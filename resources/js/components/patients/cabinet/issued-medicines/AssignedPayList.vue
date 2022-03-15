<template>
    <manage-table
        ref="table"
        v-loading="loading"
        :fields="fields"
        :filters="filters"
        :scopes="scopes"
        :repository="repository"
        table-height="250px"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot="issue_quantity"
            slot-scope="props">
            <el-input-number
                v-model="props.rowData.issue_quantity"
                :disabled="props.rowData.onPayment && props.rowData.onPayment == true"
                controls-position="right"
                :step="0.001"
                :precision="3"
                :min="0"
                :max="maxToIssue(props.rowData)"
                class="input-tiny text-right"
                @change="calcToPay(props.rowData)" />
        </template>
        <template
            slot="rests"
            slot-scope="props">
            <div class="has-icon">
                <span class="ellipsis">
                    {{ props.rowData.rests_total }}
                </span>
                <svg-icon 
                    name="info-alt" 
                    class="icon-tiny icon-grey"
                    @click.stop="showDetails(props.rowData)" />
            </div>
        </template>
        <template
            slot="remove"
            slot-scope="props" >
            <span  @click="remove(props.rowData)">
                <svg-icon 
                    name="delete" 
                    class="icon-small" 
                    :class="(props.rowData.onPayment && props.rowData.onPayment == true) || inPaymentList(props.rowData) ? 'icon-grey' : 'icon-blue'" />
            </span>
        </template>
        <template slot="footer-top">
            <slot name="totals" />
        </template>
    </manage-table>
</template>
<script>
import AssignedMedicineRepository from '@/repositories/patient/assigned-medicine';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import MedicineListMixin from './mixins/medicine-list';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        MedicineListMixin,
    ],
    props: {
        onPaymentList: {
            type: Array,
            default: [],
        },
    },
    data() {
        return {
            fields: [
                {
                    name: 'created',
                    title: __('Дата назначения'),
                    width: '100px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'date_created',
                },
                {
                    name: 'card_specialization_name',
                    title: __('Спец-ция'),
                    width: '80px',
                    filter: true,
                    filterField: 'patient_card_specialization',
                },
                {
                    name: 'clinic.name',
                    title: __('Клиника'),
                    width: '100px',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('appointments.access-clinic'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'assigner.full_name',
                    title: __('Назначил врач'),
                    width: '170px',
                    filter: true,
                    filterField: 'doctor_name',
                },
                {
                    name: 'name',
                    title: __('Название медикамента'),
                    width: '160px',
                    filter: true,
                    filterField: 'name',
                },
                {
                    name: 'by_policy',
                    title: __('Полис'),
                    width: '48px',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'franchise',
                    title: __('Фр-за, %'),
                    width: '60px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'warranter',
                    title: __('Гарант'),
                    width: '70px',
                },
                {
                    name: 'to_issue',
                    title: __('Остаток к выдаче'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    width: '70px',
                },
                {
                    name: 'issue_quantity',
                    title: __('К выдаче, шт'),
                    titleClass: 'text-right',
                    width: '75px',
                },
                {
                    name: 'base_cost',
                    title: __('Стоимость за шт, грн'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    width: '75px',
                },
                {
                    name: 'to_pay',
                    title: __('К оплате, грн'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    width: '75px',
                },
                {
                    name: 'rests',
                    title: __('Остатки на складах шт'),
                    width: '80px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'remove',
                    title: __('Удалить'),
                    width: '60px',
                    dataClass: 'text-center',
                },
            ],
            filters: {
                patient: [this.patient.id],
                clinic: this.clinics,
                is_free: false,
                should_issue: true,
                medicine_type: CONSTANTS.ASSIGNED_MEDICINE.TYPES.MEDICINE,
            },
        }
    },
    methods: {
        getRows(filters, scopes) {
            let repo = new AssignedMedicineRepository();
            return repo.fetch(filters, null, scopes).then((response) => {
                return Promise.resolve({
                    rows: response.rows.map(row => this.castMedicineRow(row))
                });
            });
        },
        castMedicineRow(row) {
            
            if (this.onPaymentList.length == 0) {
                return this.castMedicine(row);
            } else {
                let onPaymentMedicine = this.onPaymentList.find(item => item.service_id == row.id);
                let medicine = this.castMedicine(row);
                medicine.onPayment = true;
                
                if (onPaymentMedicine != undefined) {
                    onPaymentMedicine = this.getToPay({
                        ...onPaymentMedicine,
                        ...{
                            is_free: onPaymentMedicine.service.is_free,
                            by_policy: onPaymentMedicine.service.by_policy,
                            franchise: onPaymentMedicine.service.franchise,
                            base_cost: onPaymentMedicine.service.base_cost,
                            issue_quantity: onPaymentMedicine.quantity,
                        }
                    });

                    medicine.issue_quantity = onPaymentMedicine.quantity;
                    medicine.to_pay = onPaymentMedicine.to_pay;
                    medicine.self_cost = onPaymentMedicine.self_cost;
                    this.updateChangedItems(medicine);
                }
                return medicine;
            }
        },
        calcToPay(row) {
            row = this.getToPay(row);
            this.updateChangedItems(row);
        },
        getToPay(row) {
            if (row.is_free == false) {
                let cost = Number(row.base_cost);
                if (row.by_policy) {
                    let franchise = Number(row.franchise);
                    if (franchise > 0) {
                        cost = (cost / 100 * franchise);
                    } else {
                        cost = 0;
                    }
                }
                row.to_pay = this.$formatter.numberFormat(Math.round(cost * row.issue_quantity));
            }
            return row;
        },
        remove(row) {
            if ((row.onPayment && row.onPayment == true) || this.inPaymentList(row)) {
                return false;
            }
            return this.deleteAssigned(row);
        },
        inPaymentList(row) {
            return this.onPaymentList.findIndex(item => item.service_id == row.id) !== -1;
        },
    },
}
</script>
