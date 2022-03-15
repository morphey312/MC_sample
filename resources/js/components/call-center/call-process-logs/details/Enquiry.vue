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
                            {{ props.rowData.name }}
                        </a>
                        <span 
                            v-else>
                            {{ props.rowData.name }}
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
                        {{ props.rowData.name }}
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
                    </context-menu>
                </div>
            </template>
        </template>
        <template 
            slot="email" 
            slot-scope="props">
            <a :href="`mailto:${props.rowData.email}`">
                {{ props.rowData.email }}
            </a>
        </template>
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        enquiry: Object,
    },
    data() {
        return {
            fields: [
                {
                    name: 'patient',
                    title: __('Пациент'),
                    width: '20%',
                },
                {
                    name: 'phone_number',
                    title: __('Номер телефона'),
                    width: '12%',
                    formatter: (value) => {
                        return this.$formatter.phoneNumberFormat(value);
                    },
                },
                {
                    name: 'email',
                    title: 'Email',
                    width: '15%',
                },
                {
                    name: 'type',
                    title: __('Тип заявки'),
                    width: '13%',
                },
                {
                    name: 'clinic',
                    title: __('Клиника'),
                    width: '10%',
                },
                {
                    name: 'notes',
                    title: __('Примечание'),
                    width: '30%',
                    dataClass: 'no-ellipsis',
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: [this.enquiry].map((enquiry) => ({
                    name: enquiry.name,
                    patient: enquiry.patient,
                    phone_number: enquiry.phone_number,
                    email: enquiry.email,
                    type: this.$handbook.getOption('enquiry_type', enquiry.category),
                    clinic: enquiry.clinic_name,
                    notes: this.getNotes(enquiry),
                    enquiry: enquiry,
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
        selectContactNumber(number) {
            this.$emit('select-unknown', number);
        },
        addContact(enquiry) {
            this.displayCreatePatientForm((patient) => {
                enquiry.patient = patient;
            }, false, {
                phone: enquiry.phone_number,
            });
        },
        getNotes(enquiry) {
            let lines = [];
            if (_.isFilled(enquiry.card_number)) {
                lines.push(__('Номер карты: {card}', {card: enquiry.card_number}));
            }
            if (_.isFilled(enquiry.date)) {
                lines.push(__('Желаемая дата записи: {date}', {date: this.$formatter.datetimeFormat(enquiry.date)}));
            }
            lines.push(enquiry.notes);
            return lines.join('. ');
        },
    },
};
</script>