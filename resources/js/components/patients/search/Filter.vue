<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        :button-submit-text="__('Поиск контакта')"
        @changed="changed"
        @cleared="cleared" >
        <el-row :gutter="20">
            <el-col :span="-15">
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    :tab-index="1"
                    property="lastname"
                    :label="__('Фамилия пациента')" />
                <form-row
                    name="patient-card-numbers"
                    label="">
                        <div class="form-input-group">
                            <form-input-search
                                :entity="filter"
                                :clearable="true"
                                property="card_number"
                                :label="__('№ карты')" />
                            <form-input-search
                                :entity="filter"
                                :clearable="true"
                                property="archive_card_number"
                                :label="__('№ карты (архив)')" />
                        </div>
                </form-row>
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="location"
                    :error-prefix="`patient_filter.`"
                    :label="__('Место проживания')" />
            </el-col>
            <el-col :span="-15">
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    :tab-index="2"
                    property="firstname"
                    :label="__('Имя пациента')" />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    property="cardSpecialization"
                    :label="__('Специализация карты')"
                />
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="primary_phone_number"
                    :label="__('Номер телефона')"
                />
            </el-col>
            <el-col :span="-15">
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    :tab-index="3"
                    property="middlename"
                    :label="__('Отчество пациента')" />
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="secondary_phone_number"
                    :label="__('Дополнительный номер телефона')"
                />
            </el-col>
            <el-col :span="-15">
                <form-select
                    :entity="filter"
                    :options="statuses"
                    :error-prefix="`patient_filter.`"
                    property="status"
                    :label="__('Тип клиента')"
                />
                <form-select
                    :entity="filter"
                    :repository="sources"
                    :clearable="true"
                    :error-prefix="`patient_filter.`"
                    property="source"
                    :label="__('Источник информации')"
                />
            </el-col>
            <el-col :span="-15">
                <form-row
                    name="createdDates"
                    :label="__('Дата рождения')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="birthdayFrom"
                            :placeholder="__('С')" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="birthdayTo"
                            :placeholder="__('До')" />
                    </div>
                </form-row>
                <form-input-search
                    :entity="filter"
                    property="email"
                    :clearable="true"
                    label="E-mail"
                />
            </el-col>
        </el-row>
        <template
            v-if="showCreateButton && $canCreate('patients')"
            slot="extra-buttons">
            <el-button @click="createPatient">
                {{ __('Добавить контакт') }}
            </el-button>
        </template>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import InformationSourceRepository from '@/repositories/patient/information-source';
import SpecializationRepository from '@/repositories/specialization';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        skipId: {
            type: Array,
            default: () => [],
        },
        showCreateButton: {
            type: Boolean,
            default: true,
        },
        restrictClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.restrictClinics,
            }),
            statuses: this.setStatuses(),
            sources: new InformationSourceRepository(),
            specializations: new SpecializationRepository(),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                firstname: null,
                lastname: null,
                middlename: null,
                primary_phone_number: null,
                secondary_phone_number: null,
                status: '',
                email: null,
                location: null,
                source: null,
                cardNumber: null,
                archive_card_number: null,
                birthdayFrom: null,
                birthdayTo: null,
                cardSpecialization: null,
                skipId: [...this.skipId],
                ...fromState,
            };
        },
        setStatuses() {
            return [
                {id: '', value: __('Все')},
                ...this.$handbook.getOptions('patient_status')
            ];
        },
        createPatient() {
            this.$emit('create-patient');
        },
        cleared() {
            this.$emit('cleared');
            this.initFilter();
        },
    },
    watch: {
        ['filter.birthdayFrom'](val) {
            this.filter.birthdayTo = val;
        },
    },
};
</script>
