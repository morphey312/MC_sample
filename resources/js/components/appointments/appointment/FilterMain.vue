<template>
    <section class="grey">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :repository="operators"
                    property="operator"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    :label="__('Оператор')"
                />
                <form-select
                    :entity="filter"
                    :options="statuses"
                    property="status"
                    :clearable="true"
                    :multiple="true"
                    :label="__('Статус записи')"
                />
                <form-select
                    :entity="filter"
                    :options="analysis_statuses"
                    property="analysisStatuses"
                    :clearable="true"
                    :multiple="true"
                    :label="__('Статус анализов')"
                />
                <form-input-search
                    :entity="filter"
                    property="patient_card_number"
                    :clearable="true"
                    :label="__('№ карты')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :repository="doctors"
                    property="doctor"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    collapse-tags
                    :label="__('Врач')"
                />
                <form-select
                    :entity="filter"
                    :options="workspace_list"
                    property="workspace"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    collapse-tags
                    :label="__('Кабинет')"
                />
                <form-select
                    :entity="filter"
                    :options="clinics_list"
                    property="clinic"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    collapse-tags
                    :label="__('Клиника')"
                />
                <form-input-search
                    :entity="filter"
                    property="patient_archive_card_number"
                    :clearable="true"
                    :label="__('№ карты (архив)')"
                />
            </el-col>
            <el-col :span="6">
                <form-switch
                    :entity="filter"
                    :options="date_of_list"
                    property="dateOf.relation"
                    :label="__('Фильтровать записи по дате:')"
                />
                <form-row
                    name="dates"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="dateOf.dateStart"
                            :editable="false"
                            :clearable="true"
                        />
                        <form-date
                            :entity="filter"
                            property="dateOf.dateEnd"
                            :editable="false"
                            :clearable="true"
                        />
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    :options="specializations"
                    property="specialization"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    :label="__('Специализация')"
                />
                <form-row
                    name="for_has_debt"
                    label="&nbsp;">
                    <form-checkbox
                        :entity="filter"
                        property="has_debt"
                        :label="__('Является должником')"
                    />
                </form-row>
            </el-col>
            <el-col :span="6">
                 <form-select
                    :entity="filter"
                    :options="patient_card_existence_list"
                    property="cardExistence"
                    :label="__('Наличие карты')"
                />
                <form-select
                    :entity="filter"
                    :options="is_first_list"
                    property="isFirst"
                    :clearable="true"
                    :label="__('Первичный/Повторный пациент')"
                />
                <form-select
                    :entity="filter"
                    :repository="sources"
                    :clearable="true"
                    :multiple="true"
                    property="source"
                    :label="__('Источник информации')"
                />
                <form-select
                    :entity="filter"
                    :options="is_deleted_list"
                    property="isDeleted"
                    :label="__('Удалена или нет запись')"
                />
            </el-col>
        </el-row>
    </section>
</template>
<script>
import ClinicRepository from '@/repositories/clinic';
import ProxyRepository from '@/repositories/proxy-repository';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import WorkspaceRepository from '@/repositories/workspace';
import AppointmentStatusRepository from '@/repositories/appointment/status';
import CONSTANTS from '@/constants';
import InformationSourceRepository from '@/repositories/patient/information-source';

export default {
    props: {
        filter: Object,
    },
    data() {
        let specializations = new ProxyRepository(() => {
            let options = specializations.getOptions();

            if (options.filters != undefined && options.filters.clinic.length !== 0) {
                return (new SpecializationRepository).fetchList(options.filters);
            }
            return Promise.resolve([]);
        });

        return {
            specializations,
            statuses: new AppointmentStatusRepository(),
            operators: new EmployeeRepository({
                filters: this.getOperatorFilters(),
            }),
            doctors: new EmployeeRepository({
                filters: this.getDoctorFilters(),
            }),
            clinics_list: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointments'),
            }),
            workspace_list: new WorkspaceRepository({
                filters: this.getWorkspaceFilters(),
            }),
            is_deleted_list: [{ id: null, value: __('Все') }, ...this.$handbook.getOptions('appointment_is_deleted')],
            is_first_list: this.$handbook.getOptions('patient_appointment_is_first'),
            analysis_statuses: this.$handbook.getOptions('analysis_filter_status'),
            sources: new InformationSourceRepository(),
            patient_card_existence_list: [
                {
                    id: null,
                    value: __('Все'),
                },
                {
                    id: 'true',
                    value: __('Есть'),
                },
                {
                    id: 'false',
                    value: __('Нет'),
                }
            ],
            date_of_list: [
                {
                    id: 'call',
                    value: __('Звонка'),
                },
                {
                    id: 'appointment',
                    value: __('Записи'),
                },
            ],
        };
    },
    methods: {
        getSpecializationFilters() {
            return _.onlyFilled({
                    clinic: this.filter.clinic,
                });
        },
        getDoctorFilters() {
            return _.onlyFilled({
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                status: CONSTANTS.EMPLOYEE.STATUSES.WORKING,
                clinic: this.filter.clinic,
                specialization: this.filter.specialization,
                date_employment_start: this.filter.dateOf.dateStart,
                date_employment_end: this.filter.dateOf.dateEnd
            });
        },
        getWorkspaceFilters() {
            return _.onlyFilled({
                hasDaySheet: 1,
                clinic: this.filter.clinic,
                specialization: this.filter.specialization,
            });
        },
        getOperatorFilters() {
            return {};
        },
    },
    watch: {
        ['filter.clinic']() {
            this.specializations.setFilters(this.getSpecializationFilters());
            this.doctors.setFilters(this.getDoctorFilters());
            this.workspace_list.setFilters(this.getWorkspaceFilters());
        },
        ['filter.specialization']() {
            this.doctors.setFilters(this.getDoctorFilters());
            this.workspace_list.setFilters(this.getWorkspaceFilters());
        },
    },
}
</script>
