<template>
    <div class="separate-form analysis-modal-form" v-loading="loading">
        <el-tabs v-model="activeTab" class="tab-group-grey">
            <el-tab-pane
                :lazy="true"
                :label="__('Бланки клиники')"
                name="clinic" >
                <section class="pt-0 pb-0">
                    <clinic-table  
                        :appointment="appointment"
                        :clinic-requisites="clinicRequisites"
                        :doctor-specialization-list="doctorSpecializationList" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Бланки МОЗ')"
                name="moz" >
                <section class="pt-0 pb-0">
                    <moz-table 
                        :appointment="appointment"
                        :clinic-requisites="clinicRequisites"
                        :doctor-specialization-list="doctorSpecializationList" />
                </section>
            </el-tab-pane>
        </el-tabs>
        <div class="dialog-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import ClinicTable from './ClinicTable.vue';
import MozTable from './MozTable.vue';
import Employee from '@/models/employee';

export default {
    components: {
        ClinicTable,
        MozTable,
    },
    props: {
        appointment: Object,
        clinicRequisites: Object,
    },
    data() {
        return {
            activeTab: 'clinic',
            employee: new Employee({id: this.appointment.doctor_id}),
            loading: false,
            doctorSpecializationList: this.appointment.doctor_specializations,
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        getDoctorSpecializations() {
            return this.doctorSpecializationList.map(item => item.id);
        }
    }
}
</script>
