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
        record: Object,
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
                    name: 'clinic',
                    title: __('Клиника'),
                    width: '10%',
                },
                {
                    name: 'specialization_name',
                    title: __('Специализация'),
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
                rows: [this.record].map((record) => ({
                    name: record.name,
                    patient: record.patient,
                    phone_number: this.getPatientField(record),
                    email: this.getPatientField(record, 'email'),
                    clinic: record.clinic_name,
                    specialization_name: record.specialization_name,
                    notes: this.getNotes(record),
                    record: record,
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
        addContact(record) {
            this.displayCreatePatientForm((patient) => {
                record.patient = patient;
            }, false, {
                phone: record.phone_number,
            });
        },
        getPatientField(record, field = 'primary_phone_number') {
            return (record.patient && record.patient.contact_details)
                ? record.patient.contact_details[field]
                : '';
        },
        getNotes(record) {
            let lines = [];
            if (_.isFilled(record.card_number)) {
                lines.push(__('Номер карты: {card}', {card: record.card_number}));
            }

            if (_.isFilled(record.period_from) || _.isFilled(record.period_to)) {
                lines.push(__('Желаемая дата записи: {period_from} - {period_to}', {
                        period_from: this.$formatter.dateFormat(record.period_from),
                        period_to: this.$formatter.dateFormat(record.period_to)
                    })
                );
            }
            lines.push(record.notes);
            return lines.join('. ');
        },
    },
};
</script>