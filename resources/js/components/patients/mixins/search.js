import SearchPatient from '@/components/patients/search/Search.vue';
import TogglePatientFilter from '@/components/patients/search/ToggleFilter.vue';

export default {
    methods: {
        searchPatient(selected, props = {}) {
            this.$modalComponent(SearchPatient, props, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    dialog.close();
                    if (selected) {
                        selected(patient);
                    }
                },
            }, {
                header: __('Фильтр поиска контактных лиц'),
                width: '1270px',
                headerAddon: {
                    component: TogglePatientFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        getRelatives() {
            let ids = [];

            if (!this.patient.isNew()) {
                ids.push(this.patient.id);
            }

            if (this.patient.relatives.length !== 0) {
                this.patient.relatives.forEach(item => ids.push(item.id));
            }
            return ids;
        },
    }
}