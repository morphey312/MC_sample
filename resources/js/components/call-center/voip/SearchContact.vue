<template>
    <div class="sections-wrapper">
        <el-tabs v-model="activeTab">
            <el-tab-pane
                :lazy="true"
                :label="__('Пациенты')" 
                name="patients">
                <section>
                    <search-patient
                        @selected="patientSelected"
                        @cancel="cancel" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Сотрудники')" 
                name="employees">
                <section>
                    <search-employee
                        @selected="employeeSelected"
                        @cancel="cancel" />
                </section>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import SearchPatient from '@/components/patients/search/Search.vue';
import SearchEmployee from '@/components/resources/employee/search-modal/Search.vue';

export default {
    components: {
        SearchPatient,
        SearchEmployee,
    },
    props: {
        showTab: {
            type: String,
            default: 'patients',
        },
    },
    data() {
        return {
            activeTab: this.showTab,
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        patientSelected(patient) {
            this.$emit('patientSelected', patient);
        },
        employeeSelected(employee) {
            this.$emit('employeeSelected', employee);
        },
    }
};
</script>