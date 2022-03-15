<template>
    <div class="alerts">
        <template v-if="patient">
            <alert
                type="attention"
                v-if="patient.is_attention">
                {{ __('Внимание:') }} {{ attentionInfo }}
            </alert>
            <alert
                type="black-mark"
                v-if="patient.black_mark">
                {{ __('У пациента черная метка:') }} {{ blackMarkInfo }}
            </alert>
            <alert
                type="skk"
                v-if="patient.is_skk">
                {{ __('Обращение в СКК:') }} {{ skkInfo }}
            </alert>
            <alert
                type="skk"
                v-if="patient.service_debt > 0">
                {{ __('У пациента долг:') }} {{ serviceDebt }} {{ __('грн.') }}
            </alert>
            <alert
                type="do_not_take_payment"
                v-if="appointment.do_not_take_payment && !appointment.isNew()">
                {{ __('Внимание! За некоторые услуги оплату не брать!') }}
            </alert>
            <alert
                type="info"
                v-if="prevAppointmentWarning != false">
                {{ prevAppointmentWarning }}
            </alert>
        </template>
    </div>
</template>
<script>
export default {
    props: {
        patient: {
            type: Object,
            default: () => ({}),
        },
        appointment: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            prevAppointment: null,
            prevAppointmentWarning: false,
        }
    },
    computed: {
        attentionInfo() {
            return this.patient.attention_comment;
        },
        blackMarkInfo() {
            return this.$formatter.listFormat([
                this.$handbook.getOption('black_mark_reason', this.patient.black_mark_reason),
                this.patient.black_mark_comment,
            ]);
        },
        skkInfo() {
            return this.$formatter.listFormat([
                this.$handbook.getOption('skk_reason', this.patient.skk_reason),
                this.patient.skk_comment,
            ]);
        },
        serviceDebt() {
            return this.$formatter.numberFormat(this.patient.service_debt);
        },
    },
    mounted() {
        this.getPatientPrevAppointment();
    },
    methods: {
        getPatientPrevAppointment() {
            if (this.appointment.isNew() && _.isFilled(this.patient.id)) {
                this.patient.getSpecializationPrevAppointment(this.appointment.clinic_id, this.appointment.specialization_id)
                    .then(response => {
                        if (response) {
                            this.prevAppointment = response;
                        } else {
                            this.prevAppointment = null;
                        }
                        this.setPrevAppointmentWarning();
                    });
            }
        },
        setPrevAppointmentWarning() {
            if (this.prevAppointment && this.prevAppointment.show_days_since_message != false) {
                let daysPast = this.$moment(this.appointment.date).diff(this.prevAppointment.date, 'days');
                if ((this.prevAppointment.days_since_visit > 0) && (daysPast > this.prevAppointment.days_since_visit)) {
                    this.prevAppointmentWarning = __('Пациент был в отделении последний раз более чем') + ' ' + daysPast + ' ' + __('дней назад');
                    return;
                }
                this.prevAppointmentWarning = false;
                return;
            }
            this.prevAppointmentWarning = false;
        },
    },
    watch: {
        ['patient.id']() {
            this.getPatientPrevAppointment();
        },
        ['appointment.specialization_id']() {
            this.getPatientPrevAppointment();
        },
    },
}
</script>
