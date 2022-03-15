<template>
	<search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-input
                    :entity="filter"
                    property="name"
                    :clearable="true"
                    :label="__('Название')"/>
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    property="clinic"
                    :options="clinics"
                    :filterable="true"
                    :label="__('Клиника')"/>
            </el-col>
        </el-row>
	</search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';

export default {
	mixins: [
        FilterMixin,
    ],
    data() {
        return {
            specializations: [],
            clinics: new ClinicRepository({
                filters: {
                    same_group: this.initialState.clinic,
                },
            }),
        }
    },
    methods: {
    	initFilter(fromState = {}) {
            this.filter = {
                name: null,
                clinic: null,
                ...fromState,
            };
        },
    },
}

</script>
