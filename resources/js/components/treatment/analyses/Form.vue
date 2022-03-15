<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input
                            :entity="model"
                            property="name"
                            :label="__('Название анализа')" />
                        <form-input
                            :entity="model"
                            property="laboratory_code"
                            :label="__('Код анализа лаборатории')" />
                    </el-col>
                    <el-col :span="12">
                        <form-select
                            :entity="model"
                            :options="laboratories"
                            :filterable="true"
                            property="laboratory_id"
                            :label="__('Лаборатория')" />
                        <form-checkbox
                            :entity="model"
                            property="disabled"
                            :label="__('Не использовать анализ при добавлении новых записей пациентов')" />
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="24">
                        <form-text
                            :entity="model"
                            property="description"
                            :rows="1"
                            :autosize="true"
                            :label="__('Описание')"
                        />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <form-row name="clinics">
                    <transfer-table
                        v-if="model.loading === false"
                        :items="clinics"
                        :fields="clinicFields"
                        v-model="model.clinics"
                        value-key="clinic_id"
                        :left-title="__('Клиника')"
                        left-width="190px"
                        :right-title="__('Клиника')"
                        right-width="190px">
                        <template slot="code" slot-scope="props">
                            <form-input
                                :entity="props.rowData.data"
                                property="code"
                                control-size="mini"
                                css-class="table-row" />
                        </template>
                        <template slot="duration_days" slot-scope="props">
                            <form-input-number
                                :entity="props.rowData.data"
                                property="duration_days"
                                :min="0"
                                control-size="mini"
                                css-class="table-row" />
                        </template>
                    </transfer-table>
                </form-row>
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import LaboratoryRepository from '@/repositories/analysis/laboratory';

export default {
    props: {
        model: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
            }),
            laboratories: new LaboratoryRepository(),
            clinicFields: [
                {
                    name: 'code',
                    title: __('Код'),
                    width: '80px',
                },
                {
                    name: 'duration_days',
                    title: __('Кол-во дней'),
                    width: '80px',
                },
            ],
        };
    },
    watch: {
        'model.clinics': function (newClinics) {
            this.laboratories.setFilters({clinics: newClinics.map((item) => item.clinic_id)});
        }
    },
}
</script>
