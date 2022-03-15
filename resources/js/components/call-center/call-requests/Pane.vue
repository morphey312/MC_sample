<template>
    <div class="call-requests-pane">
        <section class="grey filter">
            <call-request-filter
                ref="filter"
                :start-collapsed="!displayFilter"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <section class="grey-cap">
            <sticky-footer>
                <call-request-list
                    ref="table"
                    :filters="filters"
                    v-loading="loading"
                    @selection-changed="setActiveItem"
                    @header-filter-updated="syncFilters" />
                <div slot="footer">
                    <el-button
                        v-if="$canCreate('call-requests')"
                        @click="create">
                        {{ __('Добавить заявку') }}
                    </el-button>
                    <el-button
                        v-if="$canUpdate('call-requests')"
                        :disabled="activeItem === null || !$canManage('call-requests.update', [activeItem.clinic_id])"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$canProcessCalls()"
                        :disabled="activeItem === null || !activeItem.patient"
                        @click="selectPatientContact(activeItem.patient)">
                        {{ __('Задать пациента для звонка') }}
                    </el-button>
                    <template v-if="$canUpdate('call-requests')">
                        <el-button
                            v-if="activeItem === null || !isCancelled(activeItem)"
                            :disabled="activeItem === null || !$canManage('call-requests.update', [activeItem.clinic_id])"
                            @click="cancelCallRequest">
                            {{ __('Отменить заявку') }}
                        </el-button>
                        <el-button
                            v-else
                            :disabled="activeItem === null || !$canManage('call-requests.update', [activeItem.clinic_id])"
                            @click="restoreCallRequest">
                            {{ __('Восстановить заявку') }}
                        </el-button>
                    </template>
                    <el-button
                        v-if="$can('action-logs.access')"
                        @click="showLog"
                        :disabled="activeItem === null">
                        {{ __('Операции') }}
                    </el-button>
                    <el-dropdown
                        v-if="$canAccess('appointments') || $canUpdate('patients') || $canUpdate('calls')"
                        class="ml-10"
                        @command="callMethod">
                        <el-button >
                            {{ __('Еще') }}
                        </el-button>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item
                                v-if="$canAccess('appointments')"
                                :disabled="activeItem === null || !activeItem.patient"
                                command="showAppointments">
                                {{ __('Показать записи пациента') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="$canAccess('appointments')"
                                :disabled="activeItem === null || !activeItem.appointment"
                                command="openDaySheet">
                                {{ __('Открыть лист записи') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="$canUpdate('patients')"
                                :disabled="activeItem === null || !activeItem.patient"
                                command="editPatient">
                                {{ __('Изменить пациента') }}
                            </el-dropdown-item>
                            <el-dropdown-item
                                v-if="$canUpdate('calls')"
                                :disabled="activeItem === null || !activeItem.call"
                                command="editCall">
                                {{ __('Изменить звонок') }}
                            </el-dropdown-item>
                            <el-dropdown-item v-if="$canAccess('patient-cabinet')">
                                <el-button
                                    type="text"
                                    :disabled="activeItem === null"
                                    @click="goToPatientCabinet">
                                    {{ __('Перейти в личный кабинет') }}
                                </el-button>
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                    <form-button
                        :text="__('Экспорт в excel')"
                        icon="download"
                        @click="exportExcel(__('Заявки на прозвон'))" />
                </div>
            </sticky-footer>
        </section>
    </div>
</template>

<script>
import CallRequestFilter from './Filter.vue';
import CallRequestList from './List.vue';
import ManageMixin from '@/mixins/manage';
import Call from '@/models/call';
import FormCreate from './Create.vue';
import FormEdit from './Edit.vue';
import CallEdit from '../calls-appointments/calls/FormEdit.vue';
import FormCancel from './Cancel.vue';
import SelectContactMixin from '../mixins/select-contact';
import CONSTANT from '@/constants';
import lts from '@/services/lts';
import CallRequestLog from '@/components/action-log/CallRequest.vue';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import CallRequestRepository from '@/repositories/call-request';
import * as callRequestsGenerator from '@/components/call-center/calls/generators/call-requests';

export default {
    mixins: [
        ManageMixin,
        SelectContactMixin,
        ExportXLSXMixin
    ],
    components: {
        CallRequestFilter,
        CallRequestList,
    },
    data() {
        return {
            reportRepository: new CallRequestRepository(),
            fileGenerator: callRequestsGenerator,
            loading: false
        }
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить заявку на прозвон'),
                editHeader: __('Изменить заявку на прозвон'),
                width: '800px',
            };
        },
        getMessages() {
            return {
                created: __('Заявка на прозвон успешно создана'),
                updated: __('Заявка на прозвон успешно обновлена'),
            };
        },
        getDefaultFilters() {
            return {
                appointment_date_start: this.$moment().add(1, 'd').format('YYYY-MM-DD'),
                appointment_date_end: this.$moment().add(1, 'd').format('YYYY-MM-DD'),
            };
        },
        getFilterUid() {
            return 'call-center-call-request';
        },
        goToPatientCabinet() {
            let routeData = this.$router.resolve({name: 'patient-cabinet', params: {patientId: this.activeItem.patient.id}});
            window.open(routeData.href, '_blank');
        },
        cancelCallRequest() {
            this.$modalComponent(FormCancel, {
                item: this.activeItem
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                canceled: (dialog) => {
                    this.$info(__('Заявка на прозвон успешно отменена'));
                    dialog.close();
                    this.refresh();
                },
            }, {
                header: __('Отмена заявки'),
                width: '400px',
            });
        },
        restoreCallRequest() {
            this.$confirm(__('Вы уверены, что хотите восстановить данную заявку на прозвон?'), () => {
                var copy = this.activeItem.clone();
                copy.comment_canceled = null;
                copy.status = copy.original_status || CONSTANT.CALL_REQUEST.STATUS.MADE;
                copy.save().then((response) => {
                    this.$info(__('Заявка на прозвон успешно восстановлена'));
                    this.refresh();
                }).catch((e) => {
                    this.$error(__('Не удалось восстановить заявку'));
                });
            });
        },
        isCancelled(item) {
            return item.status === CONSTANT.CALL_REQUEST.STATUS.CANCELLED;
        },
        editPatient() {
            this.displayEditPatientForm(this.activeItem.patient.id, (patient) => {
                this.activeItem.patient = patient;
            });
        },
        editCall() {
            this.$modalComponent(CallEdit, {
                item: this.activeItem.castToInstance(Call, this.activeItem.call)
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, call) => {
                    this.$info(__('Звонок был успешно обновлен'));
                    dialog.close();
                    this.activeItem.call = call;
                },
            }, {
                header: __('Изменить звонок'),
                width: '1200px',
            });
        },
        openDaySheet() {
            delete lts.appointmentStore;
            lts.appointmentStore = this.getDaySheetParam(this.activeItem.appointment);
            let routeData = this.$router.resolve({name: 'appointment-schedule'});
            window.open(routeData.href, '_blank');
        },
        getDaySheetParam(appointment) {
            return {
                daySheet: {
                    workspace_id: appointment.workspace_id,
                    date: appointment.date,
                    day_sheet_owner_id: appointment.doctor_id,
                    day_sheet_owner_type: appointment.doctor_type,
                    clinic_id: appointment.clinic_id,
                },
            };
        },
        showAppointments() {
            this.$emit('show-appointments', this.activeItem.patient);
        },
        callMethod(method) {
            this[method]();
        },
        showLog() {
            this.$modalComponent(CallRequestLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения заявки'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
}
</script>
