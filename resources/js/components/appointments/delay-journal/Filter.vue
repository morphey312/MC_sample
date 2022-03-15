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
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="appointment.clinic"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="appointment.specialization"
                    :label="__('Специализация записи')" />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="dates"
                    :label="__('Период записи пациентов')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="appointment.date_from"
                            :editable="false"
                            :clearable="true"
                        />
                        <form-date
                            :entity="filter"
                            property="appointment.date_to"
                            :editable="false"
                            :clearable="true"
                        />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filter"
                    :options="delayReasons"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="delay_reason"
                    :label="__('Причина задержки')" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import DelayReasonRepository from '@/repositories/appointment/status/delay-reason';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('appointment-delays')
            }),
            specializations: new SpecializationRepository(),
            delayReasons: new DelayReasonRepository(),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            let today = this.$moment().format('YYYY-MM-DD');
            this.filter = {
                delay_reason: [],
                appointment: {
                    clinic: [],
                    specialization: [],
                    date_from: today,
                    date_to: today,
                },
                ...fromState,
            };
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.filter.appointment.clinic,
            });
        },
    },
    watch: {
        ['filter.appointment.clinic']() {
            this.specializations.setFilters(this.getSpecializationFilters());
        },
    },
};
</script>
