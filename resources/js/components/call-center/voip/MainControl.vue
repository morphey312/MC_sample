<template>
    <div class="main-control">
        <div class="form">
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-switch
                        :disabled="!isProcessing"
                        :entity="processState.processLog"
                        options="call_process_is_patient"
                        property="is_patient"
                        :label="__('Кто звонит')" />
                    <form-row
                        name="contact_id"
                        :label="__('Абонент')">
                        <a
                            slot="label-addon"
                            href="#"
                            @click.prevent="selectContact">
                            {{ __('Добавить контакт') }}
                        </a>
                        <el-select v-model="selectedContact">
                            <el-option key="0" :value="null" :label="__('Быстрый набор')" />
                            <el-option
                                v-for="item in processState.contactPool"
                                :key="item.uid"
                                :value="item.uid"
                                :label="item.name + (isContactInHold(item) ? __(' (Удерживается)') : '')" />
                        </el-select>
                    </form-row>
                </el-col>
                <el-col :span="8">
                    <form-switch
                        :disabled="!isProcessing"
                        :entity="processState.processLog"
                        options="call_process_visit_type"
                        property="is_first_visit"
                        :label="__('Тип обращения')" />
                    <form-row
                        name="subscriberNumber"
                        :label="__('Номер абонента')">
                        <el-input
                            v-if="processState.currentContact === undefined"
                            v-model="processState.phoneNumber"
                            :placeholder="__('Введите номер SIP')" />
                        <el-select
                            v-else-if="processState.currentContact.numbers.length > 1"
                            v-model="processState.phoneNumber">
                            <el-option
                                v-for="item in processState.currentContact.numbers"
                                :key="item.number"
                                :value="item.number"
                                :label="item.number">
                                {{ item.number }}
                                <span v-if="item.comment">({{ item.comment }})</span>
                            </el-option>
                        </el-select>
                        <el-input
                            v-else
                            :value="processState.phoneNumber"
                            :readonly="true" />
                    </form-row>
                </el-col>
                <el-col :span="8">
                    <form-row
                        name="status"
                        :label="__('Результат обработки')">
                        <result-select
                            :model="processState.processLog"
                            :disabled="!isProcessing" />
                    </form-row>
                    <form-select
                        :disabled="!isProcessing"
                        :entity="processState.processLog"
                        :options="clinics"
                        property="clinic"
                        :filterable="true"
                        :label="__('Клиника')"/>
                </el-col>
            </el-row>
        </div>
        <div class="buttons">
            <el-button
                v-if="canAnswerCall"
                type="primary"
                @click="answerCall">
                {{ __('Принять') }}
            </el-button>
            <el-button
                v-else
                type="primary"
                :disabled="!canCallContact"
                @click="callContact">
                {{ __('Позвонить') }}
            </el-button>

            <el-button
                v-if="canRejectCall"
                type="danger"
                @click="rejectCall">
                {{ __('Отклонить') }}
            </el-button>
            <el-button
                v-else
                :disabled="!canTerminateCall"
                type="danger"
                @click="terminateCall">
                {{ __('Завершить разговор') }}
                <span v-if="currentCallDuration !== 0">/ {{ $formatter.durationShortFormat(currentCallDuration, 'ms') }}</span>
            </el-button>

            <el-button
                v-if="parkedCall !== null"
                :disabled="!canStartCall"
                @click="unholdCall(parkedCall)">
                {{ __('Снять с удержания') }}
                <span v-if="parkedCallTime !== 0">/ {{ $formatter.durationShortFormat(parkedCallTime, 'ms') }}</span>
            </el-button>
            <el-button
                v-else
                :disabled="!canHoldCall"
                @click="holdCall">
                {{ __('Удерживать') }}
            </el-button>

            <el-button
                :disabled="!canCompleteProcess"
                @click="completeProcessCall">
                {{ __('Завершить обработку') }}
            </el-button>

            <el-button
                v-if="isConference"
                @click="showConference">
                {{ __('Конференция') }}
            </el-button>
            <el-button
                v-else
                :disabled="!canStartConference"
                @click="startConference">
                {{ __('Создать конференцию') }}
            </el-button>

            <el-button
                :disabled="isProcessing"
                @click="clearFormData">
                {{ __('Очистить данные') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import VoipMixins from '../mixins/voip';
import ClinicRepository from '@/repositories/clinic';
import CONSTANTS from '@/constants';
import ResultSelect from './ResultSelect.vue';
import SearchContact from '../voip/SearchContact.vue';
import {PatientContact, EmployeeContact} from '@/services/sip-ua/process-state';
import Conference from './Conference.vue';
import SiteEnquiry from '@/models/site-enquiry';
import CallRepository from '@/repositories/call';
import AppointmentRepository from '@/repositories/appointment';
import WaitListRecord from '@/models/wait-list-record';

const SIP_REGEXP = new RegExp('^[0-9]{3,5}$');

export default {
    components: {
        ResultSelect,
    },
    mixins: [
        VoipMixins,
    ],
    props: {
        ua: Object,
    },
    data() {
        return {
            clinics: [],
            selectedContact: null,
            parkedCall: null,
            currentCallDuration: 0,
            parkedCallTime: 0,
        };
    },
    computed: {
        canCallContact() {
            return this.canStartCall
                && this.parkedCall === null
                && this.validContactSelected();
        },
        canCompleteProcess() {
            return this.ua.parkedCalls.length === 0
                && !this.hasCall()
                && this.isProcessing;
        },
        isProcessing() {
            return this.processState.processing;
        },
        processState() {
            return this.$store.state.processState;
        },
    },
    created() {
        this.updateTimers = () => {
            if (this.parkedCall !== null) {
                this.parkedCallTime = this.parkedCall.waitingTime;
            }
            if (this.ua.stateManager.isInCall) {
                this.currentCallDuration = this.ua.stateManager.currentCallDuration;
            }
        }
    },
    mounted() {
        this.getLists();
        this.revert();
        this.$ticker.on(this.updateTimers);
    },
    beforeDestroy() {
        this.$ticker.off(this.updateTimers);
    },
    methods: {
        getLists() {
            let clinic = new ClinicRepository();
            clinic.fetchList().then((response) => {
                this.clinics = response;
            });
        },
        clearFormData() {
            this.selectedContact = null;
            this.processState.reset();
            this.$store.commit('processState', this.processState);
            this.$eventHub.$emit('voip:cleardata');
        },
        selectContact() {
            this.$modalComponent(SearchContact, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                patientSelected: (dialog, patient) => {
                    dialog.close();
                    this.selectPatient(patient);
                },
                employeeSelected: (dialog, employee) => {
                    dialog.close();
                    this.selectEmployee(employee);
                },
            },
            {
                header: __('Выбор контакта'),
                width: '1100px',
            });
        },
        validContactSelected() {
            return this.processState.currentContact !== undefined
                || SIP_REGEXP.test(this.processState.phoneNumber);
        },
        callContact() {
            let numberToCall = this.processState.phoneNumber;
            if (this.processState.currentContact !== undefined) {
                let number = _.find(this.processState.currentContact.numbers, (number) => {
                    return number.number === numberToCall;
                });
                if (number !== undefined && _.isFilled(number.clinic)) {
                    numberToCall = this.addClinicPrefix(numberToCall, number.clinic);
                }
            }
            this.startCall(numberToCall);
        },
        addClinicPrefix(number, clinic) {
            if (number.length === 10) {
                // Ukraine, short
                return [String(clinic), '+', '38', number].join('');
            }
            if (number.length > 10) {
                // International
                return [String(clinic), '+', number].join('');
            }
            return number;
        },
        selectPatient(patient) {
            let contact = new PatientContact(patient);
            this.processState.addContact(contact);
            this.selectedContact = contact.uid;
            this.processState.phoneNumber = contact.defaultNumber;
            this.processState.processLog.clinic = patient.clinics[0];
        },
        selectEmployee(employee) {
            let contact = new EmployeeContact(employee);
            this.processState.addContact(contact);
            this.selectedContact = contact.uid;
            this.processState.phoneNumber = contact.defaultNumber;
        },
        completeProcessCall() {
            this.$clearErrors();
            this.assignContact(this.processState.processLog);
            if (_.isFilled(this.processState.processLog.enquiry)) {
                return this.completeEnquiry();
            }
            if (_.isFilled(this.processState.processLog.wait_list_record) && this.processState.processLog.status === CONSTANTS.PROCESS_LOG.STATUS.PROCESSED) {
                return this.completeWaitListRecord(this.processState.processLog.wait_list_record);
            }
            return this.saveProcessLog();
        },
        completeWaitListRecord(wait_list_record_id) {
            let waitListRecord = new WaitListRecord({id: wait_list_record_id});
            return waitListRecord.fetch().then(() => {
                if (_.isVoid(waitListRecord.specialization_id)) {
                    return this.saveProcessLog();
                }
                let requests = [];
                let relatedCalls = this.processState.processLog.related_actions.filter(action => {
                    return action.action === CONSTANTS.CALL_ACTION.TYPE.CREATE
                        && CONSTANTS.CALL_ACTION.SUBJECT.CALL == action.related_type;
                });

                if (relatedCalls.length != 0) {
                    let callIds = relatedCalls.map(action => action.related_id);
                    let callRepository = new CallRepository();
                    requests.push(callRepository.fetchList({id: callIds}));
                }

                let relatedAppointments = this.processState.processLog.related_actions.filter(action => {
                    return action.action === CONSTANTS.CALL_ACTION.TYPE.CREATE
                        && CONSTANTS.CALL_ACTION.SUBJECT.APPOINTMENT == action.related_type;
                });

                if (relatedAppointments.length != 0) {
                    let appointmentIds = relatedAppointments.map(action => action.related_id);
                    let appointmentRepository = new AppointmentRepository();
                    requests.push(appointmentRepository.fetchList({id: appointmentIds}));
                }

                if (requests.length != 0) {
                    return Promise.all(requests).then(response => {
                        let list = [...response[0], ...(response[1] ? response[1] : [])];
                        let anotherSpecialization = list.find(item => {
                            return item.specialization_id != waitListRecord.specialization_id;
                        });
                        if (anotherSpecialization) {
                            return this.$confirm(__('Специализация в результатах обработки отличается от специализации в листе ожидания, создать новую заявку?'), () => {
                                return this.processState.processLog.validate().then(errors => {
                                    if (_.isEmpty(errors)) {
                                        let newRecord = new WaitListRecord({...waitListRecord.attributes, id: null, operator_id: null});
                                        return newRecord.save().then(() => this.saveProcessLog());
                                    }
                                    return this.$displayErrors({errors});
                                });
                            }, {
                                cancelled: () => {
                                    return this.saveProcessLog();
                                },
                                confirmBtnText: __('Создать заявку'),
                                cancelBtnText: __('Завершить'),
                            });
                        }
                        return this.saveProcessLog();
                    });
                }
                return this.saveProcessLog();
            });
        },
        completeEnquiry() {
            let enquiry = new SiteEnquiry({id: this.processState.processLog.enquiry});
            return enquiry.fetch(['services']).then(() => {
                if (enquiry.has_available_services !== false) {
                    return this.$confirm(__('Не все услуги из заявки добавлены в записи на прием. Вернутся к обработке?'), () => {
                        return;
                    }, {
                        cancelled: () => {
                            return this.saveProcessLog(true, enquiry);
                        },
                        confirmBtnText: __('Вернутся'),
                        cancelBtnText: __('Завершить'),
                    });
                }
                return this.saveProcessLog(false, enquiry);
            });
        },
        saveProcessLog(enquiryHasServices = false, enquiry = null) {
            if (this.processState.processLog.status === CONSTANTS.PROCESS_LOG.STATUS.IMPROCESSIBLE) {
                if (enquiryHasServices || (enquiry && enquiry.has_used_services)) {
                    return this.$error(__('Вы не можете выбрать этот статус для заявки с оплаченными услугами'));
                }
            }

            this.processState.processLog.save().then((response) => {
                this.$info(__('Результат обработки успешно сохранен'));
                this.$eventHub.$emit('processLog:completed', this.processState.processLog);
                this.clearFormData();
                this.endWrapUp();
            }).catch((e) => {
                if (e.errors !== undefined && e.errors.related_actions !== undefined) {
                    this.$nextTick(() => {
                        this.$error(__('Во время обработки не было создано информационного звонка или записи на прием'));
                    });
                    if (Object.keys(e.errors).length > 1) {
                        this.$displayErrors(e);
                    }
                } else {
                    this.$displayErrors(e);
                }
            });
        },
        assignContact(processLog) {
            let contact = this.processState.currentContact;
            if (contact === undefined || contact.isUnknown) {
                processLog.contact_id = null;
                processLog.contact_type = null;
            } else {
                processLog.contact_id = contact.id;
                processLog.contact_type = contact.type;
            }
            processLog.phone_number = this.processState.phoneNumber;
        },
        revert() {
            if (this.processState.currentContact !== undefined) {
                this.selectedContact = this.processState.currentContact.uid;
                this.updateParkedCall(this.processState.currentContact);
            }
        },
        isContactInHold(contact) {
            return contact.numbers.some((number) => this.ua.isParked(number.number));
        },
        updateParkedCall(contact) {
            if (contact !== undefined) {
                for (let number of contact.numbers) {
                    let parked = this.ua.getParkedCall(number.number);
                    if (parked !== undefined) {
                        this.parkedCallTime = parked.waitingTime;
                        this.parkedCall = parked;
                        return;
                    }
                }
            }
            this.parkedCallTime = 0;
            this.parkedCall = null;
        },
        startConference() {
            this.ua.startConference().then(() => {
                this.showConference();
            }).catch(() => {
                this.$error(__('Не удалось создать конференц-звонок'));
            });
        },
        showConference() {
            this.$modalComponent(Conference, {
                ua: this.ua,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            },
            {
                header: __('Конференц-звонок'),
                width: '600px',
            });
        },
    },
    watch: {
        selectedContact(val) {
            if (val === null) {
                this.processState.phoneNumber = null;
            }
            this.processState.selectContact(val);
            this.updateParkedCall(this.processState.currentContact);
        },
        ['processState.currentContact'](val) {
            if (val === undefined) {
                this.selectedContact = null;
            } else {
                this.selectedContact = val.uid;
            }
        },
        ['ua.parkedCalls'](val) {
            this.updateParkedCall(this.processState.currentContact);
        },
        ['ua.call'](val) {
            if (val === null) {
                this.currentCallDuration = 0;
            }
        },
    },
};
</script>
