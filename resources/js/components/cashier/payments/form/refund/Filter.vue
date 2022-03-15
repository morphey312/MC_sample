<template>
	<search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        :button-submit-text="__('Фильтровать')"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-input
                    :entity="filter"
                    property="patient_card_number"
                    :clearable="true"
                    :label="__('Номер карты')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics_list"
                    property="appointment_clinic"
                    :clearable="true"
                    :filterable="true"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    property="doctor_specialization"
                    :label="__('Специализация врача')" />
            </el-col>
        </el-row>
	</search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';

export default {
	mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics_list:  new ClinicRepository({
                accessLimit: this.$isAccessLimited('payments'),
            }),
            specializations:  new SpecializationRepository({
                accessLimit: this.$isAccessLimited('payments'),
            }),
        }
    },
    methods: {
    	initFilter(fromState = {}){
            this.filter = {
                patient_card_number: null,
                appointment_clinic: null,
                doctor_specialization: null,
                ...fromState,
            };
        },
    },
}	

</script>