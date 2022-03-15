<template>
    <alert
        v-if="registration !== null"
        type="info">
        {{ __('По номеру телефона данного пациента была оставлена заявка на регистрацию ЛК: {name}, {date}.', {name: registration.full_name, date: $formatter.dateFormat(registration.created_at)}) }}
        <a href="#" @click.prevent="connectRegistration">{{ __('Присоединить профиль') }}</a>
    </alert>
</template>

<script>
import RegistrationRepository from '@/repositories/patient/registration';
import CONSTANT from '@/constants';
import RegistrationMixin from '../mixins/registration';

export default {
    mixins: [
        RegistrationMixin,
    ],
    props: {
        model: Object,
    },
    data() {
        return {
            registration: null,
            repository: new RegistrationRepository(),
        };
    },
    mounted() {
        if (this.model.user_id === null && this.model.registration_id === null) {
            let phones = [this.model.contact_details.primary_phone_number, this.model.contact_details.secondary_phone_number].filter(_.isFilled);
            this.repository.fetch({
                phones: phones,
                status: CONSTANT.PATIENT.REGISTRATION.STATUS.NEW,
            }, null, null, 1, 1).then((result) => {
                if (result.rows.length !== 0) {
                    this.registration = result.rows[0];
                }
            });
        }
    },
    methods: {
        connectRegistration() {
            this.extendPatientData(this.registration, this.model);
            this.registration = null;
        },
    },
}
</script>
