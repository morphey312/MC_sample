<template>
<condition-form
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
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
            <el-button
                type="primary"
                @click="createAndSubmit">
                {{ __('Создать и отправить в e-Health') }}
            </el-button>
        </div>
    </condition-form>
</template>

<script>
import ConditionForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';
import DiagnosisRepository from '@/repositories/diagnosis';
import ClinicRepository from '@/repositories/clinic';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        ConditionForm,
    },
    props: {
        item: Object,
        services: Array,
    },
    data() {
        return {
            diagnosisRepository: new DiagnosisRepository(),
            clinics: new ClinicRepository(),
            doctorsRepository: new EmployeeRepository({
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR},
            }),
            model: this.item.clone(),
        };
    },
    methods: {
        createAndSubmit() {

        },
    },
}
</script>
