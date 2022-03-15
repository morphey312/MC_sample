<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="8">
                        <form-input-number
                            :entity="model.operator_bonus"
                            property="evaluation"
                            :min="0"
                            control-size="mini"
                            :label="__('Оценочный лист')" />
                    </el-col>
                </el-row>
            </section>
            <section>
                <form-row name="clinics">
                    <transfer-table
                        key="clinics"
                        :items="clinics"
                        :fields="clinicFields"
                        v-model="model.operator_bonus.clinics"
                        value-key="clinic_id"
                        :left-title="__('Клиника')"
                        left-width="225px"
                        :right-title="__('Клиника')"
                        right-width="225px"
                        @new-row="initClinic">
                        <template slot="mistakes" slot-scope="props">
                           <form-input-number
                                :entity="props.rowData.data"
                                property="mistakes"
                                :min="0"
                                css-class="table-row"
                                control-size="mini" />
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

export default {
    props: {
        model: {
            type: Object
        },
    },
    data() {
        return {
            clinics: new ClinicRepository(),
            clinicFields: [
                {
                    name: 'mistakes',
                    title: __('Ошибки'),
                    width: '120px',
                },
            ],
        };
    },
    methods: {
        initClinic(data) {
            data.mistakes = 0;
        },
    },
}
</script>