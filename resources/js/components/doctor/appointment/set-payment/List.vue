<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        table-height="auto"
        @header-filter-updated="syncFilters">
        <template
            slot="expected_payment"
            slot-scope="props" >
                <el-input-number
                    v-model="props.rowData.expected_payment"
                    controls-position="right"
                    :min="0"
                    :step="1"
                    :max="props.rowData.debt"
                    control-size="mini"
                    css-class="input-tiny"
                    @change="changed(props.rowData)" />
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import Patient from '@/models/patient';
import AppointmentService from '@/models/appointment/service';
import AppointmentServiceRepository from '@/repositories/appointment/service';

export default {
    props: {
        appointment: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getRows();
            }),
            fields: [
                {
                    name: 'appointment.date',
                    title: __('Дата записи'),
                    width: "90px",
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                {
                    name: 'appointment.card_number',
                    title: __('Номер карты'),
                    width: "70px",
                },
                {
                    name: 'appointment.card_specialization',
                    title: __('Специализация карты'),
                    width: "150px",
                },
                {
                    name: 'name',
                    title: __('Услуга'),
                },
                {
                    name: 'paid',
                    title: __('Оплачено, грн'),
                    width: "80px",
                    dataClass: 'text-right',
                    titleClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                },
                {
                    name: 'debt',
                    title: __('Долг, грн'),
                    width: "80px",
                    dataClass: 'text-right',
                    titleClass: 'text-right',
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    },
                },
                {
                    name: 'expected_payment',
                    title: __('К оплате, грн'),
                    width: "80px",
                    dataClass: 'text-right',
                    titleClass: 'text-right',
                },
            ],
            filters: {
                doctor: this.appointment.doctor_id,
                appointment_clinic: this.appointment.clinic_id,
                patient: this.appointment.patient_id,
            },
            model: new Patient({id: this.appointment.patient_id}),
            changedRows: [],
        };
    },
    methods: {
        getRows() {
            let service = new AppointmentServiceRepository();
            return service.fetchPayeableServices(this.filters).then((response) => {
                return Promise.resolve({
                    rows: response.map(row => this.prepareRow(row)),
                });
            });
        },
        prepareRow(row) {
            row.debt = this.getDebtAttribute(row);
            return new AppointmentService(row);
        },
        syncFilters(updates) {
            this.filters = {...this.filters, ...updates}
        },
        getDebtAttribute(row) {
            let cost = Number(row.cost);
            return _.isVoid(row.paid) ? cost : (cost - Number(row.paid));
        },
        getChangedRows() {
            return this.changedRows;
        },
        changed(item) {
            let changedIndex = this.changedRows.findIndex(row => row.id == item.id);

            if (changedIndex == -1 && item.expected_payment != undefined) {
                this.changedRows.push(item);
            } else {
                if (!item.changed() || item.expected_payment == undefined) {
                    this.changedRows.splice(changedIndex, 1);
                }
            }
            this.$emit('selection-changed', this.changedRows);
        }
    },
}
</script>
