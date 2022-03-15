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
                    property="hasPrice.clinic"
                    :options="clinics"
                    :filterable="true"
                    :label="__('Клиника')"/>
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    property="specialization"
                    :label="__('Специализация')"/>
            </el-col>
        </el-row>
	</search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import SpecializationRepository from '@/repositories/specialization';
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
                    same_group: this.initialState.hasPrice.clinic,
                },
            }),
        }
    },
    methods: {
        getSpecializations() {
            let specialization = new SpecializationRepository();
            specialization.fetchList(this.getSpecializationFilter()).then((response) => {
                this.specializations = response;
            })
        },
        getSpecializationFilter() {
            return _.onlyFilled({
                clinic: this.filter.hasPrice.clinic,
            });
        },
    	initFilter(fromState = {}) {
            this.filter = {
                name: null,
                specialization: null,
                disabled: false,
                specialization_group: null,
                hasPrice: {},
                ...fromState,
            };
        },
    },
    watch: {
        ['filter.hasPrice.clinic']() {
            this.getSpecializations();
        }
    }
}

</script>
