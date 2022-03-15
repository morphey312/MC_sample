<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :show-collapse-button="true"
        :start-collapsed="startCollapsed"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :repository="operators"
                    property="operator"
                    :clearable="true"
                    :multiple="true"
                    :label="__('Оператор')"
                />
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="clinic"
                    :label="__('Клиника')" />
                <filter-patient
                    :entity="filter"
                    patient-name-prop="patient_name"
                    patient-id-prop="patient"
                    :label="__('Пациент')"
                    @selected="focusOnPatient" />
            </el-col>
            <el-col :span="8">
                <form-row
                    name="dates"
                    :label="__('Дата записи/звонка')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_start" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_end" />
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    :clearable="true"
                    options="patient_appointment_is_first"
                    property="is_first"
                    :label="__('Тип')" />
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="patient_phone_number"
                    :label="__('Номер телефона')" />
            </el-col>
            <el-col :span="8">
                <form-row
                    name="dates"
                    :label="__('Дата создания')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_start" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_end" />
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    options="delete_status"
                    :clearable="true"
                    property="is_deleted"
                    :label="__('Показывать')" />
                <div class="form-input-group">
                    <form-select
                        :entity="filter"
                        :repository="sources"
                        :clearable="true"
                        :multiple="true"
                        property="source"
                        :label="__('Источник (запись)')"
                    />
                    <form-select
                        :entity="filter"
                        :repository="sources"
                        :clearable="true"
                        :multiple="true"
                        property="patient_source"
                        :label="__('Источник (пациент)')"
                    />
                </div>
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import InformationSourceRepository from '@/repositories/patient/information-source';
import CONSTANTS  from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        startCollapsed: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('calls') && this.$isAccessLimited('appointments'),
            }),
            operators: new EmployeeRepository(),
            sources: new InformationSourceRepository({
                limitClinics: this.$isAccessLimited('calls') && this.$isAccessLimited('appointments'),
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                operator: [],
                clinic: [],
                date_start: null,
                date_end: null,
                created_start: null,
                created_end: null,
                is_first: null,
                patient: null,
                patient_name: null,
                patient_phone_number: null,
                is_deleted: null,
                source: [],
                patient_source: [],
                ...fromState,
            };
        },
        focusOnPatient(patient) {
            this.$nextTick(() => {
                this.initFilter({
                    patient: patient.id,
                });
            });
        },
    },
};
</script>
