<template>
    <div>
        <FileViewer
            ref="previewFile"
            :url="url"
            :data="data"
        />
        <div class="buttons dialog-footer text-right">
            <a href="#" class="el-button el-button--primary" @click.prevent="assignToPatient" v-if="isDisabled">{{ __('Сформировать') }}</a>
        </div>
    </div>
</template>

<script>
import FileViewer from "@/components/general/FileViewer";
import AppointmentDocument from "@/models/patient/appointment-document";

export default {
    name: "AppointmentsFileViews",
    components: {FileViewer},
    props: {
        appointment: Object,
        file: Object,
        type: String,
        patient: Object,
    },
    data() {
        return {
            url: this.file.url,
            data: this.file,
            nameDownloadFile: null,
        };
    },
    computed: {
        isDisabled() {
            if(this.type === 'acts' && this.appointment.latest_act) {
                const sortedDocuments = this.appointment.latest_act;
                if(this.checkDate(sortedDocuments) || !this.$can('acts-and-payments.shape-acts')) {
                    return false;
                }
            } else if(this.type === 'payment' && this.appointment.latest_payment) {
                const sortedDocuments = this.appointment.latest_payment;
                if(this.checkDate(sortedDocuments) || !this.$can('acts-and-payments.shape-payments')) {
                    return false;
                }
            }
            return true;
        }
    },
    methods: {
        getLastAct() {
            if(this.appointment.latest_act) {
                return this.appointment.latest_act;
            }
            return null;
        },
        checkDate(doc) {
            return Date.parse(doc.created_at) >= Date.parse(this.appointment.updated_at);
        },
        getLastReceipt() {
            if(this.appointment.latest_payment) {
                return this.appointment.latest_payment;
            }
            return null;
        },
        createNumber(document = null) {
            let number;
            if(document) {
                if(this.checkDate(document)) {
                    number = document.number;
                } else {
                    number = this.appointment.card_number.split('-')[0] + '' + this.$moment().format('mm:ss').split(':').join('');
                }
            } else {
                number = this.appointment.card_number.split('-')[0] + '' + this.$moment().format('mm:ss').split(':').join('');
            }
            return number;
        },
        print() {
            this.$refs.previewFile.print();
        },
        downloadFile() {
            let date = this.$moment().format('YYYY-MM-DD')
            let name;
            let doc;
            let number;
            if(this.type === 'acts') {
                doc = this.getLastAct();
                number = this.createNumber(doc);
                name = `${__('Акт выполненных работ')} - ${number} - ${date}.pdf`;
            } else {
                doc = this.getLastReceipt();
                number = this.createNumber(doc);
                name = `${__('Счет на оплату')} - ${number} - ${date}.pdf`;
            }
            this.$refs.previewFile.download(name);
        },
        assignToPatient() {
            let doc = null;
            if(this.type === 'acts') {
                doc = this.getLastAct();
            } else {
                doc = this.getLastReceipt();
            }
            const typeDocument = this.type === 'acts' ? __('Акт предоставления') : __('Счет на оплату')
            let number = this.createNumber(doc);
            const document = new AppointmentDocument({
                assigner_id: this.$store.state.user.employee_id,
                patient_id: this.appointment.patient_id,
                appointment_id: this.appointment.id,
                type: this.type,
                file_id: this.file.id,
                number: number,
                url: this.file.url,
            });
            document.save().then((response) => {
                this.$emit('successCreate', typeDocument);
            });
        }
    }
}
</script>
