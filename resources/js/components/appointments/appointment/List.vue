<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :scopes="scopes"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        :flex-height="true"
        :initial-fields="initialFields"
        @selection-changed="selectionChanged"
        @loaded="loaded"
        @header-filter-updated="syncFilters" >
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>

<script>
import AppointmentRepository from '@/repositories/appointment';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import SpecializationRepository from '@/repositories/specialization';
import AppointmentStatusRepository from '@/repositories/appointment/status';
import DeleteReasonRepository from '@/repositories/appointment/delete-reason';
import WorkspaceRepository from '@/repositories/workspace';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import TimeHeaderFilter from '@/components/general/table/TimeHeaderFilter.vue';
import InformationSourceRepository from '@/repositories/patient/information-source';
import CONSTANTS from '@/constants';
import ServiceRepository from '@/repositories/service';
import AppointmentStatusReasonRepository from '@/repositories/appointment/status/reason';

export default {
    props: {
        filters: Object,
    },
    data() {
        let workspaceRepo = new WorkspaceRepository();
        let specializationRepo = new SpecializationRepository();
        let employeeRepo = new EmployeeRepository({filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR}, limit: 50});
        return {
            repository: new AppointmentRepository({forcePost: true, endpoint: '/api/v1/appointments/filter'}),
            initialFields: [
                'clinic_name',
                'date',
                'start',
                'end',
                'patient',
                'is_first',
                'doctor_name',
                'workspace_name',
                'status.name',
                'specialization_name',
                'card_number',
                'rejection_reason'
            ],
            fields: [
                {
                    name: 'clinic_name',
                    sortField: 'clinicName',
                    title: __('Клиника'),
                    width: '150px',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('appointments'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'date',
                    sortField: 'date',
                    title: __('Дата записи'),
                    width: '110px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    filter: DateHeaderFilter,
                    filterField: 'date',
                },
                {
                    name: 'start',
                    sortField: 'start',
                    title: __('Начало приема'),
                    width: '70px',
                    formatter: (value) => {
                        return this.$formatter.timeFormat(this.$moment(value, 'HH:mm:ss'));
                    },
                    filter: TimeHeaderFilter,
                    filterField: 'timeStart',
                },
                {
                    name: 'end',
                    sortField: 'end',
                    title: __('Окончание приема'),
                    width: '75px',
                    formatter: (value) => {
                        return this.$formatter.timeFormat(this.$moment(value, 'HH:mm:ss'));
                    },
                    filter: TimeHeaderFilter,
                    filterField: 'timeEnd',
                },
                {
                    name: 'is_first',
                    sortField: 'isFirst',
                    title: __('Первичный'),
                    dataClass: 'no-dash',
                    width: '80px',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'patient_appointment_is_first',
                    filterField: 'isFirst',
                },
                {
                    name: 'card_only_number',
                    filterField: 'patient_card_number',
                    title: __('№ карты'),
                    width: '90px',
                    dataClass: 'no-dash',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                    scopes: ['card_number'],
                },
                {
                    name: 'card_specialization_name',
                    sortField: 'card_specialization_name',
                    title: __('С-ция карты'),
                    width: '120px',
                    filter: new SpecializationRepository(),
                    filterField: 'card_specialization',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'archive_card_number',
                    filterField: 'patient_archive_card_number',
                    title: __('Архив № карты'),
                    width: '90px',
                    dataClass: 'no-dash',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                    scopes: ['archive_card_number'],
                },
                {
                    name: 'patient',
                    sortField: 'patient_name',
                    title: __('Пациент'),
                    width: '130px',
                    formatter: (value) => {
                        return value.full_name;
                    },
                    filter: true,
                    filterField: 'patient_name',
                },
                {
                    name: 'services',
                    title: __('Мед. услуги'),
                    width: '250px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value, 'name');
                    },
                    filter: new ServiceRepository({filters: { disabled: 0, exists: {rel: 'appointments', repo:'appointments', filters: this.filters}}}),
                    filterField: 'services',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['appointment_services'],
                },
                {
                    name: 'doctor_name',
                    sortField: 'doctor_name',
                    title: __('Врач'),
                    filter: employeeRepo,
                    filterField: 'doctor',
                    filterProps: {
                        multiple: true,
                        limit: 70
                    },
                    width: '130px',
                },
                {
                    name: 'workspace_name',
                    sortField: 'workspace',
                    title: __('Проц. Кабинет'),
                    filter: workspaceRepo,
                    filterField: 'workspace',
                    filterProps: {
                        multiple: true,
                    },
                    width: '130px',
                },
                {
                    name: 'specialization_name',
                    sortField: 'specialization_name',
                    title: __('С-ция записи'),
                    width: '120px',
                    filter: specializationRepo,
                    filterField: 'specialization',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['specialization'],
                },
                {
                    name: 'status.name',
                    sortField: 'statusName',
                    title: __('Статус'),
                    width: '150px',
                    filter: new AppointmentStatusRepository(),
                    filterField: 'status',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['status'],
                },
                {
                    name: 'status_reason',
                    filterField: 'status_reason',
                    sortField: 'status_reason',
                    title: __('Причина изм. статуса'),
                    width: '120px',
                    filter: new AppointmentStatusReasonRepository(),
                    scopes: ['status_reason'],
                },
                {
                    name: 'status_reason_comment',
                    filterField: 'status_reason_comment',
                    sortField: 'status_reason_comment',
                    title: __('Прим. к смене статуса'),
                    width: '120px',
                    filter: true,
                    scopes: ['status_reason'],
                },
                {
                    name: 'treatment_course',
                    title: __('Курс'),
                    dataClass: 'no-dash',
                    width: '120px',
                    filterField: 'in_treatment_course',
                    filter: 'yes_no',
                    formatter: (value) => this.formatCourse(value),
                    scopes: ['treatment_course'],
                },
                {
                    name: 'diagnoses',
                    title: __('Диагноз'),
                    dataClass: 'no-dash',
                    width: '120px',
                    sortField: 'diagnosis',
                    filter: true,
                    filterField: 'diagnosis',
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    scopes: ['diagnosis'],
                },
                {
                    name: 'operator_name',
                    title: __('Оператор'),
                    width: '110px',
                    sortField: 'operator_name',
                    filter: new EmployeeRepository(),
                    filterField: 'operator',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['operator'],
                },
                {
                    name: 'created_at',
                    title: __('Время звонка'),
                    width: '140px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateHeaderFilter,
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    width: '130px',
                    filter: true,
                    filterField: 'comment',
                    sortField: 'comment',
                    dataClass: 'no-dash',
                },
                {
                    name: 'is_deleted',
                    filterField: 'isDeleted',
                    title: __('Удалена запись'),
                    width: '120px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'appointment_is_deleted',
                },
                {
                    name: 'delete_reason_name',
                    filterField: 'delete_reason',
                    title: __('Причина удаления'),
                    width: '150px',
                    filter: new DeleteReasonRepository(),
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['delete_reason'],
                },
                {
                    name: 'discount_card_id',
                    filterField: 'has_discount_card',
                    sortField: 'has_discount_card',
                    title: __('Дисконт в записи'),
                    width: '120px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'insurance_policy_id',
                    filterField: 'insurance_policy',
                    title: __('Полис в записи'),
                    width: '120px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                },
                {
                    name: 'patient.full_address',
                    filter: true,
                    filterField: 'address',
                    sortField: 'address',
                    title: __('Адрес'),
                    width: '150px',
                },
                {
                    name: 'delete_reason_comment',
                    title: __('Комментарий к удалению'),
                    width: '180px',
                    filter: true,
                    scopes: ['delete_reason'],
                },
                {
                    name: 'creator_name',
                    sortField: 'creator_name',
                    title: __('Оператор (завел)'),
                    width: '120px',
                    filter: new EmployeeRepository(),
                    filterField: 'creator',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['creator'],
                },
                {
                    name: 'source_name',
                    sortField: 'source',
                    title: __('Источник (запись)'),
                    width: '110px',
                    dataClass: 'no-dash',
                    filter: new InformationSourceRepository(),
                    filterField: 'source',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['source'],
                },
                {
                    name: 'patient.source_name',
                    title: __('Источник (пациент)'),
                    width: '110px',
                    dataClass: 'no-dash',
                    sortField: 'patient_source',
                    filter: new InformationSourceRepository(),
                    filterField: 'patient_source',
                    filterProps: {
                        multiple: true,
                    },
                    scopes: ['patient_source'],
                },
                {
                    name: 'patient.location',
                    title: __('Место проживания'),
                    width: '110px',
                    sortField: 'patient_location',
                    dataClass: 'no-dash',
                    filter: true,
                    filterField: 'patient_location',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'patient.birthday',
                    title: __('Дата рождения'),
                    width: '120px',
                    sortField: 'patient_birthday',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'patient.appointments_count',
                    title: __('Кол-во записей'),
                    width: '110px',
                    scopes: ['patient_appointments_count'],
                },
                {
                    name: 'patient.treatment_courses_count',
                    title: __('Кол-во курсов'),
                    width: '110px',
                    scopes: ['patient_treatment_courses_count'],
                },
                {
                    name: 'patient.gender',
                    title: __('Пол'),
                    filter: 'gender',
                    filterField: 'gender',
                    sortField: 'gender',
                    width: '50px',
                    formatter: (value) => {
                        return this.$handbook.getOption('gender_short', value);
                    },
                },
                {
                    name: 'patient.has_service_debt',
                    title: __('Должник'),
                    dataClass: 'no-dash',
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                    filterField: 'has_debt',
                    scopes: ['patient_debts'],
                },
                {
                    name: 'patient_card_debt',
                    // sortField: 'patient_location',
                    dataClass: 'no-dash',
                    filter: 'yes_no',
                    filterField: 'patient_card_debt',
                    title: __('Долг по карте'),
                    width: '110px',
                    scopes: ['patient_card_debts'],
                },
                {
                    name: 'patient.service_debt',
                    sortField: 'patient_location',
                    dataClass: 'no-dash',
                    filter: 'yes_no',
                    filterField: 'has_debt',
                    title: __('Общий долг'),
                    width: '110px',
                    scopes: ['patient_debts'],
                },
                {
                    name: 'has_base_service',
                    title: __('Есть базовая услуга'),
                    width: '130px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                    filterField: 'has_base_service',
                    scopes: ['appointment_services'],
                },
                {
                    name: 'patient.contact_details.primary_phone_number',
                    title: __('Телефон'),
                    width: '110px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                    filter: true,
                    filterField: 'patient_primary_phone_number',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'patient.contact_details.email',
                    title: __('Email'),
                    width: '150px',
                    dataClass: 'no-dash',
                    filter: true,
                    filterField: 'patient_email',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'patient.contact_details.secondary_phone_number',
                    title: __('Телефон доп.'),
                    width: '110px',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                    filter: true,
                    filterField: 'patient_secondary_phone_number',
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'rejection_reason',
                    title: __('Причина отказа'),
                    width: '100px',
                    formatter: (value) => {
                        return this.$handbook.getOption('reason_refusing_treatment', value);
                    },
                    filter: 'reason_refusing_treatment',
                    filterField: 'rejection_reason',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'created_by_patient',
                    title: __('Создано пациентом'),
                    dataClass: 'no-dash',
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    filter: 'yes_no',
                    filterField: 'created_by_patient',
                },
            ],
            scopes: [
                'clinic',
                'doctor',
                'patient',
                'patient_contacts',
                'card_specialization',
            ],
            employeeRepo,
            workspaceRepo,
            specializationRepo,
            initialSortOrder: [
                {field: 'date', direction: 'desc'},
            ],
            selectedRow: null,
        };
    },
    watch: {
        ['filters.clinic']: {
            immediate: true,
            handler() {
                this.specializationRepo.setFilters(this.getSpecializationFilters());
                this.workspaceRepo.setFilters(this.getWorkspaceFilters());
                this.employeeRepo.setFilters(this.getDoctorFilters());
            }
        },
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        getTable() {
            return this.$refs.table;
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filters.clinic,
                status: 1,
            });
        },
        formatCourse(value) {
            if (value) {
                return [
                    value.start,
                    value.end,
                ].filter((v) => _.isFilled(v))
                .map((v) => this.$formatter.dateFormat(v))
                .join(' &mdash; ');
            }
            return '';
        },
        getDoctorFilters() {
            return _.onlyFilled({
                clinic: this.filters.clinic,
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                employment_range: {
                    date_start_working: this.filters.dateOf.dateStart,
                    date_employment_end: this.filters.dateOf.dateEnd,
                    clinics: [this.filters.clinic]
                },
            });
        },
        getWorkspaceFilters() {
            return _.onlyFilled({
                hasDaySheet: 1,
                clinic: this.filters.clinic,
                specialization: this.filters.specialization,
                is_active: 1
            });
        },
    }
}
</script>
