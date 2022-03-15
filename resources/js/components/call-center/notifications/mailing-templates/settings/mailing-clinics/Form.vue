<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="model"
                    :options="clinics"
                    property="clinic_id"
                    :filterable="true"
                    :label="__('Клиника')"
                />
            </el-col>
            <el-col :span="12">
                <form-checkbox
                    :entity="model"
                    property="active"
                    class="aligned-checkbox"
                    :label="__('Активная')"/>
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-row name="spec_table">
                    <transfer-table
                        v-if="model.loading === false"
                        key="specialization_id"
                        :items="specializations"
                        :fields="specializationFields"
                        v-model="model.specializations"
                        value-key="specialization_id"
                        :left-title="__('Специализация')"
                        left-width="225px"
                        :right-title="__('Специализация')"
                        right-width="225px"
                    >
                        <template slot="custom_name" slot-scope="props">
                            <form-input
                                property="custom_name"
                                :entity="props.rowData.data"
                                css-class="table-row"/>
                        </template>
                    </transfer-table>
                </form-row>
            </el-col>
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>
<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from "@/repositories/specialization";

export default {
    props: {
        model: Object,
    },
    data() {
        return {
            specializationFields: [
                {
                    name: 'custom_name',
                    title: __('Спец. имя'),
                    width: '150px',
                },
            ],
            specializations: new SpecializationRepository({
                status: 1
            }),
            clinics: new ClinicRepository({
                filters: this.getClinicFilters()
            }),
        }
    },
    watch: {
        ['model.notification_template_id']() {
            this.clinics.setFilters(this.getClinicFilters())
        }
    },
    methods: {
        getClinicFilters() {
            if (this.model.clinic_id) {
                return _.onlyFilled({
                    not_in_template_settings: [this.model.notification_template_id, this.model.clinic_id]
                })
            }
            return _.onlyFilled({
                not_in_template_settings: [this.model.notification_template_id]
            })
        }
    }
}
</script>
