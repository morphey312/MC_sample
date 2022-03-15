<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared" >
        <el-row :gutter="20">
            <el-col :span="8">
            	<form-input
                    :entity="filter"
                    property="name_i18n"
                    :clearable="true"
                    :label="__('Название специализации')" />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    options="active_status"
                    :clearable="true"
                    property="status"
                    :label="__('Статус')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('specializations'),
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                name_i18n: null,
                short_name: null,
                clinic: null,
                status: null,
                ...fromState,
            };
        },
    },
};
</script>
