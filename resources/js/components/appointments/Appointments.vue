<template>
    <page
        :title="__('Фильтр записей пациентов')"
        v-loading="loading"
        :element-loading-text="__('Генерация отчета...')"
        type="flex">
        <template
            slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey p-0 appointment-page-filter">
                <appointment-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <appointment-list
                v-if="displayTable"
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters" >
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$canUpdate('appointments')"
                        :disabled="activeItem === null || !$canManage('appointments.update', [activeItem.clinic_id])"
                        :text="__('Изменить запись')"
                        icon="edit"
                        @click="toggleEdit" />
                    <form-button 
                        v-if="$can('action-logs.access')"
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog" />
                    <form-button 
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportExcel(__('Записи на прием'))" />
                    <template v-if="!laptopWidth">
                        <form-button 
                            v-if="$canAccess('appointments-sheets')"
                            :disabled="activeItem === null"
                            :text="__('Задать пациента')"
                            icon="patient-contacts"
                            @click="routeScheduleWithPatient" />
                        <form-button 
                            v-if="$canUpdate('patients')"
                            :disabled="activeItem === null"
                            :text="__('Изменить пациента')"
                            icon="user-alt"
                            @click="editPatient" />
                        <form-button 
                            v-if="$can('patient-cabinet.access')"
                            :disabled="activeItem === null"
                            :text="__('Записи пациента')"
                            icon="badge"
                            @click="patientAppointments" />
                    </template>
                    <el-dropdown class="ml-10">
                        <el-button>
                            {{ __('Еще') }}
                        </el-button>
                        <el-dropdown-menu slot="dropdown">
                            <template v-if="laptopWidth">
                                <el-dropdown-item v-if="$canAccess('appointments-sheets')">
                                    <el-button
                                        type="text"
                                        :disabled="activeItem === null"
                                        @click="routeScheduleWithPatient">
                                        {{ __('Задать пациента') }}
                                    </el-button>
                                </el-dropdown-item>
                                <el-dropdown-item v-if="$canUpdate('patients')">
                                    <el-button
                                        type="text"
                                        :disabled="activeItem === null"
                                        @click="editPatient">
                                        {{ __('Изменить пациента') }}
                                    </el-button>
                                </el-dropdown-item>
                                <el-dropdown-item v-if="$can('patient-cabinet.access')">
                                    <el-button
                                        type="text"
                                        :disabled="activeItem === null"
                                        @click="patientAppointments">
                                        {{ __('Записи пациента') }}
                                    </el-button>
                                </el-dropdown-item>
                            </template>
                            <el-dropdown-item v-if="$canAccess('appointments-sheets')">
                                <el-button
                                    type="text"
                                    :disabled="activeItem === null || activeItem.is_deleted === true"
                                    @click="routeScheduleWithParam">
                                    {{ __('Открыть лист записи') }}
                                </el-button>
                            </el-dropdown-item>
                            <el-dropdown-item v-if="$can('patient-cabinet.outpatient-cards')">
                                <el-button
                                    type="text"
                                    :disabled="activeItem === null"
                                    @click="showOutPatientCard">
                                    {{ __('Открыть амбулаторную карту пациента') }}
                                </el-button>
                            </el-dropdown-item>
                            <el-dropdown-item v-if="$can('patient-cabinet.courses')">
                                <el-button
                                    type="text"
                                    :disabled="activeItem === null"
                                    @click="patientCourses">
                                    {{ __('Переход в личный кабинет пациента') }}
                                </el-button>
                            </el-dropdown-item>
                            <el-dropdown-item v-if="$canProcessCalls()">
                                <el-button
                                    type="text"
                                    :disabled="activeItem === null"
                                    @click="voipSelectContact">
                                    {{ __('Позвонить пациенту') }}
                                </el-button>
                            </el-dropdown-item>
                            <el-dropdown-item>
                                <el-button
                                    type="text"
                                    @click="refresh">
                                    {{ __('Обновить') }}
                                </el-button>
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
            </appointment-list>
        </section>
    </page>
</template>

<script>
import AppointmentManager from './mixin/manager';
import ManageMixin from '@/mixins/manage';
import AppointmentList from './appointment/List.vue';
import AppointmentFilter from './appointment/Filter.vue';
import AppointmentLog from '@/components/action-log/Appointment.vue';
import lts from '@/services/lts';
import Details from "../patients/cabinet/outpatient-cards/Details";
import PrintButton from "../patients/cabinet/outpatient-cards/print/PrintButton";
import SelectContactMixin from '@/components/call-center/mixins/select-contact';
import * as appointmentGenerator from './generators/appointments';
import AppointmentRepository from '@/repositories/appointment';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';

export default {
    mixins: [
        AppointmentManager,
        ManageMixin,
        SelectContactMixin,
        ExportXLSXMixin,
    ],
    components: {
        AppointmentList,
        AppointmentFilter,
    },
    data() {
        return {
            displayFilter: true,
            displayTable: false,
            reportRepository: new AppointmentRepository(),
            loading: false,
            fileGenerator: appointmentGenerator,
        };
    },
    computed: {
        laptopWidth() {
            return window.innerWidth <= 1366;
        }
    },
    methods: {
        getDefaultFilters() {
            let today = this.$moment().format('YYYY-MM-DD');
            return {
                clinic: this.getLoggedUserClinics(),
                dateOf: {
                    relation: 'appointment',
                    dateStart: today,
                    dateEnd: today,
                },
            };
        },
        toggleEdit() {
            this.makeDaySheetData(this.activeItem, true).then(() => {
                this.editAppointment((appointment) => {
                    this.activeItem = appointment;
                    this.daySheetData = {};
                }, this.activeItem);
            });
        },
        editPatient() {
            this.displayEditPatientForm(this.activeItem.patient_id,
                (patient) => {
                    this.activeItem.patient = patient;
                },
            );
        },
        reset() {
            this.daySheetData = {};
        },
        routeScheduleWithPatient() {
            delete lts.appointmentStore;
            lts.appointmentStore = this.getPatientParam();
            let routeData = this.$router.resolve({name: 'appointment-schedule'});
            window.open(routeData.href, '_blank');
        },
        routeScheduleWithParam() {
            delete lts.appointmentStore;
            lts.appointmentStore = this.getDaySheetParam();
            let routeData = this.$router.resolve({name: 'appointment-schedule'});
            window.open(routeData.href, '_blank');
        },
        getPatientParam() {
            return {
                patient: {
                    id: this.activeItem.patient_id,
                    status: this.activeItem.patient.status,
                },
            };
        },
        getDaySheetParam() {
            return {
                daySheet: {
                    workspace_id: this.activeItem.workspace_id,
                    date: this.activeItem.date,
                    day_sheet_owner_id: this.activeItem.doctor_id,
                    day_sheet_owner_type: this.activeItem.doctor_type,
                    clinic_id: this.activeItem.clinic_id,
                },
            };
        },
        showOutPatientCard(){
            let card = this.activeItem.patient.getCard(this.activeItem.clinic_id,  this.activeItem.card_specialization_id );

            this.$modalComponent(Details, {
                appointments: [this.activeItem],
                specialization: this.activeItem.card_specialization_id,
                card: card.id,
                clinic_id: this.activeItem.clinic_id,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Амбулаторная карта'),
                width: '890px',
                customClass: 'no-footer scrollable',
                headerAddon: {
                    component: PrintButton,
                    eventListeners: {
                        print: (dialog) => {
                            dialog.getTopComponent().print(this.getCardHeader(row));
                        }
                    }
                },
            });
        },
        showLog() {
            this.$modalComponent(AppointmentLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения записи'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        patientAppointments() {
            let routeData = this.$router.resolve({name: 'patient-cabinet-calls', params: {patientId: this.activeItem.patient_id}});
            window.open(routeData.href, '_blank');
        },
        patientCourses() {
            let routeData = this.$router.resolve({name: 'patient-cabinet-courses', params: {patientId: this.activeItem.patient_id}});
            window.open(routeData.href, '_blank');
        },
        voipSelectContact() {
            this.selectPatientContact(this.activeItem.patient);
        },
        clearFiltersAndHideTable() {
            this.displayTable = false;
            this.clearFilters();
        },
        changeFiltersAndShowTable(filters) {
            this.changeFilters(filters);
            this.displayTable = true;
        },
    },
}
</script>


