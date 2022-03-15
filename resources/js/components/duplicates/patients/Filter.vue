<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="false"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
        <el-checkbox-group v-model="filter.compare_criterias">
            <el-row :gutter="20">
                <el-col :span="6">
                    <form-input-search
                        :entity="filter"
                        property="lastname"
                        :clearable="true"
                        :label="__('Фамилия')">
                        <template slot="label-addon">
                            <el-checkbox label="lastname">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                    </form-input-search>
                    <form-input-search
                        :entity="filter"
                        property="firstname"
                        :clearable="true"
                        :label="__('Имя')">
                        <template slot="label-addon">
                            <el-checkbox label="firstname">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                    </form-input-search>
                    <form-input-search
                        :entity="filter"
                        property="middlename"
                        :clearable="true"
                        :label="__('Отчество')">
                        <template slot="label-addon">
                            <el-checkbox label="middlename">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                    </form-input-search>
                </el-col>
                <el-col :span="6">
                    <form-input-search
                        :entity="filter"
                        property="primary_phone_number"
                        :clearable="true"
                        :label="__('Телефон')">
                        <template slot="label-addon">
                            <el-checkbox label="primary_phone_number">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                    </form-input-search>
                    <form-input-search
                        :entity="filter"
                        property="secondary_phone_number"
                        :clearable="true"
                        :label="__('Доп. телефон')">
                        <template slot="label-addon">
                            <el-checkbox label="secondary_phone_number">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                    </form-input-search>
                    <form-input-search
                        :entity="filter"
                        property="email"
                        :clearable="true"
                        label="E-mail">
                        <template slot="label-addon">
                            <el-checkbox label="email">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                    </form-input-search>
                </el-col>
                <el-col :span="6">
                    <form-switch
                        :entity="filter"
                        options="patient_status"
                        property="status"
                        :clearable="true"
                        :label="__('Статус клиента')">
                        <template slot="label-addon">
                            <el-checkbox label="status">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                    </form-switch>
                    <form-select
                        :entity="filter"
                        :options="clinics"
                        :clearable="true"
                        :multiple="true"
                        property="clinic"
                        :filterable="true"
                        :label="__('Клиника')"
                    />
                    <form-input-search
                        :entity="filter"
                        property="location"
                        :clearable="true"
                        :label="__('Место проживания')">
                        <template slot="label-addon">
                            <el-checkbox label="location">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                    </form-input-search>
                </el-col>
                <el-col :span="6">
                    <form-select
                        :entity="filter"
                        :repository="sources"
                        :clearable="true"
                        property="source"
                        :label="__('Источник информации')">
                        <template slot="label-addon">
                            <el-checkbox label="source">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                    </form-select>
                    <form-row
                        name="birth_dates"
                        :label="__('Дата рождения')">
                        <template slot="label-addon">
                            <el-checkbox label="birthday">{{ __('к сравнению') }}</el-checkbox>
                        </template>
                        <div class="form-input-group">
                            <form-date
                                :entity="filter"
                                :clearable="true"
                                property="birthday_from" />
                            <form-date
                                :entity="filter"
                                :clearable="true"
                                property="birthday_to"
                            />
                        </div>
                    </form-row>
                </el-col>
            </el-row>
        </el-checkbox-group>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import FilterMixin from '@/mixins/filter';
import InformationSourceRepository from '@/repositories/patient/information-source';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository(),
            sources: new InformationSourceRepository(),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter =  {
                firstname: null,
                lastname: null,
                middlename: null,
                primary_phone_number: null,
                secondary_phone_number: null,
                email: null,
                status: null,
                clinic: [],
                location: null,
                source: null,
                birthday_from: null,
                birthday_to: null,
                compare_criterias: [
                    'lastname',
                    'firstname',
                    'middlename',
                    'primary_phone_number',
                    'secondary_phone_number',
                    'birthday',
                ],
                ...fromState,
            };
        },
    },
    watch: {
        ['filter.birthday_from'](val) {
            this.filter.birthday_to = val;
        },
    },
};
</script>
