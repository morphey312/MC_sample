<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :lazy="true"
            :label="__('Данные о сотруднике')"
            name="info">
            <section>
                <employee-form
                    :has-clinics="hasClinics"
                    :model="model">
                    <create-buttons
                        slot="buttons"
                        :model="model"
                        :has-clinics="hasClinics"
                        @cancel="cancel"
                        @next="create"
                        @complete="complete"
                        @completeAndPost="completeAndPost" />
                </employee-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :disabled="model.isNew()"
            :label="__('Клиники сотрудника')"
            name="clinics">
            <error-catcher :catch="clinicErrors" />
            <section v-if="tabClinics">
                <employee-clinics
                    :employee="model"
                    @created="clinicCreated">
                    <create-buttons
                        slot="buttons"
                        :model="model"
                        :has-clinics="hasClinics"
                        @cancel="cancel"
                        @complete="complete"
                        @completeAndPost="completeAndPost" />
                </employee-clinics>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :disabled="model.isNew()"
            :label="__('Данные для входа в Checkbox')"
            name="checkbox-credentials">
            <section v-if="tabCheckboxCredentials">
                <checkbox-credentials
                    :employee="model"
                    :modal-component="modalComponent">
                    <create-buttons
                        slot="buttons"
                        :model="model"
                        :has-clinics="hasClinics"
                        @cancel="cancel"
                        @complete="complete"
                        @completeAndPost="completeAndPost" />
                </checkbox-credentials>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :disabled="!hasClinics"
            :label="__('Документы')"
            name="documents">
            <error-catcher :catch="documentsErrors" />
            <section v-if="tabDocuments">
                <employee-documents
                    :employee="model"
                    :modal-component="modalComponent">
                    <create-buttons
                        slot="buttons"
                        :model="model"
                        :has-clinics="hasClinics"
                        @cancel="cancel"
                        @complete="complete"
                        @completeAndPost="completeAndPost" />
                </employee-documents>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :disabled="!hasClinics"
            :label="__('Образование')"
            name="educations">
            <error-catcher :catch="educationErrors" />
            <section v-if="tabEducations">
                <employee-educations
                    :employee="model"
                    :modal-component="modalComponent">
                    <create-buttons
                        slot="buttons"
                        :model="model"
                        :has-clinics="hasClinics"
                        @cancel="cancel"
                        @complete="complete"
                        @completeAndPost="completeAndPost" />
                </employee-educations>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :disabled="!hasClinics"
            :label="__('Курсы')"
            name="qualifications">
            <error-catcher :catch="qualificationErrors" />
            <section v-if="tabQualifications">
                <employee-qualifications
                    :employee="model"
                    :modal-component="modalComponent">
                    <create-buttons
                        slot="buttons"
                        :model="model"
                        :has-clinics="hasClinics"
                        @cancel="cancel"
                        @complete="complete"
                        @completeAndPost="completeAndPost" />
                </employee-qualifications>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :disabled="!hasClinics"
            :label="__('Специализации')"
            name="specialities">
            <error-catcher :catch="specialityErrors" />
            <section v-if="tabSpecialities">
                <employee-specialities
                    :employee="model"
                    :modal-component="modalComponent">
                    <create-buttons
                        slot="buttons"
                        :model="model"
                        :has-clinics="hasClinics"
                        @cancel="cancel"
                        @complete="complete"
                        @completeAndPost="completeAndPost" />
                </employee-specialities>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :disabled="!hasClinics"
            :label="__('Ученые степени')"
            name="science-degrees">
            <error-catcher :catch="degreeErrors" />
            <section v-if="tabDegree">
                <employee-science-degrees
                    :employee="model"
                    :modal-component="modalComponent">
                    <create-buttons
                        slot="buttons"
                        :model="model"
                        :has-clinics="hasClinics"
                        @cancel="cancel"
                        @complete="complete"
                        @completeAndPost="completeAndPost" />
                </employee-science-degrees>
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import Employee from '@/models/employee';
import EmployeeForm from './Form.vue';
import EmployeeClinics from './CreateClinics.vue';
import EmployeeDocuments from './Documents.vue';
import EmployeeEducations from './Educations.vue';
import EmployeeQualifications from './Qualifications.vue';
import EmployeeSpecialities from './Specialities.vue';
import EmployeeScienceDegrees from './ScienceDegrees.vue';
import FormTab from './mixins/form-tabs';
import EhealthMixin from './mixins/ehealth';
import CreateButtons from './CreateButtons.vue';
import CheckboxCredentials from "./CheckboxCredentials";

export default {
    mixins: [
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
        CreateButtons,
        CheckboxCredentials,
    },
    props: {
        modalComponent: Object,
    },
    data() {
        return {
            model: new Employee(),
            activeTab: 'info',
            preventClose: false,
            hasClinics: false,
        }
    },
    mounted() {
        this.$safeClose(__('Вы не добавили ни одной клиники.'), () => {
            return this.preventClose;
        });
    },
    beforeDestroy() {
        this.$unsafeClose();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$info(__('Данные сотрудника успешно сохранены'));
                this.activeTab = 'clinics';
                this.preventClose = true;
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        complete() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('created', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        clinicCreated() {
            this.preventClose = false;
            this.hasClinics = true;
        },
        checkPreventClose() {
            if (this.preventClose) {
                this.$warning(__('Вы не завершили процесс добавления сотрудника! Добавьте сотрудника хотя бы в одну клинику, чтобы завершить процесс.'));
                return false;
            }
            return true;
        },
        completeAndPost() {
            this.$clearErrors();
            this.prepareRequest().then(() => {
                this.model.save().then((response) => {
                    this.$emit('created', this.model);
                }).catch((e) => {
                    this.$displayErrors(e);
                });
            });
        }
    },
}
</script>
