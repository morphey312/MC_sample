<template>
    <model-form :model="model">
        <el-tabs 
            v-model="activeTab" 
            class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :label="__('Общая информация')"
                name="info">
                <section>
                    <el-row :gutter="20">
                        <el-col :span="8">
                            <form-select
                                :entity="model"
                                property="type"
                                options="msp_contract_type"
                                :label="__('Тип')" />
                            <form-input
                                :entity="model"
                                :disabled="wasSent"
                                property="contract_number"
                                :label="__('Номер')" />
                            <form-input
                                :entity="model"
                                property="payer_bank"
                                :label="__('Банк')" />
                            <form-upload
                                :entity="model"
                                :multiple="false"
                                property="statute_id"
                                :label="__('Статут')" />
                        </el-col>
                        <el-col :span="8">
                            <form-input
                                :entity="model"
                                property="contractor_base"
                                :label="__('Основание')" />
                            <form-date
                                :entity="model"
                                property="start_date"
                                :disabled="isVerified"
                                :label="__('Дата вступления в силу')" />
                            <form-input
                                :entity="model"
                                property="payer_account_number"
                                :label="__('Номер счета в банке')" />
                            <form-upload
                                :entity="model"
                                :multiple="false"
                                property="additional_document_id"
                                :label="__('Дополнительный документ')" />
                        </el-col>
                        <el-col :span="8">
                            <form-select
                                :entity="model"
                                property="form_type"
                                options="msp_contract_form"
                                :label="__('Форма')" />
                            <form-date
                                :entity="model"
                                property="end_date"
                                :disabled="isVerified"
                                :label="__('Дата окончания действия')" />
                            <form-input
                                :entity="model"
                                property="payer_mfo"
                                :label="__('МФО')" />
                            <form-select
                                v-if="isReimbursement"
                                ref="program"
                                :entity="model"
                                property="medical_program.id"
                                :repository="medicalPrograms"
                                :debounce="1000"
                                :min-query-len="3"
                                :label="__('Медицинская програма')"
                                @changed="medicalProgramChanged" />
                        </el-col>
                    </el-row>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Клиники')"
                name="clinics">
                <section>
                    <form-row name="clinics">
                        <transfer-table
                            :items="clinics"
                            v-model="model.clinics"
                            :left-title="__('Клиника')"
                            left-width="300px"
                            :right-title="__('Клиника')"
                            right-width="300px">
                        </transfer-table>
                    </form-row>
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="isCapitation"
                :label="__('Подрядчики')"
                name="contractors">
                <error-catcher :catch="contractorsErrors" />
                <contractors 
                    :clinics="selectedClinics"
                    :contract="model" />
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import Contractors from './Contractors.vue';
import CONSTANT from '@/constants';
import ProxyRepository from '@/repositories/proxy-repository';
import ehealth from '@/services/ehealth';

export default {
    components: {
        Contractors,
    },
    props: {
        model: Object,
    },
    computed: {
        isReimbursement() {
            return this.model.type === CONSTANT.EHEALTH.CONTRACT_TYPE.REIMBURSEMENT;
        },
        isCapitation() {
            return this.model.type === CONSTANT.EHEALTH.CONTRACT_TYPE.CAPITATION;
        },
        wasSent() {
            return _.isFilled(this.model.ehealth_request_id);
        },
        isVerified() {
            return _.isFilled(this.model.ehealth_id);
        },
        selectedClinics() {
            return this.clinics.filter((clinic) => {
                return this.model.clinics.indexOf(clinic.id) !== -1;
            });
        }
    },
    data() {
        return {
            clinics: [],
            activeTab: 'info',
            medicalPrograms: new ProxyRepository(({filters, limit}) => {
                if (filters.id !== undefined && filters.id[0] === this.model.medical_program.id) {
                    return Promise.resolve([{
                        id: this.model.medical_program.id,
                        value: this.model.medical_program.name,
                    }]);
                }
                return ehealth.getMedicalPrograms({name: filters.query}).then((list) => {
                    return list.map(item => ({
                        id: item.id,
                        value: item.name,
                    }));
                });
            }),
            contractorsErrors: [
                {key: new RegExp('subcontractors[.][0-9]+[.]contract[.]expires_at'), label: __('Договор')},
            ],
        }
    },
    mounted() {
        let repository = new ClinicRepository({
            filters: {
                msp: this.model.msp_id,
                active_in_ehealth: true,
            }
        });
        repository.fetchList().then((list) => {
            this.clinics = list;
        });
    },
    methods: {
        medicalProgramChanged(val) {
            let options = this.$refs.program.getAvailableOptions();
            let option = _.find(options, opt => opt.id === val);
            if (option) {
                this.model.medical_program.name = option.value;
            } else {
                delete this.model.medical_program.name;
            }
        }
    }
}
</script>