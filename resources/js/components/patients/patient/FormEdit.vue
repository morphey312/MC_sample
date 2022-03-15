<template>
    <patient-form :model="model">
        <div
            v-if="checkRegistration"
            slot="header"
            class="form-header">
            <registration-check
                :model="model" />
        </div>
        <div
            slot="buttons"
            class="form-footer text-right">
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
    </patient-form>
</template>

<script>
import PatientForm from './Form.vue';
import Patient from '@/models/patient';
import WarnExtChanges from '@/mixins/warn-external-changes';
import RegistrationCheck from './RegistrationCheck.vue';
import EhealthSearchPatient from "@/components/ehealth/SearchPatient.vue";

export default {
    mixins: [
        WarnExtChanges,
    ],
    components: {
        PatientForm,
        RegistrationCheck,
    },
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
        afterFetch: {
            type: Function,
        },
        checkRegistrationEhealth: false,
    },
    watch: {
        ehealthRegistration(val) {
            if (val && _.isVoid(this.model.ehealth_id)) {
                this.$nextTick(() => {
                    this.ehealthRegistration = false;
                    this.bindEhealth();
                });
            }
        }
    },
    data() {
        return {
            model: new Patient({id: this.id}),
            checkRegistration: false,
            ehealthRegistration: false,
        }
    },
    beforeMount() {
        this.model.fetch([
            'ehealth_patient',
            'contact_details',
            'archive_card_numbers',
            'clinics',
            'relatives',
            'discount_cards',
            'insurance_policies',
            'user',
            'client_ids',
        ]).then(() => {
            if (this.afterFetch) {
                this.afterFetch(this.model);
            }
            if (this.model.user_id !== null) {
                this.model.has_registration = true;
            }
            if (this.$canUpdate('patients') && this.$canAccess('patient-registrations')) {
                this.checkRegistration = true;
            }
            this.$emit('setEhealthPatient', this.model.ehealth_patient)
        });
    },
    methods: {
        cancel() {
            this.$emit('cancel', this.model);
        },
        save() {
            if (typeof this.model.source_id === 'object' && this.model.source_id) {
                this.model.source_id = this.model.source_id.id || this.model.source_id
            }

            this.$clearErrors();
            this.confirmExternalOverwrite(() => {
                this.model.save().then((response) => {
                    this.$info(__('Пациент был успешно обновлен'));
                    this.$emit('updated', this.model);
                }).catch((e) => {
                    this.$displayErrors(e);
                });
            });
        },
        editEhealthPatient(id) {
            this.displayEditEhealthPatientForm(id)
        },
        bindEhealth() {
            this.$modalComponent(EhealthSearchPatient, {
                    initialFilter: {
                        first_name: this.model.firstname,
                        last_name: this.model.lastname,
                        second_name: this.model.middlename,
                        birth_date: this.model.birthday,
                        phone_number: this.model.contact_details.primary_phone_number,
                    },
                    patient: this.model
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    created: (dialog) => {
                        dialog.close();
                        this.model.fetch().then(() => {
                            this.$emit('setEhealthPatient', data);
                        });
                    },
                },
                {
                    header: __('Поиск пациента в ЦБД eHealth'),
                    width: '1000px',
                });
        },
    },
}
</script>
