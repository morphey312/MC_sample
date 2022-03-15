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
                    :clearable="true"
                    property="egrpo"
                    :label="__('ЕГРПОУ(ОКПО)')"
                />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="name"
                    :label="__('Название')"
                />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="filter"
                    :clearable="true"
                    property="short_name"
                    :label="__('Краткое название')"
                />
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
                accessLimit: this.$isAccessLimited('employees'),
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: null,
                egrpo: null,
                name: null,
                short_name: null,
                ...fromState,
            };
        },
    },
};
</script>
