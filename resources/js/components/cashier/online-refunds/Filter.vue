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
                <filter-patient
                    :entity="filter"
                    patient-name-prop="patient_name"
                    patient-id-prop="patient"
                    :label="__('ФИО пациента')" />
                <form-input-search
                    :entity="filter"
                    property="email"
                    :clearable="true"
                    label="E-mail"
                />
            </el-col>
            <el-col :span="8">
                <form-input-search
                    :entity="filter"
                    property="phone"
                    :clearable="true"
                    :label="__('Телефон')"
                />
                <form-select
                    :entity="filter"
                    :options="clinics"
                    :clearable="true"
                    :multiple="true"
                    :filterable="true"
                    property="clinic"
                    :label="__('Клиника заявки')"
                />
            </el-col>
            <el-col :span="8">
                <form-row
                    name="creates"
                    :label="__('Период отправления заявки')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_start" />
                        <form-date
                            :entity="filter"
                            :clearable="true"
                            property="created_end"
                        />
                    </div>
                </form-row>
                <form-select
                    :entity="filter"
                    :options="refunders"
                    property="refunder"
                    :clearable="true"
                    :filterable="true"
                    :label="__('Вернул')"
                />
            </el-col>
        </el-row>
    </search-filter>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import FilterMixin from '@/mixins/filter';

export default {
    mixins: [
        FilterMixin,
    ],
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('payments'),
            }),
            refunders: new EmployeeRepository({
                limitClinics: this.$isAccessLimited('payments'),
            }),
        };
    },
    methods: {
        initFilter(fromState = {}) {
            this.filter = {
                clinic: [],
                patient: null,
                patient_name: null,
                phone: null,
                email: null,
                created_start: null,
                created_end: null,
                refunder: null,
                ...fromState,
            };
        },
    },
};
</script>
