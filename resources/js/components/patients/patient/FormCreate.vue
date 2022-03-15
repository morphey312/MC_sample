<template>
    <patient-form :model="model">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </patient-form>
</template>

<script>
import PatientForm from './Form.vue';
import Patient from '@/models/patient';
import InitFromProcessMixin from '@/components/patients/mixins/init-from-process';
import ClinicRepository from '@/repositories/clinic';
import EhealthSearchPatient from "@/components/ehealth/SearchPatient.vue";

export default {
    mixins: [
        InitFromProcessMixin,
    ],
    components: {
        PatientForm,
    },
    props: {
        initialData: {
            type: Object,
            required: false,
        },
    },
    data() {
        return {
            model: new Patient(),
        }
    },
    mounted() {
        this.initFromProcess();
        this.initDefaultClinic();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$info(__('Пациент был успешно создан'));
                this.$emit('created', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        initFromProcess() {
            let state = this.$store.state.processState;
            let contact = state.currentContact;
            if (contact !== undefined) {
                this.initPatientFromProcessState(this.model, state, contact);
            } else if (!_.isEmpty(this.initialData)) {
                this.initFromPropData();
            }
        },
        initFromPropData() {
            this.model.firstname = this.initialData.firstname;
            this.model.lastname = this.initialData.lastname;
            this.model.middlename = this.initialData.middlename;
            this.model.location = this.initialData.location;
            this.model.contact_details.email = this.initialData.email;
            this.model.contact_details.primary_phone_number = this.initialData.phone;
            this.model.birthday = this.initialData.birthday;
            if (this.initialData.registration_id) {
                this.model.registration_id = this.initialData.registration_id;
                this.model.has_registration = true;
            }
        },
        initDefaultClinic() {
            if (this.model.clinics.length === 0) {
                let clinics = new ClinicRepository();
                clinics.getDefaultClinic().then((res) => {
                    if (res) {
                        this.model.clinics = [res.id];
                    }
                });
            }
        },
    },
}
</script>
