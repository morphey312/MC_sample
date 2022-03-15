import Vue from 'vue';
import CreateForm from '@/components/patients/patient/FormCreate.vue';
import EhealthCreateForm from '@/components/ehealth/patient/FormCreate.vue';
import EditForm from '@/components/patients/patient/FormEdit.vue';
import EhealthEditForm from '@/components/ehealth/patient/FormEdit.vue';
import HeaderPatientStatus from '@/components/ehealth/patient/HeaderPatientStatus.vue';
import CostSwitcher from "@/components/doctor/appointment/CostSwitcher.vue";

Vue.mixin({
    methods: {
        displayCreatePatientForm(created = false, cancel = false, data = {}, headerAddon = {}) {
            this.$modalComponent(CreateForm, {
                initialData: data,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                    cancel && cancel();
                },
                created: (dialog, patient) => {
                    dialog.close();
                    created && created(patient);
                },
            }, {
                header: __('Добавить пациента'),
                width: '900px',
                headerAddon,
            });
        },
        displayEditPatientForm(id, updated = false, cancel = false, properties = {}, headerAddon = {}, eventListeners = {}) {
            this.$modalComponent(EditForm, {
                id,
                ...properties,
            }, {
                cancel: (dialog, patient) => {
                    dialog.close();
                    cancel && cancel(patient);
                },
                updated: (dialog, patient) => {
                    dialog.close();
                    updated && updated(patient);
                },
                ...eventListeners
            }, {
                header: __('Редактировать пациента'),
                width: '900px',
                headerAddon,
            });
        },
        displayCreateEhealthPatientForm(created = false, cancel = false, data = {}, patient, headerAddon = {},  eventListeners = {}) {
            this.$modalComponent(EhealthCreateForm, {
                initialData: data,
                patient: patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                    cancel && cancel();
                },
                selected: (dialog, data) => {
                    console.log(data)
                },
                ...eventListeners
            }, {
                header: __('Добавить пациента eHealth'),
                width: '900px',
                headerAddon,
            });
        },
        displayEditEhealthPatientForm(id, updated = false, cancel = false, properties = {}) {
            this.$modalComponent(EhealthEditForm, {
                id,
                ...properties,
            }, {
                cancel: (dialog, patient) => {
                    dialog.close();
                    cancel && cancel(patient);
                },
                updated: (dialog, patient) => {
                    dialog.close();
                    updated && updated(patient);
                },
                updateStatus: (dialog, status) => {
                    dialog.getTopHeaderAddon().setPatientStatus(status);
                },
            }, {
                header: __('Редактировать пациента eHealth'),
                width: '900px',
                headerAddon: {
                    component: HeaderPatientStatus,
                    eventListeners: {
                        approvePatient:(dialog) => {
                            dialog.getTopComponent().confirm()
                        },
                        signPatient:(dialog) => {
                            dialog.getTopComponent().showPatientInformal()
                        }
                    },
                }
            });
        },
    },
});
