<template>
    <amublance-call-form
        :model="model"
        :appointment="appointment"
        :ambulance_call="ambulance_call"
        :patient="patient"
    >
        <div slot="buttons" class="form-footer text-right">
            <el-button @click="cancel"> {{ __("Отменить") }} </el-button>
            <el-button type="primary" @click="create">
                {{ __("Создать вызов") }}
            </el-button>
        </div>
    </amublance-call-form>
</template>

<script>
import AmbulanceCall from "@/models/ambulance-call";
import AmublanceCallForm from "./Form.vue";

export default {
    components: {
        AmublanceCallForm
    },
    props: {
        appointment: Object,
        ambulance_call: Object,
        patient: Object
    },
    data() {
        return {
            model: new AmbulanceCall()
        };
    },
    mounted() {
        let state = this.$store.state.processState;

        this.model.phone = state.phoneNumber
            ? state.phoneNumber
            : this.patient.contact_details.primary_phone_number;
    },
    methods: {
        cancel() {
            this.$emit("cancel");
        },
        setPatientPhone(phone, caller) {
            let freshPatient = new Patient({ id: this.patient.id });

            freshPatient
                .fetch([
                    "contact_details",
                    "archive_card_numbers",
                    "clinics",
                    "relatives",
                    "discount_cards",
                    "insurance_policies",
                    "user"
                ])
                .then(() => {
                    freshPatient.contact_details.secondary_phone_number = phone;
                    freshPatient.contact_details.secondary_phone_comment = caller;
                    freshPatient.contact_details.secondary_phone_clinic = this.appointment.clinic_id;

                    freshPatient.save();
                });
        },
        create() {
            this.$clearErrors();

            this.model.validate().then(e => {
                if (e && Object.keys(e).length !== 0) {
                    this.$displayErrors({ errors: e });
                } else {
                    
                    if(this.model.patient_secondary_phone){
                        this.setPatientPhone(this.model.phone, this.model.caller);
                    }

                    this.$emit("created", this.model);
                }
            });
        }
    }
};
</script>
