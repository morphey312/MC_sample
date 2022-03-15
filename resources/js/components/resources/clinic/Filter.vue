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
                <form-input
                    :entity="filter"
                    property="name"
                    :clearable="true"
                    :label="__('Название клиники')"
                />
                <form-select
                    :entity="filter"
                    options="currency"
                    :clearable="true"
                    property="currency"
                    :label="__('Валюта')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="official_name"
                    :clearable="true"
                    :label="__('Официальное название клиники')"
                />
                <form-select
                    :entity="filter"
                    options="active_status"
                    :clearable="true"
                    property="status"
                    :label="__('Статус')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="city"
                    :clearable="true"
                    property="city"
                    :label="__('Город')"
                />
                <form-select
                    :entity="filter"
                    :options="groups"
                    :clearable="true"
                    property="group"
                    :label="__('Группа клиник')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="countries"
                    :clearable="true"
                    property="country"
                    :label="__('Страна')"
                />
                <form-select
                    :entity="filter"
                    :options="msp"
                    :clearable="true"
                    property="msp"
                    :label="__('Предоставитель мед. услуг')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import CountryRepository from '@/repositories/country';
import ClinicGroupRepository from '@/repositories/clinic/group';
import MspRepository from '@/repositories/msp';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            countries: new CountryRepository(),
            groups: new ClinicGroupRepository(),
            msp: new MspRepository(),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                name: null,
                currency: null,
                official_name: null,
                status: null,
                city: null,
                country: null,
                group: null,
                ...fromState,
            };
        },
    },
};
</script>
