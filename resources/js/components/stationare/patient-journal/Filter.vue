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
                <form-date
                    :entity="filter"
                    :clearable="true"
                    property="date_from"
                    :label="__('Дата от')" />
            </el-col>
            <el-col :span="6">
                <form-date
                    :entity="filter"
                    :clearable="true"
                    property="date_to"
                    :label="__('Дата до')" />
            </el-col>
            <el-col :span="6">
                <form-input-search
                    :entity="filter"
                    property="patient_card_number"
                    :clearable="true"
                    :label="__('№ карты')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import FilterMixin from '@/mixins/filter';
import InformationSourceRepository from '@/repositories/patient/information-source';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository(),
            specializations: new SpecializationRepository(),
            sources: new InformationSourceRepository(),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter =  {
                date_from: null,
                date_to: null,
                patient_card_number: null,
                ...fromState,
            };
        },
    },
};
</script>
