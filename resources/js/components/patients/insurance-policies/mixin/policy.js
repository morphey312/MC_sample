import InsuranceCompanyRepository from '@/repositories/insurance-company';

export default {
    data() {
        return {
            insuranceCompanies: [],
        }  
    },
    mounted() {
        this.getInsuranceCompanies();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        getInsuranceCompanies() {
            let company = new InsuranceCompanyRepository();
            company.fetchList().then((response) => {
                this.insuranceCompanies = response;
            });
        },
        getInsurer() {
            let insurer = this.insuranceCompanies.find(item => item.id === this.model.insurance_company_id);
            return insurer 
                ? {
                    id: insurer.id,
                    name: insurer.value,
                } 
                : {};
        },
    },
    watch: {
        ['model.insurance_company_id']() {
            this.model.set('company', this.getInsurer());
        },
    },
}