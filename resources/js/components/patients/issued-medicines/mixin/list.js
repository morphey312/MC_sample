import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import SpecializationRepository from '@/repositories/specialization';
import PatientRepository from '@/repositories/patient';
import EmployeeRepository from '@/repositories/employee';
import ProxyRepository from '@/repositories/proxy-repository';
import IssuedMedicineRepository from '@/repositories/patient/issued-medicine';
import CONSTANTS from '@/constants';
import * as resultGenerator from "../generators/results";
import AssignedMedicine from '@/components/action-log/AssignedMedicine.vue';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';

export default {
    mixins: [
        ExportXLSXMixin,
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
            lastQuery: null,
            loading: false,
            fileGenerator: resultGenerator,
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repository = new IssuedMedicineRepository();

                this.lastQuery = {filters: this.getFilters(filters), sort, scopes, page, limit};
                return repository.fetch(this.getFilters(filters), sort, scopes, page, limit).then((result) => {
                    return {
                        rows: result.rows,
                        pagination: result.pagination,
                    }
                });
            }),
            reportRepository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repository = new IssuedMedicineRepository();

                return repository.fetch(
                    this.lastQuery.filters,
                    this.lastQuery.sort,
                    this.lastQuery.scopes,
                    page,
                    limit
                ).then((result) => {
                    return {
                        rows: result.rows,
                        pagination: result.pagination,
                    }
                });
            }),
            clinicRepository,
            patientRepository,
            employeeRepository,
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
                    filterField: 'patient_card_number',
                    filterProps: {
                        searchModes: true,
                    },
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
                    width: '165px',
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
                    filter: employeeRepository,
                    filterField: 'doctor_id',
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'assigner_name',
                    width: '165px',
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
                    filter: employeeRepository,
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
            ],
            activeItem: null,
        }
    },
    methods: {
        syncFilters(updates) {
            this.$emit('syncFilters', updates);
        },
        selectionChanged(selection) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
        },
        getManageTable() {
            return this.$refs.table;
        },
        getFilters(filters) {
            return filters;
        },
        getTableRows() {
            return this.$refs.table.getData();
        },
        showLog() {
            this.$modalComponent(AssignedMedicine, {
                id: this.activeItem.assigned_medicine_id,
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
    }

}
