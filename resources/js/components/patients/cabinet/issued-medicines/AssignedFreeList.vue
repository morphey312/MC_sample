<template>
    <manage-table
        v-loading="loading"
        ref="table"
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
                controls-position="right"
                :step="0.001"
                :min="0"
                :precision="3"
                :max="maxToIssue(props.rowData)"
                class="input-tiny text-right"
                @change="updateChangedItems(props.rowData)" />
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
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
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
                    name: 'card_number',
                    title: __('Номер карты'),
                    width: '90px',
                    filter: true,
                    filterField: 'card_number',
                },
                {
                    name: 'card_specialization_name',
                    title: __('Специализация'),
                    width: '100px',
                    filter: true,
                    filterField: 'patient_card_specialization',
                },
                {
                    name: 'clinic.name',
                    title: __('Клиника'),
                    width: '110px',
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
                    filter: true,
                    filterField: 'name',
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
                    name: 'self_cost',
                    title: __('Стоимость за шт, грн'),
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
                    width: '80px',
                    dataClass: 'text-center',
                },
            ],
            filters: {
                patient: [this.patient.id],
                clinic: this.clinics,
                is_free: true,
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
                    rows: response.rows.map(row => this.castMedicine(row))
                })
            });
        },
        remove(row) {
            return this.deleteAssigned(row);
        },
    },
}
</script>