<template>
    <div
        class="container"
        :class="{'vacation-day': isVacationDay}"
    >
        <div
            v-for="time in timeList"
            :key="time"
            class="schedule-item"
        >
            <cell
                :time="time"
                :specializations="getCellSpecializations(time)"
                :appointment-list="appointmentList"
                :appointment-duration="appointmentDuration"
                :appointment-statuses="appointmentStatuses"
                :column="columnDay.id"
                :locked="isLocked(time)"
                :out-of-daysheet="isOutOfDaySheet(time)"
                :collapsed="collapsed"
                :readonly="readonly"
                :can-create="canCreate"
                :cashier="cashier"
                :cashboxes="cashboxes"
                :inactive-statuses="inactiveStatuses"
                :time-range="timeRange"
                :column-model="columnDay"
                :checkbox-cashboxes="checkboxCashboxes"
                @dropped="handleDrop"
                @lock-period="lockPeriod"
                @lock-doctor-period="lockDoctorPeriod"
                @unlock-period="unlockPeriod"
                @watch-block="watchBlock"
                @add-appointment="newAppointment"
                @edit-appointment="editAppointment"
                @delete-appointment="deleteAppointment"
                @paste-appointment="pasteAppointment"
                @copy-appointment="copyAppointment"
            />
        </div>
    </div>
</template>

<script>
import Cell from './Cell.vue';
import Appointment from '@/models/appointment';
import AppointmentRespository from '@/repositories/appointment';
import DaySheet from '@/models/day-sheet';
import DaySheetColumn from '@/mixins/day-sheet/column';
import DoctorBlockModal from './modals/DoctorBlock.vue';
import ReasonUnBlockModal from './modals/ReasonUnblock.vue';
import DaySheetMapTime from '@/mixins/day-sheet/map-time';
import FormDelete from '@/components/appointments/modal/form/FormDelete.vue';
import FormEdit from '@/components/appointments/modal/form/FormEdit.vue';
import FormCreate from '@/components/appointments/modal/form/FormCreate.vue';
import FormHeaderButtons from '@/components/appointments/modal/form/HeaderButtons.vue';
import CONSTANTS from '@/constants';
import MESSAGES from '@/messages';
import StatusMixin from '@/components/appointments/mixin/status';

export default {
    components: {
        Cell,
    },
    mixins: [
        DaySheetColumn,
        DaySheetMapTime,
        StatusMixin,
    ],
    props: {
        day: {
            type: Object,
            required: true,
        },
        timeRange:  {
            type: [Object, Array],
            required: true
        },
        patient: {
            type: Object,
        },
        collapsed: {
            type: Boolean,
            default: false,
        },
        readonly: {
            type: Boolean,
            default: false,
        },
        inactiveStatuses: {
            type: Array,
            default: () => [],
        },
        appointmentStatuses: {
            type: Array,
            default: () => [],
        },
        cashier: Object,
        cashboxes: {
            type: Array,
            default: () => [],
        },
        checkboxCashboxes: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            model: new DaySheet(this.day),
            appointments: this.day.appointments,
            timeList: [],
            appointmentList: {},
            appointmentDuration: null,
            daySheetData: {},
            columnDay: this.day,
            relatedSheets: [],
            copiedItem: null,
            loggedEmployeeId: this.$store.state.user.employee_id,
            canCreate: this.$canManage('appointments.create', [this.day.clinic_id]),
        }
    },
    computed: {
        isVacationDay() {
            return this.columnDay.time_sheets.length === 0;
        },
    },
    watch: {
        ['day.id'](val) {
            if (val === null) {
                return;
            }
            this.getRelatedSheets().then(() => {
                this.columnDay = this.day;
                this.appointments = this.day.appointments;
                this.initLockValues();
                this.createTimeAndAppointmentList();
            });
        },
        ['appointments'](val) {
            this.columnDay.appointments = val;
            this.createTimeAndAppointmentList();
        },
        ['$store.state.clipboard'](val) {
            if(!_.isEmpty(val) && this.appointmentCanBeCopied(val)) {
                this.copiedItem = val;
            } else {
                this.copiedItem = null;
            }
        },
        timeRange() {
            this.mapTimes();
        },
    },
    beforeMount() {
        this.initLockValues();
        this.createTimeAndAppointmentList();
    },
    created() {
        this.listenDaysheetLock = ({data}) => {
            if (data[this.columnDay.id]) {
                this.columnDay.locks = data[this.columnDay.id];
                this.initMomentValues(this.columnDay.locks, this.columnDay.date);
                this.$eventHub.$emit('add-lock-alert', {
                    locks: this.columnDay.locks,
                    id: this.columnDay.id,
                    date: this.columnDay.date,
                });
            }
        }

        this.listenAppointmentCreated = ({data}) => {
            if(this.isCurrentColumnAppointment(data)) {
                this.updateAppointmentList(data);
                this.unlockOnAppointmentCreated(data);
            }
            this.reloadRelatedSheet(data);
        }

        this.listenAppointmentUpdated = ({data}) => {
            if (this.isCurrentColumnAppointment(data)) {
                this.updateAppointmentList(data);
            } else if (this.columnHasPastedAppointment(data)) {
                this.deleteFromAppointmentList(data.id);
            }
            this.reloadRelatedSheet(data);
        }

        this.listenAppointmentStatusDeleted = ({data}) => {
            if(this.isCurrentColumnAppointment(data)) {
                this.deleteFromAppointmentList(data.id);
            }
            this.reloadRelatedSheet(data);
        }

        this.listenAppointmentPasted = ({data, skipId}) => {
            if (this.isCurrentColumnAppointment(data) && skipId != this.columnDay.id) {
                this.deleteFromAppointmentList(data.id);
            }
            this.reloadRelatedSheet(data);
        }
    },
    mounted() {
        this.getRelatedSheets();
        this.$eventHub.$emit('add-lock-alert', {
            locks: this.columnDay.locks,
            id: this.columnDay.id,
            date: this.columnDay.date,
        });

        this.$eventHub.$on('broadcast.day_sheet_lock', this.listenDaysheetLock);
        this.$eventHub.$on('broadcast.appointment_created', this.listenAppointmentCreated);
        this.$eventHub.$on('broadcast.appointment_updated', this.listenAppointmentUpdated);
        this.$eventHub.$on('broadcast.appointment_status_deleted', this.listenAppointmentStatusDeleted);
        this.$eventHub.$on('appointment-pasted', this.listenAppointmentPasted);
    },
    beforeDestroy() {
        this.$eventHub.$off('broadcast.day_sheet_lock', this.listenDaysheetLock);
        this.$eventHub.$off('broadcast.appointment_created', this.listenAppointmentCreated);
        this.$eventHub.$off('broadcast.appointment_updated', this.listenAppointmentUpdated);
        this.$eventHub.$off('broadcast.appointment_status_deleted', this.listenAppointmentStatusDeleted);
        this.$eventHub.$off('appointment-pasted', this.listenAppointmentPasted);
    },
    methods: {
        appointmentCanBeCopied({type, data}) {
            if (type && type !== 'appointment') {
                return false;
            }

            if (this.columnDay.clinic_id == data.clinic_id && this.hasSameSpecialization(data.specialization_id)) {
                return true;
            }
            return false;
        },
        hasSameSpecialization(specialization_id) {
            let sameSpecialization = false;

            this.columnDay.time_sheets.forEach((sheet) => {
                if(sheet.specializations.indexOf(specialization_id) !== -1) {
                    sameSpecialization = true;
                }
            });
            return sameSpecialization;
        },
        getRelatedSheets() {
            if (this.day.related_sheets.length === 0) {
                return Promise.resolve([]);
            }
            let model = new DaySheet({id: this.day.id});
            return model.getRelatedSheets().then((response) => {
                this.relatedSheets = this.mapAppointmentTimes(response);
            });
        },
        reloadRelatedSheet(appointment) {
            if (this.relatedSheets.length == 0) {
                return;
            }

            let related = this.relatedSheets.find((sheet) => {
                return sheet.day_sheet_owner_id == appointment.doctor_id &&
                       sheet.day_sheet_owner_type == appointment.doctor_type &&
                       sheet.date == appointment.date;
            });

            if (_.isNil(related)) {
                return;
            }
            return this.getRelatedSheets();
        },
        getCellSpecializations(time) {
            this.initMomentValues(this.columnDay.time_sheets, this.columnDay.date);
            let cellTime = this.$moment(`${this.columnDay.date} ${time}:00`);
            let output = [];

            this.columnDay.time_sheets.forEach((sheet) => {
                let from = sheet.momented.start;
                let to = sheet.momented.end;
                let specializationData = sheet.specialization_data;
                if (cellTime.isSame(from) || cellTime.isBetween(from, to)) {
                    Object.keys(specializationData).forEach((key) => {
                        let list = sheet.specialization_data[key];
                        if (list.short_name && output.indexOf(list.short_name) === -1) {
                            list.id = key;
                            output.push(list);
                        }
                    });
                }
            });

            return output;
        },
        isCurrentColumnAppointment(appointment) {
            return appointment.date == this.columnDay.date &&
                   appointment.clinic_id == this.columnDay.clinic.id &&
                   appointment.doctor_id == this.columnDay.doctor.id &&
                   appointment.doctor_type == this.columnDay.day_sheet_owner_type &&
                   appointment.workspace_id == this.columnDay.workspace_id;
        },
        columnHasPastedAppointment(appointment) {
            let match = this.appointments.find(item => {
                return appointment.id === item.id && (
                    appointment.workspace_id != item.workspace_id ||
                    appointment.date != item.date ||
                    appointment.doctor_id != item.doctor_id
                );
            });
            return _.isFilled(match);
        },
        mapTimes() {
            this.timeList = [...this.timeRange];
        },
        isOutOfDaySheet(time) {
            let momentedTime = this.$moment(`${this.columnDay.date} ${time}:00`);
            let isDisabled = true;
            isDisabled = this.cellTimeIsInTimeSheets(isDisabled, momentedTime);

            if (this.relatedSheets.length == 0) {
                return isDisabled;
            }
            return this.checkRelatedAppointments(isDisabled, momentedTime);
        },
        cellTimeIsInTimeSheets(isDisabled, momentedTime) {
            this.columnDay.time_sheets.forEach((timeSheet) => {
                let from = `${this.columnDay.date} ${timeSheet.time_from}`;
                let to  = `${this.columnDay.date} ${timeSheet.time_to}`;

                if(momentedTime.isSame(from) || momentedTime.isBetween(from, to)) {
                    isDisabled = false;
                }
            });
            return isDisabled;
        },
        checkRelatedAppointments(isDisabled, momentedTime) {
            this.relatedSheets.forEach((sheet) => {
                if (sheet.appointments.length != 0) {
                    sheet.appointments.forEach((appointment) => {
                        let start = this.$moment(`${this.columnDay.date} ${appointment.start}`);
                        let end = this.$moment(`${this.columnDay.date} ${appointment.end}`);;

                        if (momentedTime.isSame(start) || momentedTime.isBetween(start, end)) {
                            isDisabled = true;
                        }
                    });
                }
            });
            return isDisabled;
        },
        initLockValues() {
            this.initMomentValues(this.columnDay.locks, this.columnDay.date);
            this.appointmentDuration = this.columnDay.doctor.appointment_duration;
        },
        createTimeAndAppointmentList() {
            this.appointmentList = {};
            this.mapTimes();
            this.mapAppointmentsData(this.columnDay);
        },
        sendLocks(newLock) {
            let daySheetModel = new DaySheet(this.day);

            daySheetModel.lock(this.columnDay.locks, newLock).then(response => {
                if (response && _.isFilled(response.id)) {
                    let index = this.columnDay.locks.findIndex(lock => lock.id === response.id);
                    if (index === -1) {
                        this.columnDay.locks = [...this.columnDay.locks, response];
                        this.initMomentValues(this.columnDay.locks, this.columnDay.date);
                    }
                }
                this.$info(__('Блок установлен'));
            }).catch((error) => {
                if (error.response) {
                    let response = ((error.response || {}).response || {});
                    let errors = this.$getValidationError(response, 'item');
                    if (errors) {
                        this.$error(errors.join(', '));
                    }
                } else {
                    this.$error(__('Ошибка'));
                }
            });
        },
        unblockReason(unlockIndex) {

            let deletedItem = this.columnDay.locks[unlockIndex];
            this.$modalComponent(ReasonUnBlockModal, {
                unlockIndex: unlockIndex,
                columnDay: this.columnDay
            }, {
                close: (dialog) => {
                    dialog.close();
                },
                unblock: (dialog, doctorUnblock) => {
                    deletedItem.reason = doctorUnblock.reason;
                    deletedItem.comment = doctorUnblock.comment;
                    this.sendUnlocks(unlockIndex);
                    dialog.close();
                }
            }, {
                header: __('Разблокировать время приема врача'),
                width: '400px',
                customClass: 'no-footer',
            });


        },
        sendUnlocks(unlockIndex) {
            let daySheetModel = new DaySheet(this.day);
            let deletedItem = this.columnDay.locks[unlockIndex];
            daySheetModel.unlock(deletedItem).then(() => {
                this.columnDay.locks.splice(unlockIndex, 1);
                this.$info(__('Блок снят'));
                this.$emit('close');
            }).catch(() => {
                this.$error(__('Ошибка'));
            });
        },
        lockPeriod({time}) {
            if (_.isNil(time)) {
                return;
            }
            let start = this.$moment(`${this.columnDay.date} ${time}:00`);
            let end = this.$moment(start).add(this.appointmentDuration, 'minutes');
            let nextPeriod = this.findNextAppointment(time);
            let inLockedPeriod = this.findCrossBlockedPeriod(start, end);

            if (nextPeriod){
                let nextPeriodStart = this.getMomentedTime(nextPeriod);

                if (nextPeriodStart <= end) {
                    end = nextPeriodStart.subtract(CONSTANTS.SCHEDULE_TIME_STEP, 'minutes');
                }

                if (inLockedPeriod) {
                    end = inLockedPeriod.momented.start.subtract(CONSTANTS.SCHEDULE_TIME_STEP, 'minutes');
                }
            } else if (inLockedPeriod) {
                end = inLockedPeriod.momented.start.subtract(CONSTANTS.SCHEDULE_TIME_STEP, 'minutes');
            }

            let newLock = {
                start: time,
                end: end.format('HH:mm'),
                momented: {
                    start: start,
                    end: end
                },
                type: 'temporary',
                employee_id: this.loggedEmployeeId,
                day_sheet_id: this.columnDay.id,
            }
            this.sendLocks(newLock);
        },
        lockDoctorPeriod(lock) {
            if (_.isNil(lock.start)) {
                return;
            }
            let start = this.$moment(`${lock.date} ${lock.start}:00`);
            let end = this.$moment(start).add(lock.duration, 'minutes');

            let newLock = {
                start: lock.start,
                end: end.format('HH:mm'),
                momented: {
                    start: start,
                    end: end
                },
                reason_id: lock.reason,
                comment: lock.comment,
                type: 'fixed',
                employee_id: this.loggedEmployeeId,
                day_sheet_id: this.columnDay.id,
            }
            this.sendLocks(newLock);
        },
        unlockPeriod({time}) {
            let unlockIndex = this.getLockPeriodIndex(time);

            if (this.isUnlockable(unlockIndex)) {
                if (this.columnDay.locks[unlockIndex].type == 'fixed'){
                    this.unblockReason(unlockIndex);
                } else {
                    this.sendUnlocks(unlockIndex);
                }
            }
        },
        watchBlock({time}) {
            let unlockIndex = this.getLockPeriodIndex(time);

            this.$modalComponent(DoctorBlockModal, {
                time: time,
                timeRange: this.timeRange,
                column: this.columnDay,
                sheetSpecializations: this.getCellSpecializations(time),
                watching: unlockIndex
            }, {
                close: (dialog) => {
                    dialog.close();
                },
                block: (dialog, lock) => {
                    this.$emit('lock-doctor-period', lock);
                    this.setLockHeight(lock);
                    dialog.close();
                }
            }, {
                header: __('Блокировать время приема врача'),
                width: '800px',
                customClass: 'no-footer',
            });
            /* if (this.isUnlockable(unlockIndex)) {
                let item = this.columnDay.locks.splice(unlockIndex, 1);
                this.sendUnlocks(item[0]);
            } */
        },
        isUnlockable(unlockIndex){
            if (unlockIndex === -1 ) {
                return false;
            }
            return (this.columnDay.locks[unlockIndex].type === 'fixed' || this.loggedEmployeeId === this.columnDay.locks[unlockIndex].employee_id)
                || this.$can('appointments.unblock-hard-locks');
        },

        getAppointmentStart(time, locked) {
            let appointmentStart = time;

            if (locked) {
                let unlockIndex = this.getLockPeriodIndex(time);

                if (unlockIndex !== -1) {
                    appointmentStart = this.columnDay.locks[unlockIndex]['start'];
                }
            }
            return appointmentStart;
        },
        newAppointment({time, locked}) {
            let appointmentStart = this.getAppointmentStart(time, locked);
            this.makeDataForCreate(appointmentStart);

            if (_.isEmpty(this.patient)) {
                return this.createAppointment();
            }

            return this.getPatientWarnMessage(this.patient.id).then((message) => {
                if (message.length == 0) {
                    return this.createAppointment();
                }
                return this.$confirm(message, () => this.createAppointment());
            });
        },
        createAppointment() {
            this.$modalComponent(FormCreate, { daySheetData: this.daySheetData },
                {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    created: (dialog, appointment) => {
                        dialog.close();
                        if (!_.isEmpty(appointment)) {
                            this.onAppointmentCreated(appointment);
                        }
                    },
                    patientSelected: (dialog, patient) => {
                        dialog.getTopHeaderAddon().setPatient(patient);
                    },
                },
                {
                    header: __('Запись пациента на прием'),
                    width: '1265px',
                    headerAddon: {
                        component: FormHeaderButtons,
                        props: {
                            appointment: this.daySheetData.appointment,
                        },
                        eventListeners: {
                            discountSelected: (dialog, [card, refreshDiscountType = 0]) => {
                                dialog.getTopComponent().setDiscountCard(card, refreshDiscountType);
                            },
                            insuranceSelected: (dialog, policy) => {
                                dialog.getTopComponent().setInsurancePolicy(policy);
                            },
                            legalEntitySelected: (dialog, legalEntity) => {
                                dialog.getTopComponent().setLegalEntity(legalEntity);
                            },
                            bulkServiceSelected: (dialog, bulkService) => {
                                dialog.getTopComponent().setBulkService(bulkService);
                            },
                            ambulanceCall: (dialog, legalEntity) => {
                                dialog.getTopComponent().setAmbulanceCall(legalEntity);
                            },
                        }
                    },
                });
        },
        onAppointmentCreated(appointment) {
            this.$eventHub.$emit('update-patient-data', appointment.patient);
            this.appointments.push(appointment);
            this.unlockPeriod({time: appointment.start.substring(0,5)});
        },
        getPatientWarnMessage(patientId, id = null) {
            return this.getAllPatientAppointments(patientId, id).then((patientAppointments) => {
                let message = '';
                if (patientAppointments.length != 0) {
                    patientAppointments = _.sortBy(patientAppointments, ['start']);

                    message += __('Внимание! Есть другие записи пациента на этот день: <br />');
                    for(let appointment of patientAppointments) {
                        let subMessage = '';
                        subMessage += `${(appointment.card_number || '')} ${appointment.clinic_name} `;
                        subMessage += `${appointment.start} - ${appointment.end} `;
                        subMessage += `${appointment.doctor_name} ${appointment.specialization_name} <br />`;
                        message += subMessage;
                    }
                }
                return Promise.resolve(message);
            });
        },
        getAllPatientAppointments(patientId, id = null) {
            let appointment = new AppointmentRespository();
            let filters = {
                date: this.columnDay.date,
                patient: patientId,
                skipId: id,
                isDeleted: 0,
            };

            return appointment.fetchList(filters).then((response) => {
                return Promise.resolve(response);
            });
        },
        editAppointment({appointment}) {
            return this.getPatientWarnMessage(appointment.patient_id, appointment.id).then((message) => {
                if (message.length == 0) {
                    return this.getAppointmentEditModal(appointment);
                }
                return this.$confirm(message, () => this.getAppointmentEditModal(appointment));
            });
        },
        getAppointmentEditModal(appointment) {
            this.makeDataForEdit(appointment);
            this.$modalComponent(FormEdit, { daySheetData: this.daySheetData },
                {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    updated: (dialog, appointment) => {
                        dialog.close();
                        this.$eventHub.$emit('update-patient-data', appointment.patient);
                    },
                    patientSelected: (dialog, patient) => {
                        dialog.getTopHeaderAddon().setPatient(patient);
                    },
                },
                {
                    header: __('Запись пациента на прием'),
                    width: '1265px',
                    headerAddon: {
                        component: FormHeaderButtons,
                        props: {
                            appointment: this.daySheetData.appointment,
                        },
                        eventListeners: {
                            discountSelected: (dialog, [card, refreshDiscountType = 0]) => {
                                dialog.getTopComponent().setDiscountCard(card, refreshDiscountType);
                            },
                            insuranceSelected: (dialog, policy) => {
                                dialog.getTopComponent().setInsurancePolicy(policy);
                            },
                            legalEntitySelected: (dialog, legalEntity) => {
                                dialog.getTopComponent().setLegalEntity(legalEntity);
                            },
                            bulkServiceSelected: (dialog, bulkService) => {
                                dialog.getTopComponent().setBulkService(bulkService);
                            },
                            ambulanceCall: (dialog, legalEntity) => {
                                dialog.getTopComponent().setAmbulanceCall(legalEntity);
                            },
                        }
                    },
                });
        },
        deleteIsInvalid(appointment) {
            return this.inactiveStatuses.indexOf(appointment.appointment_status_id) === -1;
        },
        appointmentHasPayments(appointment) {
            if (appointment.services.length == 0) {
                return false;
            }
            return appointment.services.find(service => {
                return service.payed > 0;
            }) != undefined;
        },
        deleteAppointment({appointment}) {
            this.fetchAppointment({id: appointment.id}).then((response) => {
                let newAppointment = response;
                if (this.appointmentHasPayments(newAppointment)) {
                    return this.$error(__('Невозможно удалить, есть оплаченные услуги'));
                }
                if (this.deleteIsInvalid(newAppointment)) {
                    return this.$error(__('Измените статус записи сначала'));
                }

                this.makeDataForEdit(newAppointment);
                this.$modalComponent(FormDelete, { daySheetData: this.daySheetData },
                    {
                        cancel: (dialog) => {
                            dialog.close();
                        },
                        deleted: (dialog, id) => {
                            dialog.close();
                            this.deleteFromAppointmentList(id);
                        },
                    },
                    {
                        header: __('Удалить запись?'),
                        width: '400px',
                    });
            });
        },
        updateAppointmentList(appointment) {
            let appointmentModel = new Appointment({id: appointment.id});
            appointmentModel.fetch([
                'patient_assigned_analyses',
                'patient_assigned_services',
                'patient_assigned_consultations',
                'patient_debts',
                'patient_issued_discount_cards',
                'patient_insurance_policies',
                'patient_legal_entity',
                'appointment_services_prices',
                'doctor',
                'insurance_policy',
                'default',
                'existing_call_request',
                'surgery_employees',
                'clinic',
                'ambulance_call'
            ]).then(() => {
                this.rejectOldAppointment(appointment.id);
                this.appointments.push(appointmentModel);
            });

        },
        deleteFromAppointmentList(id) {
            this.daySheetData = {};
            this.rejectOldAppointment(id);
        },
        applyAppointmentAction(appointment, appointmentStart, actionCallback) {
            let period = this.makeNewAppointmentPeriod(appointment, appointmentStart);
            if (this.isInTimeSheets(period)) {
                if(this.isValidTimeDuration(appointment, appointmentStart, period)){
                    return actionCallback(appointment, period);
                }
                return this.$error(MESSAGES.ERROR.APPOINTMENT_CROSS_TIME);
            }
            return this.$error(MESSAGES.ERROR.DOCTOR_OUT_OF_TIME);
        },
        copyAppointment({time, locked}) {
            let appointmentStart = this.getAppointmentStart(time, locked);
            let appointment = this.copiedItem.data;
            return this.applyAppointmentAction(appointment, appointmentStart, this.makeAppointmentCopy);
        },
        pasteAppointment({time, locked}) {
            let appointmentStart = this.getAppointmentStart(time, locked);
            let appointment = this.copiedItem.data;
            return this.applyAppointmentAction(appointment, appointmentStart, this.makeAppointmentPaste);
        },
        handleDrop(data) {
            if(_.isEmpty(data.item)) {
                return;
            }
            return this.applyAppointmentAction(data.item, data.dropTime, this.makeDrop);
        },
        setNewAppointmentAttributes(appointment, period) {
            this.setNewAppointmentTime(appointment, period);
            appointment.doctor_id = this.columnDay.doctor.id;
            appointment.doctor_type = this.columnDay.day_sheet_owner_type;
            appointment.date = this.columnDay.date;
            appointment.clinic_id = this.columnDay.clinic_id;
            appointment.workspace_id = this.columnDay.workspace_id;
            return appointment;
        },
        clearServicesReference(appointment) {
            return appointment.services.map(service => {
                service.id = null;
                service.appointment_id = null;
                service.issued = false;
                service.expected_payment = null;

                if (service.container_type == CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES && service.items.length != 0) {
                    service.items = service.items.map(item => {
                        item.id = null;
                        item.appointment_id = null;
                        item.status = null;
                        item.assigner_id = item.assigner_id || (appointment.doctor_type == CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE ? appointment.doctor_id : null);
                        item.date_expected_pass = null;
                        item.date_pass = null;
                        item.date_expected_ready = null;
                        item.date_ready = null;
                        item.date_sent_email = null;
                        return item;
                    });
                }
                return service;
            });
        },
        makeAppointmentCopy(appointment, period) {
            this.fetchAppointment({id: appointment.id}).then((response) => {
                let newAppointment = new Appointment(response.getSaveData());
                newAppointment.id= null;
                newAppointment.appointment_status_id = this.getStatusIdBySystemStatus(this.appointmentStatuses, CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP);
                newAppointment = this.setNewAppointmentAttributes(newAppointment, period);
                if (this.isPasteInvalid(newAppointment, appointment.insurance_policy)) {
                    return this.$error(__('Период действие страхового полиса не входит в выбранную дату'));
                }
                newAppointment.services = this.clearServicesReference(newAppointment);

                if (newAppointment.card_specialization_id) {
                    newAppointment.is_first = CONSTANTS.APPOINTMENT.TYPES.REPEATED;
                }
                this.saveAppointment(newAppointment).then(() => {
                    this.appointmentList = {};
                    this.mapAppointmentsData(this.columnDay);
                });
            });
        },
        makeAppointmentPaste(appointment, period) {
            let oldAttributes = appointment.getSaveData();
            if (this.isPasteInvalid(appointment, appointment.insurance_policy)) {
                return this.$error(__('Период действие страхового полиса не входит в выбранную дату'));
            }

            this.fetchAppointment({id: appointment.id}).then((response) => {
                let newAppointment = response;
                this.setNewAppointmentAttributes(newAppointment, period);

                this.saveAppointment(newAppointment).then(() => {
                    this.$eventHub.$emit('appointment-pasted', {data: oldAttributes, skipId: this.columnDay.id});
                    this.$store.commit('clearClipboard');
                    this.$eventHub.$emit('clipboard-cleared');
                    this.appointmentList = {};
                    this.mapAppointmentsData(this.columnDay);
                });
            });
        },
        fetchAppointment(attributes) {
            let appointment = new Appointment(attributes);
            return appointment.fetch(['appointment_services']).then(() => {
                return Promise.resolve(appointment);
            });
        },
        isPasteInvalid(appointment, policy) {
            if (appointment.insurance_policy_id == null && policy == null) {
                return false;
            }
            return this.$moment(policy.expires).isBefore(appointment.date, 'day');
        },
        makeDrop(appointment, period) {
            this.fetchAppointment({id: appointment.id}).then((response) => {
                let newAppointment = response;
                this.setNewAppointmentTime(newAppointment, period);
                this.saveAppointment(newAppointment);
            });
        },
        saveAppointment(appointment) {
            return appointment.save().then((response) => {
                this.$info(__('Запись была успешно сохранена'));
                return Promise.resolve();
            }).catch((e) => {
                this.$error(e);
            });
        },
        unlockOnAppointmentCreated(appointment) {
            if(this.columnDay.locks.length == 0) {
                return;
            }

            let locks = this.findLocksByAppointment(appointment);

            locks.forEach((lock) => {
                let unlockIndex = this.getLockPeriodIndex(lock.start);
                if (unlockIndex != -1) {
                    this.sendUnlocks(unlockIndex);
                }
            });
        },
        findLocksByAppointment(appointment) {
            let appointmentStart = this.$moment(`${this.columnDay.date} ${appointment.start}`);
            let appointmentEnd = this.$moment(`${this.columnDay.date} ${appointment.end}`);

            return _.filter(this.columnDay.locks, (lock) => {
                if(lock.type === 'fixed'){
                    return false;
                }

                let start = lock.momented.start;
                let end = lock.momented.end;

                return (appointmentStart.isSame(start) || appointmentStart.isBetween(start, end)) ||
                       (appointmentEnd.isSame(end) || appointmentEnd.isBetween(start, end));
            });
        },
    }
}
</script>
