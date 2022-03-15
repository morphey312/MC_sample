<template>
    <div class="draggable-cell-info"
        :style="styles"
        @mouseover="hoverIn"
        @mouseleave="hoverOut"
        @contextmenu="showContextMenu"
        @dblclick="onDblClick"
        v-popover:popover >
        <div class="dots"></div>
        <div
            v-if="$isMobileNavigator"
            class="contextmenu-arrow"
            :style="{top: mobNavigatorTop}"
            @click="showContextMenu">
            <i class="el-icon-caret-right"></i>
        </div>
        <p class="info-time">{{ appointmentPeriod }}. {{ (cardNumber.length > 0) ? cardNumber + ". " : "" }} {{ patientName }}</p>
        <p>{{ (item.status ? item.status.name : '') }}</p>
        <p>{{ isFirst }}</p>
        <p v-if="services.length > 0">{{ services }}</p>
        <p v-if="analyses" :style="{color: analyses.color}">{{ analyses.status }}</p>
        <p v-if="medInsurance.length > 0">{{ medInsurance }}</p>
        <p>{{ item.comment }}</p>
        <p v-if="item.patient || item.patient.source_name"
            v-show="item.patient.source_show_in_appointment">
            <b>{{ item.patient.source_name || '' }}</b>
        </p>
        <p v-if="surgeryEmployees.length > 0">{{ surgeryEmployees }}</p>
        <el-popover
            ref="popover"
            placement="right-start"
            width="200"
            v-model="contextMenuVisible"
            popper-class="schedule-popover"
            trigger="manual" >
            <slot>
                <context-menu
                    ref="contextMenu"
                    :appointment="item"
                    :visible="contextMenuVisible"
                    :readonly="readonly"
                    :is-questionnaire-filled="isQuestionnaireFilled"
                    :is-operational="isOperational"
                    :appointment-statuses="appointmentStatuses"
                    @close="closePopover"
                    @edit="editAppointment"
                    @delete="deleteAppointment"
                    @copy="copyAppointment"
                    @cut="cutAppointment"
                    @set-schedule-patient="setPatient"
                    @edit-appointment-patient="editPatient"
                    @show-patient-payments="getCreatePaymentForm"
                    @call-patient="callPatient"
                    @add-call-request="manageCallRequest"
                    @delete-call-request="deleteCallRequest"
                    @go-patient-cabinet="goPatientCabinet"
                    @go-doctor-cabinet="goDoctorCabinet"
                    @set-sms-reminder="setSmsReminder"
                    @show-appointment-log="appointmentLog"
                    @fill-patient-questionnaire="showPatientQuestionnaire"
                    @set-surgery-employees="setSurgeryEmployees"
                />
            </slot>
        </el-popover>
        <tags
            :tags="tags"
            :total-cells="item.totalCells"
            :hovered="isHovered"
            :patient="item.patient" />
    </div>
</template>

<script>
import ContextMenu from './parts/ContextMenu';
import Tags from './parts/Tags.vue';
import PatientLogButton from './parts/PatientLogButton.vue';
import AppointmentLog from '@/components/action-log/Appointment.vue';
import PatientLog from '@/components/action-log/Patient.vue';
import CreateSmsReminderModal from '@/components/call-center/sms-reminders/modal/Create';
import CallRequestModalMixin from '@/components/call-center/call-requests/schedule/mixins/call-request-modal';
import StatusMixin from '@/components/appointments/mixin/status';
import PatientQuestionnaire from '@/components/patients/questionnaire/Blank';
import { on, off } from 'element-ui/src/utils/dom';
import CONSTANTS from '@/constants';
import SelectContactMixin from '@/components/call-center/mixins/select-contact';
import CreatePayment from '@/components/cashier/payments/form/CreatePayment.vue';
import CreatePaymentHeaderAddon from '@/components/cashier/payments/HeaderAddon.vue';
import SurgeryEmployees from '@/components/appointments/schedule/modals/SurgeryEmployees.vue';

export default {
    mixins: [
        CallRequestModalMixin,
        StatusMixin,
        SelectContactMixin,
    ],
    components: {
        ContextMenu,
        Tags,
    },
    props: {
        item: {
            type: Object,
        },
        styles: {
            type: Object,
            default: () => ({})
        },
        readonly: {
            type: Boolean,
            default: false,
        },
        inactiveStatuses: {
            type: Array,
            default: [],
        },
        cashier: Object,
        cashboxes: {
            type: Array,
            default: () => [],
        },
        paymentMethods: {
            type: Array,
            default: () => [],
        },
        height: {
            type: [String, Number],
        },
        owner: {
            type: Object,
            default: () => ({})
        },
        appointmentStatuses: {
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
            contextMenuVisible: false,
            isFirstList: this.$handbook.getOptions('patient_appointment_is_first'),
            tagClass: {
                is_first: 'green',
                is_skk: 'red',
                black_mark: 'grey',
                med_insurance: 'blue',
                attention: 'red-alt',
                do_not_take_payment: 'deep-blue-alt',
                is_online: 'orange',
            },
            isQuestionnaireFilled: false,
            isHovered: false,
            attention: false,
            isCashier: this.$store.state.user.isCashier,
        }
    },
    created() {
        this.updateTimersListener = () => {
            this.updateTimers();
        };
        this.documentClickHandler = (e) => {
            if (this.$refs.popover) {
                this.$refs.popover.handleDocumentClick(e);
            }
        };
    },
    mounted() {
        this.$ticker.on(this.updateTimersListener, 3);
        on(document, 'click', this.documentClickHandler);
        on(document, 'contextmenu', this.documentClickHandler);
    },
    beforeDestroy() {
        this.$ticker.off(this.updateTimersListener);
        off(document, 'click', this.documentClickHandler);
        off(document, 'contextmenu', this.documentClickHandler);
    },
    computed: {
        appointmentPeriod() {
            return this.item.momented.scheduleStart + ' - ' +  this.item.momented.scheduleEnd
        },
        patientName() {
            return this.item.patient.full_name;
        },
        cardNumber() {
            return this.item.card_number || '';
        },
        services() {
            let serviceList = [];

            if (this.item.services.length != 0) {
                this.item.services.forEach((service) => {
                    if (_.isVoid(service.container_type)) {
                        serviceList.push(service.name);
                    }
                })
            }
            return serviceList.join(', ');
        },
        analyses() {
            let statuses = this.getStatuses(this.$handbook.getOptions('analysis_filter_status'))
            let analysis = this.getAnalysisItems();
            let analysisLength = analysis.length;
            let status;

            if (analysisLength === 0)
                return

            if (this.filterAnalysisLength(analysis, "date_pass") === analysisLength){
                status = ({status: statuses.date_pass, color: CONSTANTS.COLORS.ANALYSIS.PASSED});
                if (this.filterAnalysisLength(analysis, "date_ready") === analysisLength) {
                    status = ({status: statuses.date_ready, color: CONSTANTS.COLORS.ANALYSIS.READY});
                    if (this.filterAnalysisLength(analysis, "date_sent_email") === analysisLength) {
                        status = ({status: statuses.date_sent_email, color: CONSTANTS.COLORS.ANALYSIS.EMAIL_SENT});
                    }
                }
            }
            return status
        },
        medInsurance() {
            let insurance = {
                yes: __('Страховка есть'),
                no: __('Страховки нет'),
            };
            return insurance[this.item.patient.med_insurance]
                    ? `${insurance[this.item.patient.med_insurance]}`
                    : '';
        },
        isFirst() {
            return _.find(this.isFirstList, {id: Number(this.item.is_first)}).value;
        },
        hasOnlineServices() {
            return this.item.services.some((service) => service.is_online);
        },
        tags() {
            let list = [];
            let patient = this.item.patient;

            if (this.item.is_first) {
                list.push(this.tagClass.is_first);
            }

            if (this.item.do_not_take_payment) {
                list.push(this.tagClass.do_not_take_payment);
            }

            if (patient.is_attention) {
                list.push(this.tagClass.attention);
            }

            if (patient.is_skk) {
                list.push(this.tagClass.is_skk);
            }

            if (patient.black_mark) {
                list.push(this.tagClass.black_mark);
            }

            if (patient.med_insurance == CONSTANTS.PATIENT.MED_INSURANCE.HAS_INSURANCE) {
                list.push(this.tagClass.med_insurance);
            }

            if (this.hasOnlineServices) {
                list.push(this.tagClass.is_online);
            }

            return list;
        },
        mobNavigatorTop() {
            return Number(this.height) - CONSTANTS.SCHEDULE_CELL_HEIGHT + 'px';
        },
        isOperational() {
            return this.item.doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE
                && (this.owner.is_operational || this.owner.is_hospital_room);
        },
        surgeryEmployees() {
            return (this.item.surgery_employee_names && this.item.surgery_employee_names.length != 0)
                ? this.item.surgery_employee_names.join(', ')
                : '';
        },
    },
    methods: {
        getStatuses(options) {
            let statuses = {}

            options.map(item =>
                statuses[item.id] = item.value
            )

            return statuses
        },
        filterAnalysisLength(analysis, column) {
            return analysis.filter((item) => {
                return _.isFilled(item[column]);
            }).length
        },
        getAnalysisItems() {
            let items = [];

            let analysisContainer = this.item.services.filter((service) => {
                return service.container_type === CONSTANTS.CARD_ASSIGNMENT.TYPES.ANALYSIS_RESULTS;
            })

            analysisContainer.map((analysis) => {
                if (_.isFilled(analysis['items'])) {
                    analysis['items'].map(item =>
                        items.push(item)
                    )
                } else {
                    items.push(analysis)
                }
            })

            return items;
        },
        closePopover() {
            this.contextMenuVisible = false;
        },
        onDblClick() {
            if (this.readonly) {
                this.$eventHub.$emit('appointment-selected', this.item);
            } else if (this.$canManage('appointments.update', [this.item.clinic_id])) {
                this.editAppointment();
            }
        },
        updateTimers() {
            this.attention = false;
            if (this.isAppointmentDelayed()) {
                if (this.$moment().isAfter(this.item.momented.start)) {
                    this.attention = true;
                }
            }

            this.emitAttention();
        },
        isAppointmentDelayed() {
            return this.item.appointment_status_id == this.getStatusIdBySystemStatus([this.item.status], CONSTANTS.APPOINTMENT.STATUSES.SIGNED_UP) ||
                   this.item.appointment_status_id == this.getStatusIdBySystemStatus([this.item.status], CONSTANTS.APPOINTMENT.STATUSES.CAME_TO_RECEPTION)
        },
        emitAttention() {
            this.$emit('set-attention', this.attention);
        },
        showContextMenu(e) {
            e.preventDefault();
            if (this.$refs.popover) {
                this.$refs.popover.doShow();
            }
        },
        hoverIn() {
            this.isHovered = true;
            this.$emit('hover-in', {height: this.$el.clientHeight});
        },
        hoverOut() {
            this.isHovered = false;
            this.$emit('hover-out');
        },
        editAppointment() {
            this.contextMenuVisible = false;
            this.$emit('edit');
        },
        deleteAppointment() {
            this.contextMenuVisible = false;
            this.$emit('delete');
        },
        copyAppointment() {
            return this.addToClipboard();
        },
        cutAppointment() {
            if (this.inactiveStatuses.indexOf(this.item.appointment_status_id) === -1) {
                return this.$error(__('Измените статус записи для переноса'));
            }
            return this.addToClipboard('cut');
        },
        addToClipboard(action = 'copy') {
            this.$eventHub.$emit('clipboard-cleared');
            this.$store.commit('copyToClipboard', {
                type: 'appointment',
                action: action,
                data: this.item,
            });
            this.$emit('added-to-clipboard');
            this.contextMenuVisible = false;
        },
        setPatient() {
            this.contextMenuVisible = false;
            this.$eventHub.$emit('set-schedule-patient', {patient: this.item.patient});
        },
        editPatient() {
            this.contextMenuVisible = false;
            this.displayEditPatientForm(this.item.patient.id,
                (patient) => {
                    this.item.patient = patient;
                    this.$eventHub.$emit('appointment-patient-updated', {patient});
                }
            );
        },
        manageCallRequest() {
            return _.isNil(this.item.call_request)
                   ? this.createCallRequest()
                   : this.editCallRequest();
        },
        createCallRequest() {
            let maxToRecall = this.$moment(this.item.date).subtract(1, 'days').format('YYYY-MM-DD');

            let data = {
                appointment_id: this.item.id,
                clinic_id: this.item.clinic_id,
                patient_id: this.item.patient.id,
                specialization_id: this.item.clinic_id,
                specialization_id: this.item.clinic_id,
                doctor_id: this.item.doctor_id,
                doctor_type: this.item.doctor_type,
                recall_from: maxToRecall,
                recall_to: maxToRecall,
                appointment_date: this.item.date,
            }

            this.displayCreateCallRequestForm(data, (call_request) => {
                this.item.call_request = call_request;
            });
        },
        editCallRequest() {
            let data = {};

            if (_.isFunction(this.item.call_request.isNew)) {
                data = {...this.item.call_request._attributes};
            } else {
                data = {...this.item.call_request};
            }

            data.appointment_date = this.item.date;
            data.appointment_id = this.item.id;

            this.displayEditCallRequestForm(data, (call_request) => {
                this.item.call_request = call_request;
            });
        },
        deleteCallRequest() {
            if(_.isEmpty(this.item.call_request)){
                return this.$info(__('Нет необработанной заявки на прозвон'));
            }

            this.displayDeleteCallRequestForm(this.item.call_request, () => {
                this.item.call_request = null;
            });
        },
        goPatientCabinet() {
            let routeName = this.$store.state.user.isDoctor ? 'patient-cabinet-outpatient' : 'patient-cabinet';
            let routeData = this.$router.resolve({name: routeName, params: {patientId: this.item.patient.id}});
            window.open(routeData.href, '_blank');
        },
        goDoctorCabinet() {
            let routeData = this.$router.resolve({name: 'doctor-appointment', params: {appointmentId: this.item.id}});
            window.open(routeData.href, '_blank');
        },
        setSmsReminder(){
            this.$modalComponent(CreateSmsReminderModal, {
                appointment: this.item,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Создать SMS напоминанние'),
                width: '400px',
                customClass: 'no-footer',
            });

        },
        appointmentLog() {
            this.$modalComponent(AppointmentLog, {
                id: this.item.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения записи'),
                width: '900px',
                customClass: 'no-footer',
                headerAddon: {
                    component: PatientLogButton,
                    eventListeners: {
                        patientLog: (dialog) => {
                            this.patientLog();
                        },
                    },
                }
            });
        },
        showPatientQuestionnaire(){
            this.$modalComponent(PatientQuestionnaire, {
                patientId: this.item.patient_id,
                clinicId: this.item.clinic_id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
                saved: (dialog) => {
                    this.isQuestionnaireFilled = true;
                }
            }, {
                header: __('Анкета пациента'),
                width: '1000px',
                customClass: 'no-footer',
            });
        },
        getCreatePaymentForm() {
            this.$modalComponent(CreatePayment, {
                patient:this.item.patient,
                cashier: this.cashier,
                cashboxes: this.cashboxes,
                checkboxCashboxes: this.checkboxCashboxes,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Добавить платеж пациента:') + ' ' + this.item.patient.full_name,
                width: '1270px',
                headerAddon: {
                    component: CreatePaymentHeaderAddon,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                        addDeposit: (dialog) => {
                            dialog.getTopComponent().addDeposit();
                        },
                        addPrepayment: (dialog) => {
                            dialog.getTopComponent().addPrepayment();
                        },
                    },
                },
            });
        },
        patientLog() {
            this.$modalComponent(PatientLog, {
                id: this.item.patient_id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения данных пациента'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        callPatient() {
            this.selectPatientContact(this.item.patient);
            this.$router.push({name: 'voip'});
        },
        setSurgeryEmployees() {
            this.$modalComponent(SurgeryEmployees, {
                appointment: this.item,
                owner: this.owner,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Добавить врачей к записи'),
                width: '900px',
            });
        },
    }
}
</script>
