<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-radio-group v-model="model.type">
                <el-radio-button :disabled="disableMethod(otp)" :label="otp">{{ __('Телефон') }}</el-radio-button>
                <el-radio-button :disabled="disableMethod(offline)" :label="offline">{{ __('Документы') }}</el-radio-button>
                <el-radio-button :disabled="disableMethod(thirdPerson)" :label="thirdPerson">{{ __('Третье лицо') }}</el-radio-button>
            </el-radio-group>
        </el-row>
        <el-row :gutter="20" v-if="model.type && model.type !== thirdPerson">
                <form-input
                    :disabled="model.action === updateAction"
                    v-if="model.type === otp"
                    :entity="model"
                    property="phone_number"
                    :label="__('Номер телефона')" />
                <form-input
                    :entity="model"
                    :required="model.type === thirdPerson"
                    property="alias"
                    :label="__('Псевдоним')" />
        </el-row>
        <el-row :gutter="20" v-if="model.type && model.type === thirdPerson">
            <el-button
                :disabled="model.action === updateAction"
                @click="findPerson">
                {{ __('Выбрать особу') }}
            </el-button>
            <form-input
                :disabled="model.action === updateAction"
                class="pt-10"
                v-if="model.value"
                :entity="model"
                :required="true"
                property="phone_number"
                :label="__('Номер телефона')" />
            <form-input
                v-if="model.value"
                :entity="model"
                :required="true"
                property="alias"
                :label="__('Псевдоним')" />
        </el-row>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import CONSTANT from '@/constants';
import EhealthSearchPatient from '@/components/ehealth/SearchPatient.vue';
import ehealth from '@/services/ehealth';
import CONSTANTS from '@/constants';
import moment from 'moment';

export default {
    props: {
        model:  Object,
        patient:  Object,
    },
    computed: {
        otp() {
            return CONSTANT.EHEALTH_PATIENT.AUTHENTICATION_TYPE.OTP;
        },
        offline() {
            return CONSTANT.EHEALTH_PATIENT.AUTHENTICATION_TYPE.OFFLINE;
        },
        thirdPerson() {
            return CONSTANT.EHEALTH_PATIENT.AUTHENTICATION_TYPE.THIRD_PERSON;
        },
        updateAction() {
            return CONSTANT.EHEALTH_PATIENT.AUTHENTICATION_METHODS.UPDATE;
        },
    },
    watch: {
        ['model.type'] (val) {
            this.model.phone_number = val === this.offline || this.thirdPerson ? '+38' : null;
        }
    },
    data() {
        return {
            types: this.$handbook.getOptions('ehealth_authentication_method'),
        }
    },
    methods: {
        disableMethod(method) {
            if (this.model.action === this.updateAction) {
                return true
            } else {
                switch (method) {
                    case this.offline:
                        let otpMethod = this.patient.patient_authentications.find(method => method.type === this.otp);
                        let offlineMethod = this.patient.patient_authentications.find(method => method.type === this.offline);
                        return !!(otpMethod || offlineMethod)
                        break;
                    default:
                        return false;
                        break;
                }
            }
        },
        findPerson() {
            this.$modalComponent(EhealthSearchPatient, {
                    initialFilter: {},
                    patient: null,
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    selected: (dialog, data) => {
                        let age = moment().diff(data.birth_date, 'years');
                        if (age <= 14) {
                            this.$error(__('Пациент должен быть старше 14 лет'));
                        } else if (this.patient.ehealth_id && data.id === this.patient.ehealth_id) {
                            this.$error(__('Пациент сам у себя не может быть выбран для метода аунтификации'));
                        } else {
                            this.getThirdPersonAuthentications(data).then(() => {
                                dialog.close();
                            }).catch((e) => {
                                this.$error(e)
                            })
                        }
                    },
                },
                {
                    header: __('Поиск пациента в ЦБД eHealth'),
                    width: '1000px',
                });
        },
        getThirdPersonAuthentications(person) {
            return ehealth.getPatientAuthenticationMethods(person.id).then((response) => {
                if (response && response.data.length > 0) {
                    let otpMethod = response.data.find(authentication => authentication.type === this.otp.toUpperCase());

                    if (!otpMethod) {
                        return Promise.reject(__('Выбранная персона не имеет авторизацию через телефон'));
                    }

                    this.model.value = person.id;

                    return Promise.resolve();
                } else {
                    return Promise.reject(__('Произошла ошибка, пожалуйста выберете другого пациента'));
                }
            })
        },
    }
}
</script>
