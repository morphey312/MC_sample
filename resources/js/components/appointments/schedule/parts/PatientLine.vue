<template>
    <section class="grey pt-10 pb-10">
        <p class="schedule-patient-block mb-0">
            <template v-if="patient.id">
                {{ __('Выбран пациент:') }} {{ patient.full_name }} / 
                <a href="#" @click.prevent="editPatient" v-if="$canUpdate('patients')">{{ __('Открыть профиль пациента /') }}</a>
                <a href="#" @click.prevent="findPatient">{{ __('Выбрать другого пациента') }}</a>
            </template>
            <template v-else>
                <a href="#" @click.prevent="findPatient">{{ __('Задать пациента для записи на прием') }}</a>
            </template>
        </p>
    </section>
</template>
<script>
export default {
    props: {
        patient: Object,
    },
    methods: {
        editPatient() {
            this.$emit('edit-patient');
        },
        findPatient() {
            this.$emit('find-patient');
        },
    },
}    
</script>