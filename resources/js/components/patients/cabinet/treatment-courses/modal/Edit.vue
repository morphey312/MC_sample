<template>
    <treatment-course-edit-form 
        :model="model"
        :doctors="doctors">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button type="primary" @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </treatment-course-edit-form>
</template>

<script>
import TreatmentCourseEditForm from './EditForm';
import Call from '@/models/call';
import EmployeeRepository from '@/repositories/employee';
import EditMixin from '@/mixins/generic-edit';
import CONSTANTS from '@/constants';

export default {
    components: {
        TreatmentCourseEditForm,
    },
    mixins: [
        EditMixin,
    ],
    props: {
        course: {
            type: Object,
        },
    },
    data() {
        return {
            model: this.course.clone(),
            clinic: this.course.appointments ? this.course.appointments[0].clinic_id : null,
            doctors: [],
        }
    },
    mounted() {
        this.model.fetch().then(() => {
            if (this.clinic && _.isVoid(this.model.doctor_id)) {
                this.getDoctors();
            }
        });
    },
    methods: {
        getDoctors() {
            let doctor = new EmployeeRepository();
            let filters = {
                employee_clinic: {
                    clinic: this.clinic,
                    specialization: this.model.card_specialization_id,
                    position_type: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                }
            }
            console.log(filters);
            doctor.fetchList(filters).then(response => {
                this.doctors = response;
            });
        },
    },
}
</script>
