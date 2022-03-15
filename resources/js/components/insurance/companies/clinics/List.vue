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
import InsuranceCompany from '@/models/insurance-company';
import CompanyClinic from '@/models/insurance-company/clinic';
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        company: Object,
    },
    data() {
        return {
            model: new InsuranceCompany({ id: this.company.id }),
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
                let clinic = new CompanyClinic();
                return {
                    rows: this.model.company_clinics.map(row => clinic.castToInstance(CompanyClinic, row)),
                };
            });
        },
    },
}
</script>