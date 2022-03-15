import SignalRecordModal from '@/components/doctor/signal-record/Modal.vue';
import SignalRecordRepository from '@/repositories/patient/signal-record';
import SignalRecord from '@/models/patient/signal-record';

export default {
    props: {
        patient: Object,
    },
    data() {
        return {
            signalRecord: null,
        };
    },
    methods: {
        showSignalRecord() {
            this.loadSignalRecord().then((model) => {
                this.$modalComponent(SignalRecordModal, {
                    model,
                }, {
                    close: (dialog) => {
                        dialog.close();
                    },
                }, {
                    header: __('Сигнальные обозначения пациента'),
                    width: '1200px',
                });
            });
        },
        loadSignalRecord() {
            if (this.signalRecord !== null) {
                return Promise.resolve(this.signalRecord);
            }
            
            let repository = new SignalRecordRepository();
            
            return repository.getPatientRecord(this.patient.id).then((record) => {
                if (record === null) {
                    record = new SignalRecord({patient_id: this.patient.id});
                }
                this.signalRecord = record;
                return record;
            });
        },
    },
};