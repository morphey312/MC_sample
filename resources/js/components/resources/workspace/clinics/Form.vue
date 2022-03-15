<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="6">
                <form-select
                    :entity="model"
                    :options="clinics"
                    property="clinic_id"
                    :filterable="true"
                    :label="__('Клиника')" />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="model"
                    :options="specializations"
                    property="specializations"
                    :multiple="true"
                    :label="__('Специализации')" />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="sip_number"
                    :label="__('Номер SIP')" />
            </el-col>
            <el-col :span="6">
                <form-input
                    :disabled="disabledDuration"
                    :entity="model"
                    property="appointment_duration"
                    :label="__('Длительность приема')"
                    type="number"
                    :step="5"
                    :min="5" />
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';

export default {
    props: {
        model: {
            type: Object,
        },
        workspace: {
            type: Object,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('workspaces')
            }),
            specializations: [],
            specializationFields: [],
        };
    },
    mounted() {
        this.getSpecializations(this.model.clinic_id);
    },
    computed: {
        disabledDuration() {
            return this.workspace.has_day_sheet == false;
        },
    },
    methods: {
        getSpecializations(clinic) {
            this.specializations = [];
            if (clinic) {
                let specialization = new SpecializationRepository();
                specialization.fetchList({clinic}).then((response) => {
                    this.specializations = response;
                });
            }
        },
    },
    watch: {
        ['model.clinic_id'](val) {
             this.getSpecializations(val);
        },
    },
}
</script>
