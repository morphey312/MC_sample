<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-input-search
                    :entity="filter"
                    property="lastname"
                    :clearable="true"
                    :label="__('Фамилия')" />
                <form-input-search
                    :entity="filter"
                    property="firstname"
                    :clearable="true"
                    :label="__('Имя')" />
                <form-input-search
                    :entity="filter"
                    property="middlename"
                    :clearable="true"
                    :label="__('Отчество')" />
            </el-col>
            <el-col :span="6">
                <form-switch
                    :entity="filter"
                    options="patient_status"
                    property="status"
                    :clearable="true"
                    :label="__('Статус клиента')"
                />
                <form-row
                    name="contact-details"
                    label="">
                    <div class="form-input-group">
                        <form-input-search
                            :entity="filter"
                            property="email"
                            :clearable="true"
                            label="E-mail"
                        />
                        <form-input-search
                            :entity="filter"
                            property="phone"
                            :clearable="true"
                            :label="__('Телефон')"
                        />
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="patient-card-numbers"
                    label="">
                        <div class="form-input-group">
                            <form-input-search
                                :entity="filter"
                                property="cardNumber"
                                :clearable="true"
                                :label="__('№ карты')"
                            />
                            <form-input-search
                                :entity="filter"
                                property="archive_card_number"
                                :clearable="true"
                                :label="__('№ карты (архив)')"
                            />
                        </div>
                </form-row>
                <form-row
                    name="patient-card-specialization"
                    label="">
                        <div class="form-input-group">
                            <form-select
                                :entity="filter"
                                :options="specializations"
                                :clearable="true"
                                :filterable="true"
                                :multiple="true"
                                property="cardSpecialization"
                                :label="__('Спец. карты')"
                            />
                            <form-select
                                :entity="filter"
                                :options="specializations"
                                :clearable="true"
                                :filterable="true"
                                :multiple="true"
                                property="archive_card_specialization"
                                :label="__('Спец. карты (архив)')"
                            />
                        </div>
                </form-row>
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
                <form-input-search
                    :entity="filter"
                    property="location"
                    :clearable="true"
                    :label="__('Место проживания')"
                />
                <form-select
                    :entity="filter"
                    :repository="sources"
                    :clearable="true"
                    property="source"
                    :label="__('Источник информации')"
                />
                <form-select
                    :entity="filter"
                    options="yes_no"
                    :clearable="true"
                    property="has_questionnaire"
                    :label="__('Анкета')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import FilterMixin from '@/mixins/filter';
import InformationSourceRepository from '@/repositories/patient/information-source';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('patients')
            }),
            specializations: new SpecializationRepository(),
            sources: new InformationSourceRepository(),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter =  {
                firstname: null,
                lastname: null,
                middlename: null,
                phone: null,
                status: null,
                clinic: [],
                cardSpecialization: [],
                archive_card_specialization: [],
                email: null,
                location: null,
                source: null,
                cardNumber: null,
                archive_card_number: null,
                birthdayFrom: null,
                birthdayTo: null,
                ...fromState,
            };
        },
    },
    watch: {
        ['filter.birthdayFrom'](val) {
            this.filter.birthdayTo = val;
        },
    },
};
</script>
