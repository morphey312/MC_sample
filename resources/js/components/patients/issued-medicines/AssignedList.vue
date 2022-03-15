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
        <template
            slot="is_doctor"
            slot-scope="props">
            <span v-html="isDoctor(props.rowData)" />
        </template>
        <template slot="footer-top">
            <div class="buttons">
                <el-button
                    v-if="$can('action-logs.assigned-issued-meds')"
                    :disabled="activeItem === null"
                    @click="showLog(activeItem)">
                    {{ __('Операции') }}
                </el-button>
            </div>
        </template>
    </manage-table>
</template>
<script>
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import IssueListMixin from '@/components/patients/cabinet/issued-medicines/mixins/issue-list';
import PatientRepository from '@/repositories/patient';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import EmployeeRepository from '@/repositories/employee';
import ProxyRepository from '@/repositories/proxy-repository';
import AssignedMedicineRepository from '@/repositories/patient/assigned-medicine';
import CONSTANTS from '@/constants';
import AssignedMedicine from '@/components/action-log/AssignedMedicine.vue';

export default {
    mixins: [
        IssueListMixin,
    ],
    props: {
        filters: Object,
    },
    data() {
        let clinicRepository = new ClinicRepository({
            accessLimit: this.$isAccessLimited('appointments'),
        });

        let patientRepository = new PatientRepository();

        let employeeRepository = new EmployeeRepository()

        return {
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repository = new AssignedMedicineRepository();
                return repository.fetch(this.getFilters(filters), sort, scopes, page, limit).then((result) => {
                    return {
                        rows: result.rows,
                        pagination: result.pagination,
                    }
                });
            }),
            loading: false,
            clinicRepository,
            patientRepository,
            employeeRepository,
            fields: [
                {
                    name: 'created',
                    title: __('Дата назначения'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'date_created',
                    sortField: 'created_at',
                    width: '100px',
                },
                {
                    name: 'card_number',
                    title: __('Номер карты'),
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                    filterField: 'card_number',
                    width: '100px',
                },
                {
                    name: 'card_specialization_name',
                    title: __('Специализация'),
                    filter: new SpecializationRepository(),
                    filterField: 'patient_card_specialization',
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'card_specialization_name',
                    width: '100px',
                },
                {
                    name: 'clinic.name',
                    title: __('Клиника'),
                    filter: clinicRepository,
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'clinic_name',
                    width: '135px',
                },
                {
                    name: 'patient_name',
                    title: __('Пациент'),
                    filter: new PatientRepository(),
                    filterField: 'patient',
                    sortField: 'patient_name',
                    width: '170px',
                },
                {
                    name: 'name',
                    title: __('Название медикамента'),
                    dataClass: 'no-ellipsis',
                    filter: true,
                    filterField: 'name',
                    sortField: 'medicine_name',
                    filterProps: {
                        searchModes: true,
                    },
                    width: '180px',
                },
                {
                    name: 'assigner.full_name',
                    title: __('Назначил'),
                    filter: employeeRepository,
                    filterField: 'doctor',
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'assigner_name',
                    width: '170px',
                },
                {
                    name: 'is_doctor',
                    title: __('Врач'),
                    width: '50px',
                },
                {
                    name: 'to_issue',
                    title: __('Остаток к выдаче'),
                    dataClass: 'text-right',
                    titleClass: 'text-right',
                    sortField: 'quantity',
                    width: '70px',
                },
                {
                    name: 'base_cost',
                    title: __('Стоимость за шт, грн'),
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                    sortField: 'base_cost',
                    width: '75px',
                },
                {
                    name: 'is_free',
                    title: __('Платный'),
                    width: '70px',
                    dataClass: 'no-dash',
                    filter: 'yes_no',
                    sortField: 'is_payed',
                    formatter: (value) => {
                        return this.$formatter.boolToString(!value, '<span class="check-yes" />');
                    },
                },
            ],
            activeItem: null,
            scopes: [
                'medicine',
                'assigner',
                'patient',
                'created_by',
                'clinic',
                'card_specialization',
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
        }
    },
    watch: {
        ['filters.clinic']: {
            handler(val){
                if(val){
                    this.employeeRepository.setFilters({
                        clinic: val,
                    })
                }
            },
            immediate: true
        },
    },
    methods: {
        isDoctor(row) {
            return this.$formatter.boolToString((row.assigner_id == row.creator_id), '<span class="check-yes" />')
        },
        getToIssue(row) {
            return Number((row.quantity - row.issued_quantity).toFixed(3));
        },
        syncFilters(updates) {
            this.$emit('syncFilters', updates);
        },
        getManageTable() {
            return this.$refs.table;
        },
        showLog() {
            this.$modalComponent(AssignedMedicine, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменений медикамента'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        selectionChanged(selection) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
        },
        getFilters(filters){
            return _.onlyFilled({
                ...filters,
                not_issued: true,
                medicine_type: CONSTANTS.ASSIGNED_MEDICINE.TYPES.MEDICINE,
            });
        },
    }

}
</script>
