import AppointmentRepository from '@/repositories/appointment';
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS from '@/constants';
import StatusReason from '@/components/appointments/modal/form/StatusReason.vue';
import DelayReason from '@/components/appointments/modal/form/DelayReason.vue';
import StatusMixin from './status';
import AppointmentStatusRepository from '@/repositories/appointment/status';

export default {
    mixins: [
        StatusMixin,
    ],
    props: {
        daySheetData: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            model: null,
            statusWhenDelayReasonAdd: null,
            delayReasonModel: null,
            totalCost: 0,
            first_patients: [],
            patientCardList: [],
            statuses: [],
            actionEdit: false,
            insurancePolicy: null,
            surgerySpecialization: this.getSurgerySpecialization(),
        };
    },
    mounted() {
        this.$eventHub.$on('total-changed', this.setTotalCost);
    },
    beforeDestroy() {
        this.$eventHub.$off('total-changed', this.setTotalCost);
    },
    watch: {
        ['model.specialization_id']() {
            this.setActivePatientCard()
            if (!this.checkIsUltraSoundSpecialityAndCardNull())
                this.setPatientIsFirst(this.model.card_specialization_id);
        },
        ['model.appointment_status_id']() {
            this.verifyStatusReasons();
        },
        ['model.card_specialization_id'](val) {
            if (!this.checkIsUltraSoundSpecialityAndCardNull())
                this.setPatientIsFirst(val);
        },
    },
    computed: {
        currentStatus() {
            return this.getCurrentStatus();
        }
    },
    methods: {
        getLaboratoryAnalysisItems() {
            let items = [];
            this.model.analyses_results.map((analysis) => {
                if (_.isFilled(analysis.lab_analysis_id)
                    && _.isFilled(analysis.date_pass)
                    && !_.isFilled(analysis.laboratory_containers)) {
                    items.push(analysis);
                }

            });
            return items;
        },
        allPassedAnalysis() {
            return this.model.analyses_results.every((item) => _.isFilled(item.date_pass))
                || this.model.analyses_results.every((item) => _.isVoid(item.date_pass));
        },
        setInsurancePolicy(policy = null) {
            this.insurancePolicy = policy;
            this.model.insurance_policy_id = policy ? policy.id : null;
        },
        setLegalEntity(legalEntity = null) {
            this.model.legal_entity_id = legalEntity ? legalEntity.id : null;
        },
        setAmbulanceCall(ambulanceCall) {
            this.model.ambulance_call = ambulanceCall;
            this.setAmbulanceCallAppointmentComment();
        },
        setAmbulanceCallAppointmentComment() {
            if (this.model.comment === "") {
                let string = '';

                if (this.model.patient.full_name) {
                    string += this.model.patient.full_name + ', \n';
                }
                if (this.model.patient.age) {
                    string += this.model.patient.age + ' лет \n'
                }

                string += 'Вызвал: ' + this.model.ambulance_call.caller + '\n';

                if (this.model.ambulance_call.created_at) {
                    string += 'в ' + this.model.ambulance_call.created_at + '\n';
                } else {
                    let today = new Date();
                    string += 'в ' + today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds() + '\n';
                }
                if (this.model.start && this.model.date) {
                    string += 'на ' + this.model.start + ' ' + this.model.date + '\n';
                }

                string += this.model.ambulance_call.phone + '\n';

                string += this.model.ambulance_call.street + ' ' + this.model.ambulance_call.house;

                if (this.model.ambulance_call.flat) {
                    string += 'кв.' + this.model.ambulance_call.flat;
                }

                if (this.model.ambulance_call.porch) {
                    string += ', ' + this.model.ambulance_call.porch + ' подъезд';
                }

                if (this.model.ambulance_call.storey) {
                    string += ', ' + this.model.ambulance_call.storey + ' этаж' + '\n';
                }

                if (this.model.ambulance_call.comment) {
                    string += 'Примечание: ' + this.model.ambulance_call.comment + '\n';
                }

                if (this.model.ambulance_call.reason) {
                    string += 'Повод: ' + this.model.ambulance_call.reason;
                }

                this.model.comment = string;
            }
        },
        loadStatuses() {
            let repository = new AppointmentStatusRepository();
            let filters = _.onlyFilled({
                ...(this.model.is_deleted
                    ? {}
                    : {system_status_not: CONSTANTS.APPOINTMENT.STATUSES.DELETED}),
                ...(this.surgerySpecialization
                    ? {}
                    : {for_surgery: 0}),
            });
            repository.fetchList(filters).then((res) => {
                this.statuses = res;
                if (!this.model.appointment_status_id) {
                    this.model.appointment_status_id = this.getStatusIdBySystemStatus(this.statuses, CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP);
                }
            });
        },
        getSurgerySpecialization() {
            let specialization = this.daySheetData.specializations
                .find(spec => spec.service_group === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY);
            return specialization ? specialization.id : null;
        },
        verifyStatusReasons() {
            if (!this.model.isNew() && this.isDelayReasonRequired()) {
                let updates = this.model.changed();
                if (updates.indexOf('appointment_status_id') !== -1) {
                    let start = this.$moment(`${this.model.date} ${this.getTimeString(this.model.start)}`);
                    let now = this.$moment();
                    if (start.isAfter(now)) {
                        return;
                    }
                    if (now.diff(start, 'minutes') > this.currentStatus.delay) {
                        return this.setDelayReason(now.diff(start, 'seconds'));
                    }
                }
            }
            if (this.isStatusReasonRequired()) {
                return this.setStatusReason();
            }
            return;
        },
        statusReasonIsEmpty() {
            return this.isStatusReasonRequired() && !this.model.status_reason_id;
        },
        getCurrentStatus() {
            return this.getStatuses().find((status) => {
                return status.id == this.model.appointment_status_id
            });
        },
        getStatuses() {
            return this.statuses;
        },
        isDelayReasonRequired() {
            return this.currentStatus && (this.currentStatus.has_delay == true)
        },
        isStatusReasonRequired() {
            return this.currentStatus && (this.currentStatus.status_reason == true);
        },
        saveDelayReason() {
            if (this.getCurrentStatus() === this.statusWhenDelayReasonAdd) {
                this.delayReasonModel.save()
            }
        },
        setDelayReason(duration) {
            return this.$modalComponent(DelayReason, {
                appointment: this.model,
                status: this.currentStatus,
                duration: duration,
            }, {
                created: (dialog, model) => {
                    this.delayReasonModel = model
                    this.statusWhenDelayReasonAdd = this.getCurrentStatus()
                    dialog.close();
                },
            }, {
                header: __('Укажите причину задержки выбора статуса'),
                width: '400px',
                showClose: false,
            });
        },
        setStatusReason() {
            return this.$modalComponent(StatusReason, {
                model: this.model,
                status: this.currentStatus,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                reason_updated: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Укажите причину выбора статуса'),
                width: '400px',
            });
        },
        isPatientCardRequired() {
            return (this.currentStatus && this.currentStatus.patient_card_required) && _.isVoid(this.model.card_specialization_id);
        },
        isTimeLocked() {
            let start = this.$moment(`${this.model.date} ${this.getTimeString(this.model.start)}`);
            let end = this.$moment(`${this.model.date} ${this.getTimeString(this.model.end)}`);

            let overlapping = false;

            _.each(this.daySheetData.locks, (lock) => {
                if ((start.isBetween(lock.momented.start, lock.momented.end, null, '()') ||
                        end.isBetween(lock.momented.start, lock.momented.end, null, '()')) &&
                    lock.type === 'fixed') {
                    overlapping = true;
                }
            });

            return overlapping;
        },
        setPatient(patient) {
            this.daySheetData.patients = [
                {
                    id: patient.id,
                    value: patient.full_name,
                }
            ];
            this.fetchPatient(patient).then(() => {
                let newPatient = true
                this.model.patient = patient;
                this.model.patient_id = patient.id;
                this.$emit('patientSelected', this.model.patient);
                if (this.model.patient && this.model.patient.id === patient.id)
                    newPatient = false;
                this.setActivePatientCard(newPatient);
            });
        },
        fetchPatient(patient) {
            return patient.fetch([
                'assigned_analyses',
                'assigned_services',
                'assigned_consultations',
                'discount_cards',
                'cards',
                'debts',
                'insurance_policies',
                'legal_entity',
            ])
                .then(() => {
                    return Promise.resolve(patient);
                });
        },
        createPatientCardList() {
            if (!this.model.patient || this.model.patient.isNew()) {
                return;
            }

            let patientCard = this.model.patient.cards[0];
            let cardList = [];

            if (patientCard && patientCard.specializations.length > 0) {
                patientCard.specializations.forEach((card) => {
                    cardList.push({
                        id: card._attributes.id,
                        value: `${patientCard.number} ${card._attributes.specialization.short_name}`,
                        specialization_id: card._attributes.specialization_id,
                        service_group: card._attributes.specialization.service_group,
                    });
                });
            }

            this.patientCardList = cardList;
        },
        setActivePatientCard(changed = false) {
            this.createPatientCardList();
            let cardSpecialization = this.model.card_specialization_id;
            if (cardSpecialization && !this.model.isNew()) {
                return;
            }
            if (!cardSpecialization || changed) {
                cardSpecialization = this.patientCardList.find(card => card.specialization_id == this.model.specialization_id);
                this.model.card_specialization_id = cardSpecialization ? cardSpecialization.specialization_id : null;
            }
        },
        setPatientIsFirst(card) {
            if (this.model.patient_id == null) {
                return;
            }

            let surgeryMark = CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY;
            let patientCard = card && this.patientCardList.length !== 0 ? this.patientCardList.find(item => item.specialization_id === card) : null
            let isSurgery = patientCard ? patientCard.service_group === surgeryMark : false;

            if (this.model.card_specialization_id && (this.model.card_specialization_id != this.model.specialization_id) && !isSurgery) {
                this.model.is_first = CONSTANTS.APPOINTMENT.TYPES.REPEATED;
            } else if (_.isNil(card)) {
                this.model.is_first = CONSTANTS.APPOINTMENT.TYPES.FIRST;
            } else if (isSurgery) {
                this.changeStatusByLastAppointments([CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP], true, true)
            } else {
                this.changeStatusByLastAppointments()
            }
        },
        changeStatusByLastAppointments(skip_system_status = [], differentSpecializations = false, is_first = true) {
            this.getPatientAppointments(this.getSuccessAppointmentFilters(skip_system_status, differentSpecializations, is_first)).then((response) => {
                if (response.length != 0) {
                    this.model.is_first = CONSTANTS.APPOINTMENT.TYPES.REPEATED;
                } else {
                    this.model.is_first = CONSTANTS.APPOINTMENT.TYPES.FIRST;
                }
            });
        },
        getPatientAppointments(filters) {
            let appointment = new AppointmentRepository();
            return appointment.fetchList(filters).then((response) => {
                return Promise.resolve(response);
            });
        },
        getPatientBaseFilters() {
            return _.onlyFilled({
                clinic_same_group: this.model.clinic_id,
                card_specialization: this.model.card_specialization_id,
                patient: this.model.patient_id,
                skip_id: this.model.id,
            });
        },
        getSuccessAppointmentFilters(skip_system_status, differentSpecializations, is_first) {
            let statuses = [
                CONSTANTS.APPOINTMENT.STATUSES.DID_NOT_COME,
                CONSTANTS.APPOINTMENT.STATUSES.DELETED
            ];
            statuses.concat(skip_system_status);
            return _.onlyFilled({
                ...this.getPatientBaseFilters(),
                skip_system_status: statuses,
                date_end: this.model.date,
                differentSpecializations: differentSpecializations,
                is_first: is_first,
            });
        },
        getIsFirstAppointmentFilters() {
            return _.onlyFilled({
                ...this.getPatientBaseFilters(),
                is_first: true,
                system_status: [
                    CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_RECEPTION,
                    CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_DOCTOR,
                    CONSTANTS.APPOINTMENT.STATUSES.COMPLETED,
                ],
            });
        },
        getIsFirstLimitMessage(specialization) {
            if (this.doctorDoesntHaveLimits()) {
                return null;
            }

            let limit = this.findSpecializationLimit(this.daySheetData.doctor.appointment_limitations);

            if (!limit) {
                return null;
            }

            if (specialization.count < limit.limitation) {
                return null;
            }

            let percent = (specialization.count / specialization.total) * 100;

            if (percent < limit.limitation_percent) {
                return null;
            }

            let is_hard = 0;
            let message = __('Внимание! процент записи первичных пациентов к врачу {percent}%', {percent: percent.toFixed(2)});
            message += __('больше допустимого процента: {percent}%.', {percent: limit.limitation_percent});

            if (limit.is_hard_limit == 1) {
                is_hard = 1;
                message = __('Жетское ограничение записи первичного пациента.') + ' ' + message;
            } else {
                message += __('<b>Все равно записать первичного пациента?</b>');
            }

            return {message, is_hard};
        },
        doctorDoesntHaveLimits() {
            return this.daySheetData.doctor.appointment_limitations.length == 0;
        },
        findSpecializationLimit(limitations) {
            return limitations.find(limitation => limitation.specialization_id == this.model.specialization_id);
        },
        getSpecializationIsFirst() {
            return this.first_patients.find(patient => patient.specialization_id == this.model.specialization_id);
        },
        getDoctorAppointmentFirstPatients() {
            let employee = new EmployeeRepository();
            let limitations = this.daySheetData.doctor.appointment_limitations.map((item) => item.id);

            let filters = {
                id: this.daySheetData.doctor.id,
                limitations
            };

            employee.getIsFirstCount(filters).then((response) => {
                this.first_patients = response;
                this.setActivePatientCard();
            });
        },
        setTotalCost() {
            let total = 0;

            if (this.model) {
                total += _.sumOf(this.model.analyses_results, 'cost');
                total += _.sumOf(this.model.regular_services, 'cost');
            }

            this.totalCost = total.toFixed(2);
        },
        cancel() {
            this.$emit('cancel');
        },
        appointmentNewAndIsFirst() {
            return this.model.isNew() && this.model.is_first == CONSTANTS.APPOINTMENT.TYPES.FIRST;
        },
        verifySpecializationFirstTotal(specialization) {
            specialization.count += 1;
            let warning = this.getIsFirstLimitMessage(specialization);
            specialization.count -= 1;

            return warning;
        },
        cardDontMatchSpecialization() {
            if (this.patientCardList.length == 0) {
                return false;
            }
            return this.model.specialization_id != this.findCardSpecialization();
        },
        hasPaidServices() {
            return this.model.services.some((service) => service.paid >= service.price);
        },
        findCardSpecialization() {
            let chosenCard = this.patientCardList.find(card => card.specialization_id == this.model.card_specialization_id);
            if (chosenCard) {
                return chosenCard.specialization_id;
            }
            return this.model.specialization_id;
        },
        statusIsActive() {
            return this.currentStatus && (this.currentStatus.system_status == CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_DOCTOR);
        },
        verifyPatientIsFirst() {
            if (this.model.is_first && this.statusIsActive()) {
                return this.getPatientAppointments(this.getIsFirstAppointmentFilters()).then((response) => {
                    if (response.length == 0) {
                        return Promise.resolve(true);
                    }
                    return Promise.resolve(false);
                });
            }
            return Promise.resolve(true);
        },
        warnCardMisMatch() {
            return this.$confirm(__('Внимание, специализация карты не совпадает со специализацией врача'),
                () => {
                    if (this.appointmentChanged) {
                        this.$error(__('Сохранение невозможно. Запись на прием была изменена другим пользователем'));
                        return;
                    }
                    return this.save();
                }
            );
        },
        warnPatientCardRequired() {
            return this.$error(__('Создайте карту пациента по специализации врача'));
        },
        warnTimeLocked() {
            return this.$error(__('Время записи пересекается с заблокированным'));
        },
        warnAnalysisDatePass() {
            this.$error(__('Сохранение невозможно. Проставьте анализам дату слачи или удалите их из записи'))
        },
        warnPatientHasFirstAppointments() {
            return this.$error(__('У пациента уже есть действующая первичная запись в отделении'));
        },
        warnDiscountCardWithPaidServices() {
            return this.$confirm(__('Добавление дисконтной карты к записи в которой есть оплаченные услуги'));
        },
        getTimeString(time) {
            if (time.length === 5) {
                time += ":00";
            }
            return time;
        },
        // TODO: temporary solution for specialization of ultrasound, refactor after the working version of the batch proposals
        checkIsUltraSoundSpecialityAndCardNull() {
            let specializations = this.daySheetData.specializations;
            // statuses when setPatientIsFirst() won't work
            let statuses = [
                this.getStatusIdBySystemStatus(this.statuses, CONSTANTS.APPOINTMENT.STATUSES.DID_NOT_COME),
                this.getStatusIdBySystemStatus(this.statuses, CONSTANTS.APPOINTMENT.STATUSES.DELETED),
                this.getStatusIdBySystemStatus(this.statuses, CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP),
            ];

            if (specializations.length > 0 && this.model.card_specialization_id) {
                let SpecializationServiceGroup = specializations.find(specialization => specialization.id === this.model.specialization_id).service_group
                if (SpecializationServiceGroup === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.ULTRASOUND && statuses.indexOf(this.model.appointment_status_id) != -1) {
                    return true
                }
            }

            return false
        },
        setLaboratoryOrderStatusForAnalyzes(items) {
            let analyzes = this.getLaboratoryAnalysisItems();
            Object.keys(items).forEach(key => {
                let status = key == 'items' ? true : false;
                items[key].forEach(analysis => {
                    let changedAnalysis = analyzes.find(item => item.id === analysis.result_id);
                    if (changedAnalysis && status) {
                        changedAnalysis.laboratory_order_item_is_postponed = false;
                        changedAnalysis.laboratory_order_item_id = 1;
                    } else if (changedAnalysis && !status) {
                        changedAnalysis.laboratory_order_item_is_postponed = true
                    }
                })
            })
            return Promise.resolve();
        },
    },
}
