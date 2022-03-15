<template>
    <manage-table 
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
                        v-if="props.rowData.patient"
                        href="#"
                        @click.prevent="showPatientLog(props.rowData.patient.id)">
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
import showLog from '@/mixins/call-center/show-log';

export default {
    mixins: [
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
                    name: 'phone_number',
                    title: __('Телефон'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                },
                {
                    name: 'email',
                    title: 'Email',
                    width: '15%',
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: this.actions.map((action) => ({
                    patient: action.related,
                    time: action.time,
                    action: this.$handbook.getOption('call_process_action', action.action),
                    phone_number: action.related.contact_details.primary_phone_number,
                    email: action.related.contact_details.email,
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
    },
};
</script>
