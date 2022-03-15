<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :label="__('Данные о сотруднике')"
            name="info">
            <section>
                <employee-form :model="model">
                    <edit-buttons
                        slot="buttons"
                        @cancel="cancel"
                        @update="update"
                        @updateAndPost="updateAndPost" />
                </employee-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :label="__('Клиники сотрудника')"
            name="clinics">
            <error-catcher :catch="clinicErrors" />
            <section v-if="tabClinics">
                <employee-clinics
                    :employee="item"
                    :modal-component="modalComponent"
                    @clinics-updated="clinicsUpdated">
                    <edit-buttons
                        slot="buttons"
                        @cancel="cancel"
                        @update="update"
                        @updateAndPost="updateAndPost" />
                </employee-clinics>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :label="__('Данные для входа в Checkbox')"
            name="checkbox-credentials">
            <section v-if="tabCheckboxCredentials">
                <checkbox-credentials
                    :employee="item"
                    :modal-component="modalComponent">
                    <edit-buttons
                        slot="buttons"
                        @cancel="cancel"
                        @update="update"
                        @updateAndPost="updateAndPost" />
                </checkbox-credentials>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :label="__('Документы')"
            name="documents">
            <error-catcher :catch="documentsErrors" />
            <section v-if="tabDocuments">
                <employee-documents
                    :employee="item"
                    :modal-component="modalComponent">
                    <edit-buttons
                        slot="buttons"
                        @cancel="cancel"
                        @update="update"
                        @updateAndPost="updateAndPost" />
                </employee-documents>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :label="__('Образование')"
            name="educations">
            <error-catcher :catch="educationErrors" />
            <section v-if="tabEducations">
                <employee-educations
                    :employee="item"
                    :modal-component="modalComponent">
                    <edit-buttons
                        slot="buttons"
                        @cancel="cancel"
                        @update="update"
                        @updateAndPost="updateAndPost" />
                </employee-educations>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :label="__('Курсы')"
            name="qualifications">
            <error-catcher :catch="qualificationErrors" />
            <section v-if="tabQualifications">
                <employee-qualifications
                    :employee="item"
                    :modal-component="modalComponent">
                    <edit-buttons
                        slot="buttons"
                        @cancel="cancel"
                        @update="update"
                        @updateAndPost="updateAndPost" />
                </employee-qualifications>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :label="__('Специализации')"
            name="specialities">
            <error-catcher :catch="specialityErrors" />
            <section v-if="tabSpecialities">
                <employee-specialities
                    :employee="item"
                    :modal-component="modalComponent">
                    <edit-buttons
                        slot="buttons"
                        @cancel="cancel"
                        @update="update"
                        @updateAndPost="updateAndPost" />
                </employee-specialities>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :label="__('Ученые степени')"
            name="science-degrees">
            <error-catcher :catch="degreeErrors" />
            <section v-if="tabDegree">
                <employee-science-degrees
                    :employee="item"
                    :modal-component="modalComponent">
                    <edit-buttons
                        slot="buttons"
                        @cancel="cancel"
                        @update="update"
                        @updateAndPost="updateAndPost" />
                </employee-science-degrees>
            </section>
        </el-tab-pane>
        <el-tab-pane
            v-if="hasServiceTypes"
            :lazy="true"
            :label="__('Роли')"
            name="ehealth-roles">
            <employee-service-types
                :employee="item"
                :modal-component="modalComponent">
                <edit-buttons
                    slot="buttons"
                    @cancel="cancel"
                    @update="update"
                    @updateAndPost="updateAndPost" />
            </employee-service-types>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import EmployeeForm from './Form.vue';
import EmployeeClinics from './Clinics.vue';
import EmployeeDocuments from './Documents.vue';
import EmployeeEducations from './Educations.vue';
import EmployeeQualifications from './Qualifications.vue';
import EmployeeSpecialities from './Specialities.vue';
import EmployeeScienceDegrees from './ScienceDegrees.vue';
import EmployeeServiceTypes from './ServiceTypes.vue';
import Employee from '@/models/employee';
import EditMixin from '@/mixins/generic-edit';
import EditButtons from './EditButtons.vue';
import EhealthMixin from './mixins/ehealth';
import FormTab from './mixins/form-tabs';
import CheckboxCredentials from "./CheckboxCredentials";

export default {
    mixins: [
        EditMixin,
        EhealthMixin,
        FormTab,
    ],
    components: {
        EmployeeForm,
        EmployeeClinics,
        EmployeeDocuments,
        EmployeeEducations,
        EmployeeQualifications,
        EmployeeSpecialities,
        EmployeeScienceDegrees,
        EmployeeServiceTypes,
        EditButtons,
        CheckboxCredentials,
    },
    props: {
        item: Object,
        modalComponent: Object,
    },
    data() {
        return {
            model: new Employee({
                id: this.item.id,
                copy_to_portal: this.item.copy_to_portal,
            }),
        };
    },
    mounted() {
        this.model.fetch(['user.permissions', 'user.roles']);
    },
    methods: {
        clinicsUpdated() {
            this.$emit('clinicsUpdated');
        },
        updateAndPost() {
            this.$clearErrors();
            this.prepareRequest().then(() => {
                this.update();
            });
        }
    },
}
</script>
