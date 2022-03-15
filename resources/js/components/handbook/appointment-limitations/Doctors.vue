<template>
    <div>
        <form-row name="show-doctors" >
            <el-checkbox v-model="show">
                {{ __('Показать уволенных сотрудников') }}
            </el-checkbox>
        </form-row>
        <doctor-table 
            :doctors="activeDoctors"
            @check-percent="checkPercent" />
        <div>&nbsp;</div>
        <doctor-table 
            v-if="show" 
            :doctors="notWorkingDoctors" 
            :disable="true" />
        <div class="form-footer text-right">
            <el-button
                :disabled="invalidPercents"
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="invalidPercents"
                @click="saveModel">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import DoctorTable from './DoctorTable.vue';
import CONSTANTS from '@/constants';
import DoctorMixin from './mixins/doctor';

export default {
    mixins: [
        DoctorMixin,
    ],
    components: {
        DoctorTable
    },
    data() {
        return {
            activeDoctors: [],
            notWorkingDoctors: [],
            show: false,
        }
    },
    mounted() {
        this.getDoctors().then(() => this.castDoctors());
    },
    methods: {
        getDoctors() {
            let filters = {
                skipId: this.getModelDoctorsId(),
                ...this.doctorFilters()
            };

            return this.getDoctorsList(filters).then((response) => {
                this.model.doctors = [
                    ...this.model.doctors,
                    ...this.mapDoctors(response)
                ];
                return Promise.resolve();
            });
        },
        castDoctors() {
            this.model.doctors.forEach((doctor) => {
                if (doctor.status && doctor.status == CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING) {
                    this.notWorkingDoctors.push(doctor);
                } else {
                    this.activeDoctors.push(doctor);
                }
            });
        },
        getModelDoctorsId() {
            let list = [];
            this.model.doctors.forEach(item => list.push(item.doctor_id));
            return list;
        },
    },
}
</script>