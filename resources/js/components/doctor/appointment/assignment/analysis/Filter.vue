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
                    property="laboratoryCode"
                    :clearable="true"
                    :label="__('Код лаборатории')"/>
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="filter"
                    property="clinicCode"
                    :clearable="true"
                    :label="__('Код клиники')"/>
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="filter"
                    property="name"
                    :clearable="true"
                    :label="__('Название')"/>
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="8">
                <form-input
                    :entity="filter"
                    property="description"
                    :clearable="true"
                    :label="__('Описание')"/>
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    property="hasPrice.clinic"
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
            clinics: new ClinicRepository({
                filters: {
                    same_group: this.initialState.hasPrice.clinic,
                },
            }),
        };
    },
    methods: {
    	initFilter(fromState = {}){
            this.filter = {
                name: null,
                laboratoryCode: null,
                clinicCode: null,
                description: null,
                disabled: false,
                hasPrice: {},
                ...fromState,
            };
        },
    },
}

</script>
