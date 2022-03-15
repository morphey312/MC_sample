<template>
	 <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded">
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import handbook from '@/services/handbook';
import Specialization from '@/models/specialization';
import SpecializationClinic from '@/models/specialization/clinic';
import ClinicRepository from '@/repositories/clinic';

export default {
	props: {
        specialization: Object,
    },
    data() {
        return {
            model: new Specialization({ id: this.specialization.id }),
            repository: new ProxyRepository(() => {
                return this.getClinics();
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Клиника'),
                    width: '25%',
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    formatter: (value) => {
                        return this.$formatter.fromHandbook('active_status', value);
                    },
                    width: '15%',
                },
                {
                    name: 'first_patient_appointment_limit',
                    title: __('Минимальное количество записей'),
                    width: '35%',
                },
                {
                    name: 'days_since_last_visit',
                    title: __('Давность посещения'),
                    width: '20%',
                    dataClass: 'no-dash',
                },
            ],
            clinic_list: new ClinicRepository(),
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        getClinics() {
            return this.model.fetch(['clinics']).then(() => {
                let clinic = new SpecializationClinic({
                    limitClinics: this.$isAccessLimited('specializations')
                });
                return {
                    rows: this.model.clinics.map(row => clinic.castToInstance(SpecializationClinic, row)),
                };
            });
        },
    },
}
</script>
