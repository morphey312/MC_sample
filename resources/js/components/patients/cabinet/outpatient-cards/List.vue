<template>
    <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
        <el-tab-pane
            :lazy="true"
            :label="__('Амбулаторные')"
            name="outpatient" >
            <section class="pt-0 shrinkable">
                <manage-table
                    ref="table"
                    :fields="fields"
                    :filters="filters"
                    :scopes="scopes"
                    :repository="repository"
                    :enable-pagination="false"
                    @header-filter-updated="syncFilters">
                    <template slot="specialization" slot-scope="props">
                        <a
                            href="#"
                            @click.prevent="showDetails(props.rowData)">
                            {{ props.rowData.specialization.name }}
                        </a>
                    </template>
                    <template slot="last_appointment_date" slot-scope="props">
                        {{ getLatestDateBySpec(props.rowData) }}
                    </template>
                    <template slot="last_appointment_doctor_name" slot-scope="props">
                        {{ getLatestDoctorBySpec(props.rowData) }}
                    </template>
                    <div class="buttons" slot="footer-top">
                        <el-button
                            @click="printForm">
                            {{ __('Печать формы') }}
                        </el-button>
                    </div>
                </manage-table>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Архивные')"
            name="archived" >
            <section class="pt-0 shrinkable">
                <archived-list :patient="patient"></archived-list>
            </section>
        </el-tab-pane>
    </el-tabs>
</template>
<script>
import CardSpecializationRepository from '@/repositories/patient/card/card-specialization';
import Details from './Details.vue';
import DetailsHeader from '@/components/patients/cabinet/outpatient-cards/modal-header/DetailsModalHeader';
import EmployeeRepository from '@/repositories/employee';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import SpecializationRepository from '@/repositories/specialization';
import ArchivedList from "@/components/patients/cabinet/outpatient-cards/ArchivedList";
import PatientDocument from './modals/PatientDocument.vue';

export default {
    components: {
        ArchivedList
    },
    props: {
        filters: Object,
        patient: Object
    },
    data() {
        return {
            repository: new CardSpecializationRepository(),
            fields: [
                {
                    name: 'card.number',
                    title: __('Номер карты'),
                    width: '10%',
                },
                {
                    name: 'specialization',
                    title: __('Специализация'),
                    width: '15%',
                    filter: new SpecializationRepository(),
                },
                {
                    name: 'last_appointment_date',
                    title: __('Дата последнего визита'),
                    width: '20%',
                    filterField: 'date',
                    filter: DateHeaderFilter,
                },
                {
                    name: 'last_appointment_doctor_name',
                    title: __('Врач'),
                    width: '20%',
                    filter: new EmployeeRepository(),
                    filterField: 'doctor',
                },
            ],
            activeTab: 'outpatient',
            scopes: ['card', 'appointments'],
            currentAppointment: null,
            currentAppointmentIndex: 0
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        showDetails(row) {
            if (row.appointments.length === 0) {
                this.$info(__('По данной карте нет записей'));
                return;
            }
            this.$modalComponent(Details, {
                specialization_id: row.specialization_id,
                specialization: row.specialization,
                card: row.id,
                clinic_id: row.clinic_id,
                appointments: row.appointments
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                appointmentChange: (dialog, data) => {
                    this.currentAppointment = data.appointment;
                    this.currentAppointmentIndex = data.appointmentIndex;

                    dialog.getTopHeaderAddon().setData({
                        currentAppointmentIndex: data.appointmentIndex,
                        currentAppointment: data.appointment
                    });
                },
            }, {
                header: __('Амбулаторная карта:') + ' ' + row.specialization.name,
                width: '890px',
                customClass: 'no-footer scrollable details__modal',
                headerAddon: {
                    component: DetailsHeader,
                    props: {
                        appointments: row.appointments,
                        specialization: row.specialization
                    },
                    eventListeners: {
                        dateSelected(dialog, data){
                            dialog.getTopComponent().dateChange(data);
                        },
                        print: (dialog) => {
                            dialog.getTopComponent().print(this.getCardHeader(row));
                        },
                        next: (dialog) => {
                            dialog.getTopComponent().next();
                        },
                        prev: (dialog) => {
                            dialog.getTopComponent().prev();
                        }
                    }
                },
            });
        },
        getLatestVisitBySpec(data){
            let latestAppointment = null;
            let appointments = [ ...data.appointments ];

            appointments.reverse().forEach((appointment) => {
                if(appointment.specialization_id === data.specialization_id){
                    latestAppointment = appointment;
                }
            });

            return latestAppointment;
        },
        getLatestDateBySpec(data){
            let appointment = this.getLatestVisitBySpec(data);

            if(!appointment || !appointment.date){
                return null;
            }
            return this.$formatter.dateFormat(appointment.date);
        },
        getLatestDoctorBySpec(data){
            let appointment = this.getLatestVisitBySpec(data);

            if(!appointment || !appointment.doctor_name){
                return null;
            }

            return appointment.doctor_name;
        },
        getCardHeader(row) {
            return __('Амбулаторная карта: {card} {specialization}', {card: row.card.number, specialization: row.specialization.short_name});
        },
        printForm() {
            this.$modalComponent(PatientDocument, {
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            },
            {
                header: __('Выдать документ: {name}', {name: this.patient.full_name}),
                width: '1020px',
                customClass: 'padding-0',
            });
        },
    }
};
</script>
