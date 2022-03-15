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
import LegalEntity from '@/models/legal-entity';
import LegalEntityClinic from '@/models/legal-entity/clinic';
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        legalEntity: Object,
    },
    data() {
        return {
            model: new LegalEntity({ id: this.legalEntity.id }),
            repository: new ProxyRepository(() => {
                return this.getClinics();
            }),
            fields: [
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                },
                {
                    name: 'agreement',
                    title: __('Данные договора'),
                },
                {
                    name: 'agreement_active',
                    title: __('Договор активный'),
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
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
                let clinic = new LegalEntityClinic();
                return {
                    rows: this.model.entity_clinics.map(row => clinic.castToInstance(LegalEntityClinic, row)),
                };
            });
        },
    },
}
</script>