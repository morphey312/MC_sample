<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :label="__('Клиника')"
                    :filterable="true"
                    :options="clinics"
                    property="clinic_id"
                    :disabled="disableField" />
                <form-date
                    :label="__('Дата начала ограничения')"
                    :entity="model"
                    property="date_from" />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :label="__('Специализация')"
                    :options="specializations"
                    property="specialization_id"
                    :disabled="disableField" />
                <form-date
                    :label="__('Дата окончания ограничения')"
                    :entity="model"
                    property="date_to" />
            </el-col>
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="limitation"
                    :label="__('Минимальное количество записей')"
                    type="number"
                    :step="1"
                    :min="minLimit" />
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
        model: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        let specializations = new SpecializationRepository({
            limitClinics: this.limitClinics,
            filters: this.getSpecializationsFilters(),
        });
        specializations.watch('data', (repo, prop, data) => {
            this.loadedSpecializations = data;
        })

        return {
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
            }),
            specializations,
            loadedSpecializations: [],
            disableField: !this.model.isNew(),
            minLimit: 0,
        }
    },
    methods: {
        findLimitation(id) {
            let item = this.loadedSpecializations.find(specialization => specialization.id == id);
            return item ? Number(item.limitation) : 0;
        },
        getSpecializationsFilters() {
            return _.onlyFilled({
                clinic: this.model.clinic_id,
            });
        },
    },
    watch: {
        ['model.clinic_id'](val) {
            this.specializations.setFilters(this.getSpecializationsFilters());
        },
        ['model.specialization_id'](val) {
            if(val) {
                let limitation = this.findLimitation(val);
                this.minLimit = limitation;
                this.model.limitation = limitation;
            }
        },
    },
}
</script>
