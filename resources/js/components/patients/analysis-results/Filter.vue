<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared" >
        <el-row :gutter="20">
            <el-col :span="6">
                <form-input-search
                    :entity="filter"
                    property="patient_lastname"
                    :clearable="true"
                    :label="__('Фамилия')" />
                <form-input-search
                    :entity="filter"
                    property="patient_firstname"
                    :clearable="true"
                    :label="__('Имя')" />
                <form-input-search
                    :entity="filter"
                    property="patient_middlename"
                    :clearable="true"
                    :label="__('Отчество')" />
                <form-row
                    name="birth_dates"
                    :label="__('Дата рождения')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="birthdayFrom" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="birthdayTo"
                        />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="6">
                <div class="form-input-group">
                    <form-input-search
                        :entity="filter"
                        property="patientCardNumber"
                        :clearable="true"
                        :label="__('№ карты')"
                    />
                    <form-input-search
                        :entity="filter"
                        property="patient_archive_card_number"
                        :clearable="true"
                        :label="__('№ карты (архив)')"
                    />
                </div>
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    property="patientCardSpecialization"
                    :label="__('Специализация карты')"
                />
                <form-row
                    name="dates"
                    :label="__('Период сдачи')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_pass_start"
                            :placeholder="__('Сданы с')" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_pass_end"
                            :placeholder="__('Сданы по')" />
                    </div>
                </form-row>
                <form-row
                    name="dates"
                    :label="__('Период назначения')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_appointed_start"
                            :placeholder="__('Назначены с')" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_appointed_end"
                            :placeholder="__('Назначены по')" />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="analysis_name"
                    :clearable="true"
                    :label="__('Название')"
                />
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
                <form-input
                    :entity="filter"
                    property="clinic_code"
                    :clearable="true"
                    :label="__('Код клиники')"
                />
                <form-row
                    name="attachment">
                    <form-select
                        :entity="filter"
                        options="yes_no"
                        :clearable="true"
                        property="attachments"
                        :label="__('Результат прикреплён')"
                    />
                </form-row>
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="analysis_status"
                    :clearable="true"
                    :multiple="true"
                    :collapse-tags="true"
                    property="status"
                    :label="__('Статус')"
                />
                <form-select
                    :entity="filter"
                    :options="laboratories"
                    :clearable="true"
                    :filterable="true"
                    property="laboratory"
                    :label="__('Лаборатория')"
                />
                <form-input-search
                    :entity="filter"
                    property="laboratory_code"
                    :clearable="true"
                    :label="__('Код лаборатории')"
                />
                <form-input
                    :entity="filter"
                    property="verification_code"
                    :clearable="true"
                    :label="__('Код верификации')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import SpecializationRepository from '@/repositories/specialization';
import FilterMixin from '@/mixins/filter';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        activeTab: String
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('analysis-results'),
            }),
            laboratories: new LaboratoryRepository(),
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('analysis-results'),
            }),
        };
    },
    watch: {
        ['filter.clinic'](value) {
            this.specializations.setFilters({
                clinic: value,
            }, true);

            this.laboratories.setFilters({
                clinics: value,
            }, true);
        },
        ['filter.patientCardSpecialization'](value) {
            this.clinics.setFilters( _.onlyFilled({
                has_specialization: value,
            }));
        },
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: null,
                analysis_name: null,
                laboratory: null,
                laboratory_code: null,
                clinic_code: null,
                status: [CONSTANTS.ANALYSIS_RESULT.STATUSES.PASSED],
                date_pass_start: null,
                date_pass_end: null,
                patientCardNumber: null,
                patientCardSpecialization: null,
                patient_lastname: null,
                patient_firstname: null,
                patient_middlename: null,
                ...fromState,
            };
        },
    }
};
</script>
