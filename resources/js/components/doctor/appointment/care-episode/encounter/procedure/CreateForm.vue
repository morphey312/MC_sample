<template>
    <procedure-form
        :model="model"
        :services="services"
        :clinics="clinics"
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
    </procedure-form>
</template>

<script>
import Procedure from '@/models/ehealth/encounter/procedure';
import ProcedureForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        Procedure,
        ProcedureForm,
    },
    props: {
        encounter: Object,
        services: Array,
        clinic_id: Number,
        doctor_id: Number,
    },
    data() {
        return {
            clinics: new ClinicRepository(),
            doctorsRepository: new EmployeeRepository({
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
            }),
            model: new Procedure({
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
