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
                <form-input
                    :entity="filter"
                    property="name"
                    :clearable="true"
                    :label="__('Название')"
                />
            </el-col>
            <el-col :span="6">
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
                <form-input
                    :entity="filter"
                    property="discount_percent"
                    :clearable="true"
                    :label="__('Скидка (%)')"
                    type="number"
                    :step="1"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import FilterMixin from '@/mixins/filter';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('analysis-results'),
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: null,
                name: null,
                discount_percent: null,
                ...fromState,
            };
        },
    }
};
</script>
