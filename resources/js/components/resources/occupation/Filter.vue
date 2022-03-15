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
                    property="clinic"
                    :filterable="true"
                    :label="__('Клиника')"
                />
            </el-col>
            <el-col :span="8">
                <form-date
                    :entity="filter"
                    property="date_from"
                    :label="__('Дата начала')" />
            </el-col>
            <el-col :span="8">
                <form-date
                    :entity="filter"
                    property="date_to"
                    :label="__('Дата окончания')" />
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
                accessLimit: this.$isAccessLimited('departments'),
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            let today = this.$moment().format('YYYY-MM-DD');

            this.filter = {
                clinic: null,
                date_from: today,
                date_to: today,
                active: 1,
                ...fromState,
            };
        },
    },
     watch: {
        ['filter.date_to'](val) {
            if(val < this.filter.date_from){
                this.filter.date_to = this.filter.date_from;
            }
        },
    },
};
</script>
