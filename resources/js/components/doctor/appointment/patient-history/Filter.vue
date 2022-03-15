<template>
    <search-filter
        :model="filters"
        :show-submit-button="true"
        :show-clear-button="true"
        :auto-search="false"
        :button-submit-text="__('Фильтровать')"
        @changed="changed"
        @cleared="cleared" >
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="filters"
                    :options="record_types"
                    :clearable="true"
                    property="type"
                    :label="__('Операции')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filters"
                    :options="specializations"
                    :clearable="true"
                    property="specialization"
                    :label="__('Специализация')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="filters"
                    :options="doctors"
                    :clearable="true"
                    property="doctor"
                    :label="__('Врач')"
                />
            </el-col>
            <el-col :span="6">
                <form-row
                    name="createdDates"
                    label="">
                    <div class="form-input-group">
                        <form-date
                            :entity="filters"
                            property="date_from"
                            :label="__('Дата, с')" />
                        <form-date
                            :entity="filters"
                            property="date_to"
                            :label="__('Дата, до')" />
                    </div>
                </form-row>
            </el-col>
        </el-row>
    </search-filter>
</template>
<script>
import FilterMixin from '@/mixins/filter';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        FilterMixin,
    ],
    props: {
        filters: null,
        patientCards: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            record_types: [
                {
                    id: CONSTANTS.CARD_RECORD.TYPE.OUTPATIENT_RECORD,
                    value: __('Запись в амбулаторную карту'),
                },
                {
                    id: CONSTANTS.CARD_RECORD.TYPE.DIARY_RECORD,
                    value: __('Запись в дневнике'),
                },
                {
                    id: CONSTANTS.CARD_RECORD.TYPE.PROTOCOL_RECORD,
                    value: __('Запись в протокол исследований'),
                },
                {
                    id: CONSTANTS.CARD_RECORD.TYPE.CARD_ASSIGNMENT,
                    value: __('Назначения'),
                },
                {
                    id: CONSTANTS.CARD_RECORD.TYPE.TREATMENT_ASSIGNMENT,
                    value: __('Назанчение лечения'),
                },
                {
                    id: CONSTANTS.CARD_RECORD.TYPE.CONSULTATION_RECORD,
                    value: __('Назначенные консультации'),
                },
                {
                    id: CONSTANTS.CARD_RECORD.TYPE.PATIENT_DOCUMENT,
                    value: __('Документы'),
                },
                {
                    id: CONSTANTS.CARD_RECORD.TYPE.PRINTED_DOCUMENT,
                    value: __('Печать документа'),
                },
            ],
            specializations: [],
            doctors: [],
        }
    },
    mounted() {
        this.getSpecializations();
        this.getDoctors();
    },
    methods: {
        initFilter() {
            this.filter = {
                type: null,
                specialization: null,
                doctor: null,
                date_from: null,
                date_to: null,
            };
        },
        cleared() {
            this.$emit('cleared');
            this.initFilter();
        },
        getSpecializations() {
            let specialization = new SpecializationRepository();
            specialization.fetchList({patient_card: this.patientCards}).then((response) => {
                this.specializations = response;
            });
        },
        getDoctors() {
            let doctor = new EmployeeRepository();
            let filters = {
                positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                record_card: this.patientCards,
            };

            doctor.fetchList(filters).then((response) => {
                this.doctors = response;
            });
        },
    },
}
</script>
