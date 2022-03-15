<template>
    <manage-table
        ref="table"
        v-loading="loading"
        :fields="fields"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :flex-height="true"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @header-filter-updated="syncFilters">
        <template
            slot="to_issue"
            slot-scope="props">
            {{ getToIssue(props.rowData) }}
        </template>
        <template slot="footer-top">
            <div class="buttons">
                <el-button
                    v-if="$can('action-logs.assigned-issued-meds')"
                    :disabled="activeItem === null"
                    @click="showLog(activeItem)">
                    {{ __('Операции') }}
                </el-button>
                <el-button @click="exportExcel(__('Медикаменты'))">
                    {{ __('Экспорт в Excel') }}
                </el-button>
            </div>
        </template>
    </manage-table>
</template>
<script>
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import EmployeeRepository from '@/repositories/employee';
import PatientRepository from '@/repositories/patient';
import ProxyRepository from '@/repositories/proxy-repository';
import IssuedMedicineRepository from '@/repositories/patient/issued-medicine';
import CONSTANTS from '@/constants';
import IssueListMixin from '@/components/patients/cabinet/issued-medicines/mixins/issue-list';
import ListMixin from './mixin/list';

export default {
    mixins: [
        IssueListMixin,
        ListMixin,
    ],
    data() {
        let clinicRepository = new ClinicRepository({
            accessLimit: this.$isAccessLimited('appointments'),
        });

        let patientRepository = new PatientRepository({
            filters: {
                wereIssuedMedicine: true
            },
            sort: [{direction: 'asc', field: 'full_name'}],
        });

        let issuerRepository = new EmployeeRepository({filters: {
            everIssuedMedicine: true
        }})

        let assignerRepository = new EmployeeRepository({filters: {
            everAssigned: true
        }});

        return {
            clinicRepository,
            patientRepository,
            issuerRepository,
            assignerRepository,
            fields: [
                {
                    name: 'medicine.clinic.name',
                    title: __('Клиника'),
                    width: '100px',
                    filter: clinicRepository,
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'created',
                    title: __('Дата выдачи медикамента'),
                    width: '90px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'created',
                    sortField: 'created_at',
                },
                {
                    name: 'medicine.card_number',
                    title: __('Номер карты'),
                    width: '100px',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                    filterField: 'patient_card_number',
                },
                {
                    name: 'medicine.card_specialization_name',
                    title: __('Специализация карты'),
                    width: '100px',
                    filter: new SpecializationRepository(),
                    filterField: 'specialization_card_id',
                    filterProps: {
                        multiple: true,

                    },
                },
                {
                    name: 'medicine.patient_name',
                    title: __('Пациент'),
                    filter: patientRepository,
                    filterField: 'patient',
                    sortField: 'patient_name',
                    width: '180px',
                },
                {
                    name: 'medicine.name',
                    title: __('Название медикаментов'),
                    filter: true,
                    filterField: 'medicine_name',
                    filterProps: {
                        searchModes: true,
                    },
                    sortField: 'medicine_name',
                    dataClass: 'no-ellipsis',
                    width: '180px',
                },
                {
                    name: 'medicine.assigner.full_name',
                    title: __('Назначил'),
                    filter: assignerRepository,
                    filterField: 'doctor_id',
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'assigner_name',
                    width: '180px',
                },
                {
                    name: 'medicine',
                    title: __('Врач'),
                    width: '50px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString((value.assigner_id == value.creator_id), '<span class="check-yes" />')
                    },
                },
                {
                    name: 'issuer_name',
                    title: __('Оператор'),
                    filter: issuerRepository,
                    filterField: 'operator_id',
                    sortField: 'operator_name',
                    width: '100px',
                },
                {
                    name: 'medicine.self_cost',
                    title: __('Стоимость, грн за шт'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    sortField: 'self_cost',
                    width: '80px',
                },
                {
                    name: 'medicine.quantity',
                    title: __('Назначено, шт'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    sortField: 'assigned_quantity',
                    width: '80px',
                },
                {
                    name: 'issued',
                    title: __('Выдано, шт'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    sortField: 'quantity',
                    width: '80px',
                },
                {
                    name: 'to_issue',
                    title: __('Осталось выдать, шт'),
                    width: '80px',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    filter: 'yes_no',
                    filterField: 'should_issue',
                },
                {
                    name: 'medicine.is_free',
                    title: __('Платный'),
                    width: '70px',
                    dataClass: 'no-dash',
                    filter: 'yes_no',
                    filterField: 'is_payed',
                    sortField: 'is_payed',
                    formatter: (value) => {
                        return this.$formatter.boolToString(!value, '<span class="check-yes" />');
                    },
                },
            ],
        }
    },
    watch: {
        ['filters.clinic']: {
            handler(val){
                if(val){
                    this.issuerRepository.setFilters({
                        clinic: val,
                    }, true);

                    this.assignerRepository.setFilters({
                        clinic: val,
                    }, true);

                    this.patientRepository.setFilters({
                        clinic: val,
                    }, true)
                }
            },
            immediate: true
        },
    },
}
</script>
