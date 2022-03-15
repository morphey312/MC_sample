<template>
    <page
        :title="__('Бланки документов')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                v-if="$canAccess('patient-documents')"
                :lazy="true"
                :label="__('Бланки клиники')"
                name="clinic" >
                <section class="pt-0 shrinkable">
                    <clinic-list />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('patient-documents')"
                :lazy="true"
                :label="__('Бланки МОЗ')"
                name="moz" >
                <section class="pt-0 shrinkable">
                    <moz-list />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('protocol-templates')"
                :lazy="true"
                :label="__('Бланки протоколов исследований')"
                name="protocols" >
                <section class="pt-0 shrinkable">
                    <protocols-list />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('analysis-templates')"
                :lazy="true"
                :label="__('Бланки результатов анализов')"
                name="analyses" >
                <section class="pt-0 shrinkable">
                    <analysis-templates-list />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('patient-clinic-routes')"
                :lazy="true"
                :label="__('Маршруты пациентов')"
                name="patient-clinic-routes" >
                <section class="pt-0 shrinkable">
                    <patient-clinic-routes />
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import ClinicList from './patient-documents/Clinic.vue';
import MozList from './patient-documents/Moz.vue';
import ProtocolsList from '@/components/treatment/ProtocolTemplates.vue';
import AnalysisTemplatesList from '@/components/treatment/AnalysisTemplates.vue';
import PatientClinicRoutes from './patient-clinic-routes/ClinicRoutes.vue';

export default {
    components: {
        ClinicList,
        MozList,
        ProtocolsList,
        AnalysisTemplatesList,
        PatientClinicRoutes,
    },
    data() {
        let defaultTab = 'clinic';
        if (!this.$canAccess('patient-documents')) {
            if (this.$canAccess('protocol-templates')) {
                defaultTab = 'protocols';
            } else if (this.$canAccess('analysis-templates')) {
                defaultTab = 'analyses';
            } else if (this.$canAccess('patient-clinic-routes')) {
                defaultTab = 'patient-clinic-routes';
            }
        }

        return {
            activeTab: defaultTab,
        }
    },
};
</script>
