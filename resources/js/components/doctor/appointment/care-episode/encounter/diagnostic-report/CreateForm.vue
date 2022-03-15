<template>
    <diagnostic-report-form
        :model="model"
        :services="services"
        :clinics="clinics"
        :diagnosisRepository="diagnosisRepository"
        :doctorsRepository="doctorsRepository">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
            <el-button
                type="primary"
                @click="createAndSubmit">
                {{ __('Создать и отправить в e-Health') }}
            </el-button>
        </div>
    </diagnostic-report-form>
</template>

<script>
import DiagnosticReport from '@/models/ehealth/encounter/diagnostic-report';
import DiagnosticReportForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import DiagnosisRepository from '@/repositories/diagnosis';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
      DiagnosticReportForm,
    },
    props: {
        encounter: Object,
        services: Array,
        clinic_id: Number,
        doctor_id: Number,
    },
    data() {
        return {
            diagnosisRepository: new DiagnosisRepository(),
            clinics: new ClinicRepository(),
            doctorsRepository: new EmployeeRepository({
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
            }),
            model: new DiagnosticReport({
                encounter_id: this.encounter.id,
                division: this.clinic_id,
                performer: this.doctor_id,
                recorded_by: this.$store.state.user.isDoctor ? this.$store.state.user.id : null,
            }),
        }
    },
    methods: {
        createAndSubmit() {},
    },
}
</script>
