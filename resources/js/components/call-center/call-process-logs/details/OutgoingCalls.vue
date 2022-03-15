<template>
    <manage-table 
        :fields="fields"
        :repository="repository"
        :enable-loader="false">
        <template 
            slot="patient" 
            slot-scope="props">
            <template v-if="props.rowData.patient">
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
            <template v-else>
                <div class="has-icon">
                    <span class="ellipsis">
                        {{ __('Неизвестный') }}
                    </span>
                    <context-menu>
                        <a 
                            v-if="$canCreate('patients')"
                            href="#"
                            @click.prevent="addContact(props.rowData)">
                            {{ __('Добавить контакт') }}
                        </a>
                        <a 
                            v-if="$canProcessCalls()"
                            href="#"
                            @click.prevent="selectContactNumber(props.rowData.phone_number)">
                            {{ __('Задать контакт для звонка') }}
                        </a>
                        <a
                            href="#"
                            @click.prevent="showLog(props.rowData.id)">
                            {{ __('Операции') }}
                        </a>
                    </context-menu>
                </div>
            </template>
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
        calls: Array,
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
                    name: 'phone_number',
                    title: __('Номер телефона'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                },
                {
                    name: 'started_at',
                    title: __('Начало звонка'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
                {
                    name: 'ended_at',
                    title: __('Окончание звонка'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: this.calls.map((call) => ({
                    patient: call.patient,
                    phone_number: call.phone_number,
                    started_at: call.started_at,
                    ended_at: call.ended_at,
                }))
            })),
        };
    },
    methods: {
        selectPatientContact(patient) {
            this.$emit('select-patient', patient);
        },
        selectContactNumber(number) {
            this.$emit('select-unknown', number);
        },
        showCabinet(patient) {
            this.$emit('show-cabinet', patient);
        },
        addContact(call) {
            this.displayCreatePatientForm((patient) => {
                call.patient = patient;
            }, false, {
                phone: call.phone_number,
            });
        },
    },
};
</script>
