import ScheduleCallRequestForm from '../Form.vue';
import CallRequest from '@/models/call-request';

export default {
    components: {
        ScheduleCallRequestForm,
    },
    props: {
        modelAttributes: {
            type: Object
        },
    },
    data() {
        return {
            model: new CallRequest(),
        }
    },
    mounted() {
        this.initFromData();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        isInvalidDates() {
            return (this.modelAttributes.appointment_date <= this.model.recall_from) ||
                   (this.modelAttributes.appointment_date <= this.model.recall_to);
        },
        showDateError() {
            return this.$error(__('Дата напоминания не может быть больше даты записи на прием'));
        },
        initFromData() {
            this.model.set({
                id: this.modelAttributes.id || null,
                patient_id: this.modelAttributes.patient_id || null,
                specialization_id: this.modelAttributes.specialization_id || null,
                clinic_id: this.modelAttributes.clinic_id || null,
                doctor_id: this.modelAttributes.doctor_id || null,
                doctor_type: this.modelAttributes.doctor_type || null,
                recall_from: this.modelAttributes.recall_from || null,
                recall_to: this.modelAttributes.recall_to || null,
                appointment_id: this.modelAttributes.appointment_id || null,
                call_request_purpose_id: this.modelAttributes.call_request_purpose_id || null,
                comment: this.modelAttributes.comment || '',
                status: this.modelAttributes.status || 'made',
            });
        },
    }
}