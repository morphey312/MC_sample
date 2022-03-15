<template>
    <div>
        <doctor-table 
            :doctors="model.doctors" 
            @check-percent="checkPercent" />
        <div class="form-footer text-right">
            <el-button
                :disabled="invalidPercents"
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="invalidPercents || blankPercents"
                @click="saveModel">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import DoctorTable from './DoctorTable.vue';
import DoctorMixin from './mixins/doctor';

export default {
    mixins: [
        DoctorMixin,
    ],
    components: {
        DoctorTable,
    },
    mounted() {
        this.getDoctors();
    },
    methods: {
        getDoctors() {
            this.getDoctorsList(this.doctorFilters()).then((response) => {
                this.model.doctors = this.mapDoctors(response);
            });
        },
    },
}
</script>