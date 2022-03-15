<template>
    <manage-table 
        ref="table"
        :fields="fields"
        :repository="repository"
        :enable-loader="false">
        <template 
            slot="patient" 
            slot-scope="props">
            <div class="has-icon">
                <span class="ellipsis">
                    <a 
                        v-if="$canUpdate('patients')"
                        href="#"
                        @click.prevent="displayEditPatientForm(props.rowData.patient.id)">
                        {{ props.rowData.patient.full_name }}
                    </a>
                    <span 
                        v-else>
                        {{ props.rowData.patient.full_name }}
                    </span>
                </span>
                <context-menu>
                    <a 
                        v-if="$canUpdate('patients')"
                        href="#"
                        @click.prevent="displayEditPatientForm(props.rowData.patient.id)">
                        {{ __('Данные пациента') }}
                    </a>
                    <a 
                        v-if="$canProcessCalls()"
                        href="#"
                        @click.prevent="selectPatientContact(props.rowData.patient)">
                        {{ __('Задать пациента для звонка') }}
                    </a>
                    <a 
                        v-if="$canUpdate('appointments')"
                        href="#"
                        @click.prevent="updateAppointment(props.rowData.appointment, props.rowData.original)">
                        {{ __('Редактировать запись') }}
                    </a>
                    <a
                        href="#"
                        @click.prevent="showAppointmentLog(props.rowData.appointment.id)">
                        {{ __('Операции') }}
                    </a>
                    <a 
                        v-if="$can('patient-cabinet.access')"
                        href="#"
                        @click.prevent="showCabinet(props.rowData.patient)">
                        {{ __('Кабинет пациента') }}
                    </a>
                </context-menu>
            </div>
        </template>
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import Appointment from '@/models/appointment';
import AppointmentManager from '@/components/appointments/mixin/manager';
import showLog from '@/mixins/call-center/show-log';

export default {
    mixins: [
        AppointmentManager,
        showLog
    ],
    props: {
        actions: Array,
    },
    data() {
        return {
            fields: [
                {
                    name: 'patient',
                    title: __('Пациент'),
                    width: '40%',
                },
                {
                    name: 'time',
                    title: __('Дата и время'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
                {
                    name: 'action',
                    title: __('Действие'),
                    width: '15%',
                },
                {
                    name: 'specialization',
                    title: __('Специализация'),
                    width: '15%',
                },
                {
                    name: 'doctor_name',
                    title: __('Врач'),
                    width: '15%',
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: this.actions.map((action) => ({
                    patient: action.related.patient,
                    time: action.time,
                    action: this.$handbook.getOption('call_process_action', action.action),
                    specialization: action.related.specialization_name,
                    doctor_name: action.related.doctor.name,
                    appointment: new Appointment(action.related),
                    original: action,
                }))
            })),
        };
    },
    methods: {
        selectPatientContact(patient) {
            this.$emit('select-patient', patient);
        },
        showCabinet(patient) {
            this.$emit('show-cabinet', patient);
        },
        updateAppointment(appointment, action) {
            this.makeDaySheetData(appointment, true).then(() => {
                this.editAppointment((appointment) => {
                    action.related = appointment.attributes;
                    this.$refs.table.refresh();
                    this.daySheetData = {};
                }, appointment);
            });
        },
    },
};
</script>
