<template>
    <div v-loading="loading">
        <div>
            <form-row
                v-if="phoneNumber"
                name="login"
                :label="__('На номер {phone} был отправлен код подтверждения', {phone: phoneNumber})">
                <el-input
                    v-if="phoneNumber"
                    v-model="code" />
            </form-row>
            <hr v-if="documentsForUpload.length > 0 && phoneNumber"/>
            <el-row :gutter="20" class="pt-10" v-if="documentsForUpload.length > 0">
                <el-col :span="24">
                    <form-upload
                        v-for="(document, index) in documentsForUpload"
                        :key="index"
                        :multiple="false"
                        :entity="documentsForUpload[index]"
                        :label="getDocLabel(document.name)"
                        :button-text="__('Выберите документ')"
                        property="document_id"
                        accept="image/jpeg">
                        <svg-icon
                            v-if="getDocAddon(document)"
                            slot="label-addon"
                            name="question-alt"
                            class="icon-tiny"
                            :title="getDocAddon(document)" />
                    </form-upload>
                </el-col>
            </el-row>
            <hr v-if="showPhoneNumberInfo"/>
            <div v-if="showPhoneNumberInfo" class="pt-10" v-html="getInfo()" />
        </div>
        <div
            class="form-footer"
            style="display: flex; justify-content: space-between">
            <div class="text-left">
                <el-button
                    v-if="phoneNumber"
                    @click="resendSms">
                    {{ __('Отправить смс повторно') }}
                </el-button>
            </div>
            <div class="text-right">
                <el-button
                    @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    @click="save">
                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </div>
    </div>
</template>

<script>
import CONSTANTS from '@/constants';
import ehealth from '@/services/ehealth';

export default {
    props: {
        model: Object,
        approveAction: Boolean,
        phoneNumber: String,
        showPhoneNumberInfo: Boolean,
    },
    mounted() {
        this.$eventHub.$on('approveEhealthPatient', this.eventListener);
        this.$eventHub.$on('approveEhealthPatientAuthentication', this.eventListener);


        if (this.approveAction && this.model.urgent.documents && this.model.urgent.documents.length > 0) {
            this.model.urgent.documents.forEach((doc) => {
                this.parseUrgentDocument(doc)
            })
        }
    },
    beforeDestroy() {
        this.$eventHub.$off('approveEhealthPatient');
        this.$eventHub.$off('approveEhealthPatientAuthentication');
    },
    data() {
        return {
            code: null,
            loading: false,
            documentsForUpload: [],
        };
    },
    methods: {
        eventListener({status, errorMessage}) {
            this.loading = false;
            if (status === CONSTANTS.EHEALTH.STATUS.SUCCESS) {
                this.cancel()
            } else {
                if (errorMessage) {
                    switch (errorMessage) {
                        case CONSTANTS.EHEALTH_PATIENT.AUTHENTICATION_ERRORS.INVALID_CODE:
                            this.$error(__('Код неправильный, повторите попытку снова'));
                            break;
                        case CONSTANTS.EHEALTH_PATIENT.AUTHENTICATION_ERRORS.MAXIMUM_ATTEMPTS:
                            this.$error(__('Превышен лимит попыток, отправить СМС повторно'));
                            break;
                        default:
                            break;
                    }
                }
            }
        },
        resendSms() {
            if (this.approvePatientAction) {
                this.$emit('resendSms');
            } else {
                this.$emit('reInitializeOtp');
            }
        },
        cancel() {
            this.$emit('cancel');
        },
        approve() {
            this.$emit('approve');
        },
        stopLoading() {
            this.loading = false;
        },
        save() {
            if (this.phoneNumber) {
                if (this.documentsForUpload.length > 0 && !this.documentsForUpload.some((doc) => doc.document_id === null)) {
                    return this.$error(__('Не все документы были загружены'));
                }
                if (this.code.match(/^[1-9][0-9]*$/)){
                    this.loading = true;
                    if (this.approveAction) {
                        this.$emit('approve', {code: this.code, documents: this.documentsForUpload});
                    } else {
                        this.$emit('completeOtp', this.code);
                    }
                } else {
                    this.$error(__('Введите корректно код'))
                }
            } else {
                this.loading = true;
                this.$emit('approve', {code: this.code, documents: this.documentsForUpload});
            }
        },
        parseUrgentDocument(document) {
            let file = document.type;
            let documentUrl = document.url;
            let documentName = null;
            let documentOwner = null;
            let documentType = null;

            if (file.lastIndexOf('.') > -1) {
                documentName = file.slice(file.lastIndexOf('.') + 1);
                file = file.slice(0, file.lastIndexOf('.'));
                if (file.lastIndexOf('.') > -1) {
                    let type = file.slice(file.lastIndexOf('.') + 1);
                    documentType = type === CONSTANTS.EHEALTH_PATIENT.DOCUMENTS_TYPE.RELATIONSHIP ? type : null;
                    file = file.slice(0, file.lastIndexOf('.'));
                }
                if (file.indexOf('.') > -1) {
                    documentOwner = file.slice(0, file.indexOf('.'));
                } else if (file.length !== 0){
                    documentOwner = file;
                }
            } else {
                documentName = file;
            }

            this.documentsForUpload.push({
                type: documentType,
                owner: documentOwner,
                name: documentName,
                url: documentUrl,
                document_id: null,
            });
        },
        getDocLabel(type) {
            switch (type) {
                case 'no_tax_id':
                    return __('Документ про отказ от ИНН');
                    break;
                case 'tax_id':
                    return __('Документ ИНН');
                    break;
                default:
                    return this.$handbook.getOption('person_document', type.toLowerCase());
                    break;
            }
        },
        getDocAddon(document) {
            if (document.owner === CONSTANTS.EHEALTH_PATIENT.DOCUMENTS_OWNER.CONFIDANT_PERSON) {
                if (document.type === CONSTANTS.EHEALTH_PATIENT.DOCUMENTS_TYPE.RELATIONSHIP) {
                   return __('Выберите документ, удостоверяющий представительство');
                }
                return __('Выберите документ, удостоверяющий личность представителя');
            } else if (document.owner === CONSTANTS.EHEALTH_PATIENT.DOCUMENTS_OWNER.PERSON) {
                return __('Выберите документ, удостоверяющий личность');
            }
        },
        getInfo() {
            return ehealth.getNoAccessPhoneNumberInfo(this.phoneNumber)
        }
    },
}
</script>
