<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника')"
                />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="date_start"
                            :clearable="true"
                        />
                        <form-date
                            :entity="filter"
                            property="date_end"
                            :clearable="true"
                        />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="price_agreement_act_type"
                    property="type"
                    :label="__('Тип акта')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    options="price_agreement_act_status"
                    property="status"
                    :label="__('Статус акта')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import ClinicRepository from "@/repositories/clinic";
import CONSTANTS from "@/constants";

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('price-agreement-acts')
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                date_end: null,
                date_start: null,
                type: null,
                status: null,
                clinic: [],
                ...fromState,
            };
        },
    },
};

</script>
