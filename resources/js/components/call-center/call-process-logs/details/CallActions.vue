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
                <span 
                    v-if="props.rowData.is_patient_contact"
                    class="ellipsis">
                    <a 
                        v-if="$canUpdate('patients')"
                        href="#"
                        @click.prevent="displayEditPatientForm(props.rowData.contact.id)">
                        {{ props.rowData.contact.full_name }}
                    </a>
                    <span 
                        v-else>
                        {{ props.rowData.contact.full_name }}
                    </span>
                </span>
                <span 
                    v-else
                    class="ellipsis">
                    {{ props.rowData.contact.full_name }}
                </span>
                <context-menu>
                    <a 
                        v-if="props.rowData.is_patient_contact && $canUpdate('patients')"
                        href="#"
                        @click.prevent="displayEditPatientForm(props.rowData.contact.id)">
                        {{ __('Данные пациента') }}
                    </a>
                    <a 
                        v-if="$canProcessCalls()"
                        href="#"
                        @click.prevent="selectContact(props.rowData.contact)">
                        {{ __('Задать контакт для звонка') }}
                    </a>
                    <a 
                        v-if="$canUpdate('calls')"
                        href="#"
                        @click.prevent="editCall(props.rowData.call, props.rowData.original)">
                        {{ __('Редактировать звонок') }}
                    </a>
                    <a
                        href="#"
                        @click.prevent="showLog(props.rowData.call.id)">
                        {{ __('Операции') }}
                    </a>
                    <a 
                        v-if="props.rowData.is_patient_contact && $can('patient-cabinet.access')"
                        href="#"
                        @click.prevent="showCabinet(props.rowData.contact)">
                        {{ __('Кабинет пациента') }}
                    </a>
                </context-menu>
            </div>
        </template>
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import EditCall from '@/components/call-center/calls-appointments/calls/FormEdit'
import Call from '@/models/call';
import Patient from '@/models/patient';
import Employee from '@/models/employee';
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
                    width: '15%',
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
                    width: '10%',
                },
                {
                    name: 'is_first',
                    title: __('Первичный'),
                    width: '10%',
                    formatter: (value) => this.$formatter.boolToString(value, '<span class="check-yes" />'),
                },
                {
                    name: 'result',
                    title: __('Результат'),
                    width: '10%',
                },
                {
                    name: 'specialization',
                    title: __('Специализация'),
                    width: '10%',
                },
                {
                    name: 'card_number',
                    title: __('Номер карты'),
                    width: '15%',
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    width: '15%',
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: this.actions.map((action) => ({
                    is_patient_contact: this.isPatient(action.related.contact),
                    contact: this.getContactData(action.related.contact),
                    time: action.time,
                    action: this.$handbook.getOption('call_process_action', action.action),
                    is_first: action.related.is_first,
                    result: action.related.call_result_name,
                    specialization: action.related.specialization_name,
                    card_number: action.related.card_number,
                    comment: action.related.comment,
                    call: new Call(action.related),
                    original: action,
                }))
            })),
        };
    },
    methods: {
        getContactData(contact) {
            if (contact.patient) {
                return new Patient(contact.patient);
            }
            if (contact.employees) {
                return new Employee(contact.employees);
            }
            return contact;
        },
        isPatient(contact) {
            return contact.patient !== undefined
                || contact instanceof Patient;
        },
        selectContact(contact) {
            if (contact instanceof Patient) {
                this.$emit('select-patient', contact);
            } else if (contact instanceof Employee) {
                this.$emit('select-employee', contact);
            }
        },
        showCabinet(patient) {
            this.$emit('show-cabinet', patient);
        },
        editCall(call, action) {
            this.$modalComponent(EditCall, {item: call}, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    saved: (dialog, model) => {
                        dialog.close();
                        this.$info(__('Звонок успешно обновлен'));
                        model.fetch().then(() => {
                            action.related = model.attributes;
                            this.$refs.table.refresh();
                        });
                    },
                }, 
                {
                    header: __('Редактировать звонок'),
                    width: '1100px',
                }
            );
        },
    },
};
</script>
