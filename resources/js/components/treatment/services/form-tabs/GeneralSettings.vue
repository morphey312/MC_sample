<template>
    <div class="sections-wrapper">
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-input-i18n
                        :entity="model"
                        property="name"
                        :label="__('Услуга')"
                    />
                    <form-input-i18n
                        :entity="model"
                        property="name_ua"
                        :label="__('Название для чека')"
                    />
                    <form-select
                        :entity="model"
                        :options="specializations"
                        :filterable="true"
                        :clearable="true"
                        property="specialization_id"
                        :label="__('Специализация')"
                    />
                </el-col>
                <el-col :span="8">
                    <form-select
                        :entity="model"
                        :options="destinations"
                        :filterable="true"
                        :clearable="true"
                        property="payment_destination_id"
                        :label="__('Назначение платежа')"
                    />
                    <form-select
                        :disabled="!model.is_base"
                        :entity="model"
                        :options="diagnosis"
                        :filterable="true"
                        :clearable="true"
                        property="diagnosis_id"
                        :label="__('Диагноз по МКБ')"
                    />
                    <form-select
                        :entity="model"
                        :repository="ehealthServices"
                        :clearable="true"
                        property="ehealth_service_id"
                        :label="__('Соответствие eHealth')" />
                </el-col>
                <el-col :span="8">
                    <form-checkbox
                        :entity="model"
                        property="disabled"
                        :label="__('Не использовать услугу при добавлении новых записей пациентов')"
                    />
                    <form-checkbox
                        :entity="model"
                        property="is_base"
                        :label="__('Является базовой в курсе лечения')"
                    />
                    <form-checkbox
                        :entity="model"
                        property="is_for_discount_card"
                        :label="__('Услуга предназначена для оплаты дисконтной карты')"
                    />
                    <form-checkbox
                        :entity="model"
                        property="is_no_auto_recommend_source"
                        :label="__('Не использовать при авторекомендации')"
                    />
                </el-col>
            </el-row>
        </section>
        <hr />
        <section>
            <form-row name="clinics">
                <transfer-table
                    v-if="loading === false"
                    :items="clinics"
                    :fields="clinicFields"
                    v-model="model.clinics"
                    value-key="clinic_id"
                    :left-title="__('Клиника')"
                    left-width="215px"
                    :right-title="__('Клиника')"
                    right-width="215px">
                    <template slot="code" slot-scope="props">
                        <form-input
                            :entity="props.rowData.data"
                            property="code"
                            control-size="mini"
                            css-class="table-row" />
                    </template>
                </transfer-table>
            </form-row>
        </section>
    </div>
</template>

<script>
import SpecializationRepository from '@/repositories/specialization';
import ClinicRepository from '@/repositories/clinic';
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import DiagnosisRepository from '@/repositories/diagnosis';
import EhealthServiceRepository from '@/repositories/ehealth/service';

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
            ehealthServices: new EhealthServiceRepository(),
            specializations: new SpecializationRepository({
                limitClinics: this.limitClinics,
            }),
            clinics: [],
            destinations: new PaymentDestinationRepository({
                limitClinics: this.limitClinics,
            }),
            diagnosis: new DiagnosisRepository({}),
            clinicFields: [
                {
                    name: 'code',
                    title: __('Код'),
                    width: '120px',
                },
            ],
            loading: true,
        }
    },
    mounted() {
        this.getLists();
    },
    methods: {
        getLists() {
            this.getClinics(this.model.specialization_id);
        },
        getClinics(specialization) {
            this.loading = true;
            let clinic = new ClinicRepository({
                accessLimit: this.limitClinics,
            });
            clinic.fetchList({has_specialization: specialization}).then((response) => {
                this.clinics = response;
                this.loading = false;
            });
        },
    },
    watch: {
        ['model.specialization_id'](val) {
            this.getClinics(val);
        }
    },
}
</script>
