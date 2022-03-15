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
                <form-select
                    :entity="filter"
                    :options="clinics"
                    property="clinic"
                    :filterable="true"
                    :multiple="true"
                    :label="__('Клиника')" />
                <filter-patient
                    :entity="filter"
                    patient-name-prop="patient_name"
                    patient-id-prop="patient"
                    :label="__('ФИО пациента')" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="filter"
                    property="patient_card_number"
                    :clearable="true"
                    :label="__('Номер карты')" />
                <form-select
                    :entity="filter"
                    :options="specializations"
                    :clearable="true"
                    :filterable="true"
                    :multiple="true"
                    property="card_specialization"
                    :label="__('Специализация карты')" />
            </el-col>
            <el-col :span="8">
                <form-row 
                    name="period"
                    :label="__('Период')">
                    <div class="form-input-group">
                        <form-date
                            :entity="filter"
                            property="createdStart"
                            :editable="false"
                            :clearable="true" />
                        <form-date
                            :entity="filter"
                            property="createdEnd"
                            :editable="false"
                            :clearable="true" />
                    </div>
                </form-row>
                <form-switch
                    :entity="filter"
                    :options="payment_types"
                    property="payment_type"
                    :label="__('Тип платежа')" />
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
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('payments'),
            }),
            specializations: new SpecializationRepository({
                limitClinics: this.$isAccessLimited('payments'),
                filters: {id: this.$store.state.user.specializations},
            }),
            payment_types: this.setPaymentTypes(),
        }
    },
    methods: {
        initFilter(fromState = {}){
            let today = this.$moment().format('YYYY-MM-DD');

            this.filter = {
                clinic: this.$store.state.user.clinics,
                patient: null,
                patient_name: null,
                doctor: [this.$store.state.user.employee_id],
                patient_card_number: null,
                card_specialization: [],
                createdStart: today,
                createdEnd: today,
                payment_type: null,
                ...fromState,
            };
        },
        setPaymentTypes() {
            return [{ id: null, value: __('Все')}, ...this.$handbook.getOptions('payment_type')];
        },
    }
}
</script>