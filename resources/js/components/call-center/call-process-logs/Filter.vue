<template>
    <search-filter
        :model="filter"
        :show-submit-button="true"
        :show-clear-button="true"
        :show-collapse-button="true"
        :start-collapsed="startCollapsed"
        @changed="changed"
        @cleared="cleared">
        <el-row :gutter="20">
            <el-col :span="8">
                <div class="form-input-group">
                    <form-select
                        :entity="filter"
                        :clearable="true"
                        :repository="operators"
                        property="operator"
                        :multiple="true"
                        :label="__('Оператор')" />
                    <form-input-search
                        :entity="filter"
                        :clearable="true"
                        property="sip"
                        label="SIP" />
                </div>
                <form-row
                    name="dates"
                    :label="__('Период дат')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_from.date" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="date_to.date" />
                    </div>
                </form-row>
                <form-row
                    name="hours"
                    :label="__('Период времени')">
                    <div class="form-input-group">
                        <form-time
                            :entity="filter"
                            :clearable="true"
                            start="00:00"
                            end="23:45"
                            step="00:15"
                            property="date_from.time" />
                        <form-time
                            :entity="filter"
                            :clearable="true"
                            start="00:15"
                            end="24:00"
                            step="00:15"
                            property="date_to.time" />
                    </div>
                </form-row>
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :clearable="true"
                    options="call_process_status"
                    property="status"
                    :label="__('Статус')" />
                <form-switch
                    :entity="filter"
                    options="call_process_is_patient"
                    property="is_patient"
                    :label="__('Кто звонит')" />
                <form-select
                    :entity="filter"
                    :clearable="true"
                    options="reason_impossibility_of_call_processing"
                    property="unprocessibility_reason"
                    :label="__('Причина невозможности обработки')" />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="filter"
                    :clearable="true"
                    options="call_log_source"
                    property="source"
                    :label="__('Источник обработки')" />
                <form-input-search
                    :entity="filter"
                    :clearable="true"
                    property="phone_number"
                    :label="__('Номер телефона')" />
                <filter-patient
                    :entity="filter"
                    patient-name-prop="patient_name"
                    patient-id-prop="patient"
                    :label="__('Пациент')"
                    @selected="focusOnPatient" />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import FilterMixin from '@/mixins/filter';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS  from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        startCollapsed: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            operators: new EmployeeRepository({
                filters: {hasVoip: true},
            }),
        }
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                operator: [],
                sip: null,
                date_from: {
                    date: null,
                    time: null,
                },
                date_to: {
                    date: null,
                    time: null,
                },
                status: null,
                is_patient: null,
                unprocessibility_reason: null,
                source: null,
                phone_number: null,
                patient: null,
                patient_name: null,
                ...fromState,
            };
        },
        focusOnPatient(patient) {
            this.$nextTick(() => {
                this.initFilter({
                    patient: patient.id,
                });
            });
        },
    },
};
</script>
