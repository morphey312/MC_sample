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
                    property="phone"
                    :clearable="true"
                    :label="__('Телефон')" />
            </el-col>
            <el-col :span="6">
                <form-input-search
                    :entity="filter"
                    property="firstname"
                    :clearable="true"
                    :label="__('Имя')" />
                <form-input-search
                    :entity="filter"
                    property="email"
                    :clearable="true"
                    label="E-mail" />
            </el-col>
            <el-col :span="6">
                <form-input-search
                    :entity="filter"
                    property="middlename"
                    :clearable="true"
                    :label="__('Отчество')" />                
                <form-row
                    name="birth_dates"
                    :label="__('Дата рождения')">
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
            <el-col :span="6">      
                <form-row
                    name="reg_dates"
                    :label="__('Дата регистрации')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_from" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_to"
                        />
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    options="registration_status"
                    :clearable="true"
                    property="status"
                    :label="__('Статус')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    methods: {
        initFilter(fromState = {}) {
            this.filter =  {
                firstname: null,
                lastname: null,
                middlename: null,
                phone: null,
                email: null,
                birthday_from: null,
                birthday_to: null,
                created_from: null,
                created_to: null,
                status: null,
                ...fromState,
            };
        },
    },
    watch: {
        ['filter.birthday_from'](val) {
            this.filter.birthday_to = val;
        },
        ['filter.created_from'](val) {
            this.filter.created_to = val;
        },
    },
};
</script>