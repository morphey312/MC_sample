<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-input
                    :entity="model"
                    property="name"
                    :label="__('Название лаборатории')" />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="model"
                    :options="externalLaboratories"
                    property="external_id"
                    :label="__('Лаборатория ЛИС')" />
            </el-col>
            <el-col :span="8">
                <form-checkbox
                    :entity="model"
                    property="disabled"
                    class="aligned-checkbox"
                    :label="__('Не использовать')" />
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-row name="clinics">
                    <transfer-table
                        v-if="model.loading === false"
                        :items="clinics"
                        :fields="clinicFields"
                        v-model="model.clinics"
                        value-key="clinic_id"
                        :left-title="__('Клиника')"
                        left-width="280px"
                        :right-title="__('Клиника')"
                        right-width="280px"
                    >
                        <template slot="priority" slot-scope="props">
                            <form-input
                                property="priority"
                                :entity="props.rowData.data"
                                type="number"
                                :min="0"
                                :step="1"
                                control-size="mini"
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
import LaboratoryClientRepository from '@/repositories/analysis/laboratory/client';

export default {
    props: {
        model: Object
    },
    data(){
        return {
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('laboratories')
            }),
            clinicFields: [
                {
                    name: 'priority',
                    title: __('Приоритет'),
                    width: '80px',
                }
            ],
            externalLaboratories: [],
        }
    },
    mounted() {
        let client = new LaboratoryClientRepository();
        client.fetchList(null, null, null, 'laboratories').then(response => {
            if (response && response.data) {
                this.externalLaboratories = response.data;
            }
        });
    }
}
</script>
