<template>
    <page
        :title="__('Листы записи пациентов к врачам')"
        type="flex" >
        <template v-if="showSchedule">
            <template slot="header-addon">
                <header-buttons
                    ref="buttons"
                    :patient="patient"
                    @schedule-add-selected="addDaySheets"
                    @clear-patient="clearPatient">
                    <toggle-link v-model="displayFilter" slot="filter">
                        <svg-icon name="filter-alt" class="icon-small icon-blue">
                            {{ __('Фильтр') }}
                        </svg-icon>
                    </toggle-link>
                </header-buttons>
            </template>
            <alerts />
            <patient-line
                :patient="patient"
                @edit-patient="editPatient"
                @find-patient="findPatient"
            />
            <hr>
            <drawer :open="displayFilter">
                <section class="grey pt-10 filter">
                    <schedule-filter
                        ref="scheduleFilter"
                        :filters="filterList"
                        @apply-filters="applyFilters"
                     />
                </section>
            </drawer>
            <section class="grey-cap-schedule shrinkable">
                <div
                    class="day-sheet-appointment-content"
                    :class="{'filter-collapsed': !displayFilter}">
                    <time-block
                        :time-range="timeRange"
                        @change-sheets-date="updateParamsDate"
                    />
                    <schedule-grid
                        ref="grid"
                        :params="params"
                        :filters="filters"
                        :time-range="timeRange"
                        :patient="patient"
                        :cashier="cashier"
                        :cashboxes="cashboxes"
                        :checkbox-cashboxes="checkboxCashboxes"
                        @filter-list-changed="setFilters"
                        @params-updated="updateParams"
                        @param-updated="updateParam"
                        @grid-changed="createTimeList"
                        @remove-param="removeParam"
                        @remove-all-params="removeAllParams"
                    />
                </div>
            </section>
        </template>
        <start-screen
            v-else
            :patient="patient"
            @schedule-selected="addDaySheets" />
    </page>
</template>

<script>
import ScheduleGrid from './schedule/Grid.vue';
import ScheduleFilter from './schedule/parts/Filter.vue';
import TimeBlock from './schedule/parts/TimeBlock.vue';
import StartScreen from './schedule/parts/StartScreen.vue';
import HeaderButtons from './schedule/parts/HeaderButtons.vue';
import Alerts from './schedule/parts/Alerts.vue';
import PatientLine from './schedule/parts/PatientLine.vue';
import Patient from '@/models/patient';
import CONSTANTS from '@/constants';
import SearchPatient from '@/components/patients/search/Search.vue';
import lts from '@/services/lts';
import TogglePatientFilter from '@/components/patients/search/ToggleFilter.vue';
import GridMixin from './mixin/grid';
import Employee from '@/models/employee';
import CheckboxShiftRepository from "@/repositories/cashbox/shift";

export default {
    mixins: [
        GridMixin,
    ],
    components: {
        ScheduleGrid,
        ScheduleFilter,
        TimeBlock,
        StartScreen,
        HeaderButtons,
        Alerts,
        PatientLine,
    },
    data() {
        return {
            cashier: {},
            cashboxes: [],
            appointments: [],
            patient: {},
            filterList: {},
            filters: {},
            displayFilter: true,
            checkboxCashboxes: [],
        }
    },
    beforeMount() {
        if(this.$store.state.user.isCashier){
            this.initCashier();
            this.getCashboxes();
            this.getCheckboxes(true);
        }
    },
    created() {
        this.listenPatientSet = ({patient}) => {
            this.setActivePatient(patient);
        };
        this.listenPatientUpdate = (patient) => {
            if (this.patient.id === patient.id) {
                this.setActivePatient(patient);
            }
        }
    },
    mounted() {
        this.checkPatientInStorage();
        this.checkParamInStorage();
        this.$eventHub.$on('set-schedule-patient', this.listenPatientSet);
        this.$eventHub.$on('update-patient-data', this.listenPatientUpdate);
    },
    beforeDestroy() {
        this.$eventHub.$off('set-schedule-patient', this.listenPatientSet);
        this.$eventHub.$off('update-patient-data', this.listenPatientUpdate);
    },
    methods: {
        getCheckboxes() {
            new CheckboxShiftRepository().fetch({
                user: this.$store.state.user.employee.id,
                isActive: true,
            }, [], ['money_reciever_cashbox']).then((res) => {
                this.checkboxCashboxes = res.rows
                this.activeShift = res.rows.length !== 0 ? res.rows[0] : null
            })
        },
        setFilters(filters) {
            this.filterList = filters;
            this.$refs.scheduleFilter.filter.clinic = filters.clinics.map((clinic) => clinic.id);
        },
        findPatient() {
            this.$modalComponent(SearchPatient, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    dialog.close();
                    this.setActivePatient(patient);
                },
            }, {
                header: __('Фильтр поиска контактных лиц'),
                width: '1270px',
                headerAddon: {
                    component: TogglePatientFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        editPatient() {
            this.displayEditPatientForm(this.patient.id,
                (patient) => {
                    if (patient.id === this.patient.id) {
                        this.setActivePatient(patient);
                    }
                },
            );
        },
        clearPatient() {
            this.patient = {};
        },
        checkPatientInStorage() {
            let contact = this.$store.state.processState.currentContact;
            if (contact !== undefined && contact.type === CONSTANTS.USER.TYPE.PATIENT && contact.id !== null) {
                return this.fetchPatient(contact.id);
            }

            if (lts.appointmentStore && lts.appointmentStore.patient) {
                let patient = {...lts.appointmentStore.patient};
                if (!_.isEmpty(patient) && patient.status === CONSTANTS.USER.TYPE.PATIENT) {
                    delete lts.appointmentStore;
                    return this.fetchPatient(patient.id);
                }
            }
        },
        checkParamInStorage() {
            if (lts.appointmentStore && lts.appointmentStore.daySheet) {
                let params = {...lts.appointmentStore.daySheet};
                if (!_.isEmpty(params)) {
                    delete lts.appointmentStore;
                    return this.addDaySheets({params: [params], showAdjacent: false});
                }
            } else if (lts.scheduleParamStore) {
                let employeeStored = lts.scheduleParamStore[this.$store.state.user.employee_id];
                if (employeeStored && (employeeStored.date == this.$moment().format("DD-MM-YYYY"))) {
                    return this.addDaySheets({params: [...employeeStored.params], showAdjacent: false});
                }
            }
        },
        storeParamsInStore() {
            let stored = lts.scheduleParamStore || {};
            let employeeId = this.$store.state.user.employee_id;
            stored[employeeId] = {
                date: this.$moment().format("DD-MM-YYYY"),
                params: [...this.params],
            };
            lts.scheduleParamStore = stored;
        },
        setActivePatient(patient) {
            this.patient = patient;
        },
        fetchPatient(id) {
            let patient = new Patient({id: id});
            patient.fetch().then(() => this.setActivePatient(patient));
        },
        applyFilters(filters) {
            this.filters = filters;
        },
        initCashier() {
            this.cashier = new Employee({
                id: this.$store.state.user.employee_id,
                full_name: this.$store.state.user.full_name,
                clinic_id: this.$store.state.user.cashierClinicId,
                is_cashier: this.$store.state.user.isCashier,
            });
        },
        getCashboxes() {
            if (this.cashier === null) {
                return;
            }

            this.cashier.fetchCashboxes({cashbox_clinic: this.cashier.clinic_id, enabled_method: true}).then((response) => {
                this.cashboxes = response;
                this.paymentMethods = this.getPaymentMethods();
            });
        },
        getPaymentMethods() {
            let list = [];
            this.cashboxes.forEach((box) => {
                list.push({
                    id: box.payment_method_id,
                    value: box.payment_method.name,
                })
            });
            return list;
        },
    },
    watch: {
        params: {
            handler() {
                this.storeParamsInStore();
            },
            deep: true,
        },
    },
}
</script>
