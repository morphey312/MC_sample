<template>
    <div v-loading="loading">
        <div
            v-html="patientRequest.content"/>
        <div
            class="form-footer"
            style="display: flex; justify-content: space-between">
            <div class="text-left">
                <form-checkbox
                    :entity="patient"
                    property="patient_signed"
                    :label="__('Информационная памятка подписана пациентом')" />
            </div>
            <div class="text-right">
                <el-button
                    @click="close">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    @click="sign">
                    {{ __('Подписать') }}
                </el-button>
            </div>
        </div>
    </div>
</template>

<script>
import DigitalSignMixin from '@/mixins/digital-sign';
import printer from '@/services/print';
import CONSTANTS from "@/constants";

export default {
    mixins: [
        DigitalSignMixin
    ],
    props: {
        patient: Object,
        patientRequest: Object,
    },
    data() {
        return {
            loading: false,
        }
    },
    mounted() {
        this.$eventHub.$on('signEhealthPatient', (status) => {
            this.loading = false;
            if (status === CONSTANTS.EHEALTH.STATUS.SUCCESS) {
                this.loading = false;
                this.close()
            }
        });
    },
    methods: {
        print() {
            printer.printRawHtml(this.patientRequest.content)
        },
        close() {
            this.$emit('close');
        },
        sign() {
            if (this.patient.patient_signed !== false) {
                this.loading = true;
                this.patientRequest.patient_signed = true
                    this.signData(this.patientRequest, (signed) => {
                        this.patient.sign(signed).then(() => {
                            this.$info(__('Подписаный документ был отправлен'));
                        })
                    });
            } else {
                this.$error(__('Информационная памятка должна быть подписана пациентом'));
            }
        },
    },
    beforeDestroy() {
        this.$eventHub.$off('signEhealthPatient');
    },
}
</script>
