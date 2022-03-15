<template>
    <section>
        <manage-table
            ref="table"
            :fields="fields"
            :filters="filters"
            :repository="repository"
            @header-filter-updated="syncFilters"
            @loaded="loaded">
            <template
                slot="fill_out"
                slot-scope="props">
                <div class="has-icon">
                    <a href="#"
                    @click.prevent="fillOut(props.rowData)">
                    {{ __('Заполнить и печатать') }}
                    </a>
                </div>
            </template>
        </manage-table>
    </section>
</template>
<script>
import PatientDocumentRepository from "@/repositories/patient-document";
import AppointmentRepository from "@/repositories/appointment";
import Appointment from "@/models/appointment";
import Blank from './Blank.vue';
import HeaderAddon from '@/components/doctor/appointment/patient-document/HeaderAddon.vue';
import CabinetMixin from '@/components/patients/cabinet/mixins/cabinet';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        CabinetMixin,
    ],
    props: {
        patient: Object,
    },
    data() {
        return {
            loading: false,
            repository: new PatientDocumentRepository(),
            fields: [
                {
                    name: 'name',
                    title: __('Название'),
                    filterField: 'name',
                    filter: true,
                },
                {
                    name: 'fill_out',
                    title: __('Заполнить и печатать'),
                    width: "150px",
                },
            ],
            filters: {
                clinic: this.patient.clinics,
                is_official_form: true,
                is_conclusion: false,
            },
            appointment: null,
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        getPatientData() {
            let appointment = new AppointmentRepository();
            let filters = {
                clinic: this.patient.clinics,
                patient: this.patient.id,
                skip_system_status: [
                    CONSTANTS.APPOINTMENT.STATUSES.DID_NOT_COME,
                    CONSTANTS.APPOINTMENT.STATUSES.DELETED,
                ],
            };
            return appointment.fetch(filters, [{field: 'date', direction: 'asc'}], [], 1, 1).then(response => {
                if (response && response.rows && response.rows.length != 0) {
                    let appointment = new Appointment({id: response.rows[0].id});
                    return appointment.fetch(['clinic']).then(() => {
                        this.appointment = appointment;
                        if (this.signalRecord == null) {
                            return this.loadSignalRecord();
                        }
                    });
                }
                return Promise.resolve();
            });
        },
        fillOut(file) {
            if (this.appointment == null) {
                return this.getPatientData().then(() => {
                    if (this.appointment != null) {
                        return this.fillBlank(file);
                    }
                    return this.$error(__('Печать невозможна, у пациента отсутствуют успешные приходы в клинику'));
                });
            }
            return this.fillBlank(file);
        },
        fillBlank(file) {
            this.$modalComponent(Blank, {
                appointment: this.appointment,
                signalRecord: this.signalRecord,
                patient: this.patient,
                file: file,
            }, {
                cancel: (dialog) => {
                    this.cancel();
                },
                printed: (dialog, patientDocument) => {
                },
            }, {
                headerAddon: {
                    component: HeaderAddon,
                        eventListeners: {
                        print: (dialog) => {
                            dialog.getTopComponent().print();
                        },
                    },
                }
            });
        },
    }
}
</script>
