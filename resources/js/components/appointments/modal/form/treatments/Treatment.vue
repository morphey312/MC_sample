<template>
    <el-row class="tab-switcher" v-if="!model.loading">
        <el-tabs v-model="currentTab" class="tab-group-grey">
            <el-tab-pane
                name="service"
                :label="__('Медицинские услуги')">
                <service-block
                    :appointment-data="appointmentData"
                    :model="model"
                    :enquiry="enquiry"
                    :insurance-policy="insurancePolicy"
                    :show-extra-fields="showExtraFields"
                    @assigned-add="assignedAddServices"
                    @services-loaded="servicesLoaded"
                />
            </el-tab-pane>
            <el-tab-pane
                name="analysis"
                :label="__('Анализы')">
                <analysis-block
                    :specialization=specialization
                    :appointment-data="appointmentData"
                    :model="model"
                    :enquiry="enquiry"
                    :insurance-policy="insurancePolicy"
                    :show-extra-fields="showExtraFields"
                    @assigned-add="assignedAddAnalyses" />
            </el-tab-pane>
        </el-tabs>
    </el-row>
</template>

<script>
import ServiceBlock from './service/Service.vue';
import AnalysisBlock from './analysis/Analysis.vue';

export default {
    components: {
        ServiceBlock,
        AnalysisBlock,
    },
    props:{
        specialization: {
            type: Object,
            default: () => ({})
        },
        appointmentData: {
            type: Object,
            default: () => ({})
        },
        model: {
            type: Object,
            default: () => ({})
        },
        enquiry: Object,
        insurancePolicy: Object,
    },
    data() {
        return {
            currentTab: 'service',
        };
    },
    computed: {
        showExtraFields() {
            return !_.isNil(this.insurancePolicy);
        },
    },
    methods: {
        assignedAddServices(assigned) {
            this.$emit('assigned-add', {services: assigned});
        },
        assignedAddAnalyses(assigned) {
            this.$emit('assigned-add', {analyses: assigned});
        },
        servicesLoaded() {
            this.$emit('services-loaded');
        },
    }
}
</script>
