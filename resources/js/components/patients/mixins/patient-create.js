export default {
    methods: {
        createEhealthPatient(patient, headerAddon = {}, onCreated = null) {
            let initialData = this.getEhealthFilterValues();
            this.displayCreateEhealthPatientForm(null, null, initialData, patient, headerAddon);
        },
        createPatient(headerAddon = {}, onCreated = null) {
            let initialData = this.getFilterValues();
            this.displayCreatePatientForm((patient) => {
                this.lastActiveItemId = patient.id;
                if (onCreated) {
                    onCreated(patient);
                } else {
                    this.refresh();
                }
            }, null, initialData, headerAddon);
        },
        getFilterValues() {
            let filterList = this.$refs.patientFilter.filter;

            return {
                firstname: this.trimFilterMode(filterList.firstname),
                lastname: this.trimFilterMode(filterList.lastname),
                middlename: this.trimFilterMode(filterList.middlename),
                phone: this.trimFilterMode(filterList.phone),
                email: this.trimFilterMode(filterList.email),
                location: this.trimFilterMode(filterList.location),
            };
        },
        getEhealthFilterValues() {
            let filterList = this.filter;

            return {
                first_name: this.trimFilterMode(filterList.first_name),
                last_name: this.trimFilterMode(filterList.last_name),
                second_name: this.trimFilterMode(filterList.second_name),
                birth_date: this.trimFilterMode(filterList.birth_date),
            };
        },
        trimFilterMode(value) {
            if (typeof value === 'string') {
                if (['=', '|', '~'].indexOf(value.substr(0, 1)) !== -1) {
                    return value.substr(1);
                }
            }
            return value;
        },
    }
}
