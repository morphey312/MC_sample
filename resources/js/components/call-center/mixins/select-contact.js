import {Contact, PatientContact, EmployeeContact} from '@/services/sip-ua/process-state';
import CONSTANT from '@/constants';

export default {
    methods: {
        selectPatientContact(patient) {
            this.selectContact(new PatientContact(patient));
        },
        selectEmployeeContact(employee) {
            this.selectContact(new EmployeeContact(employee));
        },
        selectUnknownContact(number, name = __('Неизвестный')) {
            this.selectContact(new Contact(null, CONSTANT.USER.TYPE.PATIENT, name, [{
                number,
            }]));
        },
        selectContact(contact) {
            let state = this.$store.state.processState;
            if (this.modalComponent) {
                this.modalComponent.close();
            }
            state.addContact(contact);
            state.selectContact(contact.uid);
            window.scrollTo(0, 0);
            if (this.$route.name !== 'voip') {
                this.$router.push({name: 'voip'});
            }
        },
    },
};