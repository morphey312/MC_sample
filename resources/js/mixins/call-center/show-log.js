import CallLog from '@/components/action-log/Call.vue';
import AppointmentLog from '@/components/action-log/Appointment.vue';
import PatientLog from '@/components/action-log/Patient.vue';

export default {
    methods: {
        showLog(id) {
            this.$modalComponent(CallLog, {
                id: id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения звонка'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        showAppointmentLog(id){
            this.$modalComponent(AppointmentLog, {
                id: id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения записи'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        showPatientLog(id) {
            this.$modalComponent(PatientLog, {
                id: id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения данных пациента'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
}
