import Vue from 'vue';
import VueRouter from 'vue-router';
import store from '@/store';

Vue.use(VueRouter);

const router = new VueRouter({
    routes: [
        {
            path: '/',
            component: require('@/components/App.vue'),
            name: 'main',
            meta: {guarded: true},
            children: [
                {
                    component: require('@/components/appointments/Appointments.vue'),
                    path: 'appointments',
                    name: 'appointments',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/call-center/calls/Calls.vue'),
                    path: 'calls',
                    name: 'calls',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/appointments/Schedule.vue'),
                    path: 'appointment-schedule',
                    name: 'appointment-schedule',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/appointments/LockLog.vue'),
                    path: 'lock-log',
                    name: 'lock-log',
                    meta: {guarded: true},
                },
                {
                    path: 'resources',
                    redirect: { name: 'clinics' },
                    component: require('@/components/resources/Resources.vue'),
                    children: [
                        {
                            component: require('@/components/resources/Occupation.vue'),
                            path: 'occupations',
                            name: 'occupations',
                        },
                        {
                            component: require('@/components/resources/Clinics.vue'),
                            path: 'clinics',
                            name: 'clinics',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/resources/Specializations.vue'),
                            path: 'specializations',
                            name: 'specializations',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/resources/Employees.vue'),
                            path: 'employees',
                            name: 'employees',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/resources/employee/positions/Positions.vue'),
                            path: 'positions',
                            name: 'positions',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/resources/Workspaces.vue'),
                            path: 'workspaces',
                            name: 'workspaces',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/resources/employee/roles/Roles.vue'),
                            path: 'roles',
                            name: 'roles',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/resources/DaySheets.vue'),
                            path: 'day_sheets',
                            name: 'day-sheets',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/resources/day-sheet/calendar/Schedule.vue'),
                            path: 'day_sheets/schedule/:owner_type/:id/',
                            name: 'day-sheet-schedule',
                            meta: {guarded: true},
                        },
                        {
                            path: 'clinic-bonus-norms',
                            name: 'clinic-bonus-norms',
                            component: require('@/components/resources/ClinicBonusNorms.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'operator-bonus-norms',
                            name: 'operator-bonus-norms',
                            component: require('@/components/resources/OperatorBonusNorms.vue'),
                            meta: {guarded: true},
                        },
                    ]
                },
                {
                    path: 'call-center',
                    name: 'call-center',
                    redirect: { name: 'voip' },
                    component: require('@/components/call-center/CallCenter.vue'),
                    meta: {guarded: true},
                    children: [
                        {
                            path: 'voip',
                            name: 'voip',
                            component: require('@/components/call-center/VoIP.vue'),
                            meta: {guarded: true},
                        },
                    ]
                },
                {
                    path: 'patients',
                    name: 'patients',
                    component: require('@/components/resources/Patients.vue'),
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/duplicates/Patients.vue'),
                    path: 'patients/duplicates',
                    name: 'patients-duplicates',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/patients/IssuedMedicines.vue'),
                    path: 'patients/issued-medicines',
                    name: 'patients-issued-medicines',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/patients/PersonalCabinet.vue'),
                    path: 'patients/registrations',
                    name: 'patients-registrations',
                    meta: {guarded: true},
                },
                {
                    path: 'patient/:patientId',
                    name: 'patient-cabinet',
                    redirect: 'patient/:patientId/info',
                    component: require('@/components/patients/Cabinet.vue'),
                    meta: {guarded: true},
                    children: [
                        {
                            path: 'info',
                            name: 'patient-cabinet-info',
                            component: require('@/components/patients/cabinet/Info.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'history',
                            name: 'patient-cabinet-history',
                            component: require('@/components/patients/cabinet/History.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'calls-appointments',
                            name: 'patient-cabinet-calls',
                            component: require('@/components/patients/cabinet/CallsAppointments.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'outpatient-cards',
                            name: 'patient-cabinet-outpatient',
                            component: require('@/components/patients/cabinet/OutpatientCards.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'protocols',
                            name: 'patient-cabinet-protocols',
                            component: require('@/components/patients/cabinet/Protocols.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'analyses-results',
                            name: 'patient-cabinet-analyses',
                            component: require('@/components/patients/cabinet/AnalysesResults.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'treatment-courses',
                            name: 'patient-cabinet-courses',
                            component: require('@/components/patients/cabinet/TreatmentCourses.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'medicines',
                            name: 'patient-cabinet-medicines',
                            component: require('@/components/patients/cabinet/IssuedMedicines.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'payments',
                            name: 'patient-cabinet-payments',
                            component: require('@/components/patients/cabinet/Payments.vue'),
                            meta: {guarded: true},
                        },
                        {
                            path: 'documents',
                            name: 'patient-cabinet-documents',
                            component: require('@/components/patients/cabinet/Documents.vue'),
                            meta: {guarded: true},
                        },
                    ],
                },
                {
                    component: require('@/components/patients/cards/Cards.vue'),
                    path: 'patients/:id/cards',
                    name: 'patient-cards',
                    meta: {guarded: true},
                },
                {
                    path: 'analysis-results',
                    name: 'analysis-results',
                    component: require('@/components/patients/AnalysisResults.vue'),
                    meta: {guarded: true},
                },
                {
                    path: 'handbook',
                    redirect: { name: 'patient-sources' },
                    component: require('@/components/handbook/Handbook.vue'),
                    children: [
                        {
                            component: require('@/components/handbook/PatientSources.vue'),
                            path: 'patient-sources',
                            name: 'patient-sources',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/BlackMarkReasons.vue'),
                            path: 'black-mark-reasons',
                            name: 'black-mark-reasons',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/SkkReasons.vue'),
                            path: 'skk-reasons',
                            name: 'skk-reasons',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/ReasonsImpossibilityCallProcessing.vue'),
                            path: 'reason-impossibility-call-processing',
                            name: 'reason-impossibility-call-processing',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/CallDeleteReasons.vue'),
                            path: 'call-delete-reasons',
                            name: 'call-delete-reasons',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/CallResults.vue'),
                            path: 'call-results',
                            name: 'call-results',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/CallRequestPurposes.vue'),
                            path: 'call-request-purposes',
                            name: 'call-request-purposes',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/AppointmentDeleteReasons.vue'),
                            path: 'appointment-delete-reasons',
                            name: 'appointment-delete-reasons',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/AppointmentStatuses.vue'),
                            path: 'appointment-statuses',
                            name: 'appointment-statuses',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/Cities.vue'),
                            path: 'cities',
                            name: 'cities',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/Countries.vue'),
                            path: 'countries',
                            name: 'countries',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/DaySheetBlockReason.vue'),
                            path: 'day-sheet-time-block-reason',
                            name: 'day-sheet-time-block-reason',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/laboratories-schedule/calendar/Schedule.vue'),
                            path: 'laboratories-schedule',
                            name: 'laboratories-schedule',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/Laboratories.vue'),
                            path: 'laboratories',
                            name: 'laboratories',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/AppointmentLimitations.vue'),
                            path: 'appointment-limitations',
                            name: 'appointment-limitations',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/Currencies.vue'),
                            path: 'currencies',
                            name: 'currencies',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/ReasonRefusingTreatment.vue'),
                            path: 'reason-refusing-treatments',
                            name: 'reason-refusing-treatments',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/Blanks.vue'),
                            path: 'blanks',
                            name: 'blanks',
                            meta: {guarded: true},
                        },
                        {
                            component: require('@/components/handbook/WaitListRecordCancelReasons.vue'),
                            path: 'wait-list-record-cancel-reasons',
                            name: 'wait-list-record-cancel-reasons',
                            meta: {guarded: true},
                        },
                    ],
                },
                {
                    component: require('@/components/laboratories/AnalysesCandidate.vue'),
                    path: 'analyses-candidate',
                    name: 'analyses-candidate',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/doctor/Schedule.vue'),
                    path: 'doctor',
                    name: 'doctor-schedule',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/doctor/Appointment.vue'),
                    path: 'doctor/appointment/:appointmentId',
                    name: 'doctor-appointment',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/price-list/Services.vue'),
                    path: 'services/price-list',
                    name: 'price-list-services',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/duplicates/Services.vue'),
                    path: 'services/duplicates',
                    name: 'services-duplicates',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/price-list/upload/Services.vue'),
                    path: 'services/price-list/upload',
                    name: 'price-list-services-upload',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/price-list/Analyses.vue'),
                    path: 'analyses/price-list',
                    name: 'price-list-analyses',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/duplicates/Analyses.vue'),
                    path: 'analyses/duplicates',
                    name: 'analyses-duplicates',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/price-list/upload/Analyses.vue'),
                    path: 'analyses/price-list/upload',
                    name: 'price-list-analyses-upload',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/treatment/Analyses.vue'),
                    path: 'analyses',
                    name: 'analyses',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/agreement-of-prices/AgreementOfPrices.vue'),
                    path: 'agreement-of-prices',
                    name: 'agreement-of-prices',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/treatment/analyses/Prices.vue'),
                    path: 'analyses/:id/prices',
                    name: 'analyses-prices',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/treatment/Services.vue'),
                    path: 'services',
                    name: 'services',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/treatment/services/Prices.vue'),
                    path: 'services/:id/prices',
                    name: 'services-prices',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/treatment/services/PaymentDestination.vue'),
                    path: 'services/payment-destinations',
                    name: 'service-payment-destinations',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/treatment/PaymentMethods.vue'),
                    path: 'payment-methods',
                    name: 'payment-methods',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/treatment/Diagnoses.vue'),
                    path: 'diagnoses',
                    name: 'diagnoses',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/discount-cards/NumberingKinds.vue'),
                    path: 'card-numbering-kinds',
                    name: 'card-numbering-kinds',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/discount-cards/CardTypes.vue'),
                    path: 'discount-card-types',
                    name: 'discount-card-types',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/discount-cards/Icons.vue'),
                    path: 'card-type-icons',
                    name: 'card-type-icons',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/price-list/Medicines.vue'),
                    path: 'medicines/price-list',
                    name: 'price-list-medicines',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/cashier/PaymentList.vue'),
                    path: 'list-cashier',
                    name: 'list-cashier',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/cashier/Cashier.vue'),
                    path: 'cashier',
                    name: 'cashier',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/cashier/OnlineRefund.vue'),
                    path: 'refund-online-list',
                    name: 'refund-online-list',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/cashier/DoctorCashier.vue'),
                    path: 'doctor-list-cashier',
                    name: 'doctor-list-cashier',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/reports/CallCenterReport.vue'),
                    path: 'reports/call-center',
                    name: 'call-center-report',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/reports/RegistryReport.vue'),
                    path: 'reports/registry',
                    name: 'registry-report',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/reports/FinanceReport.vue'),
                    path: 'reports/finance',
                    name: 'finance-report',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/reports/MarketingReport.vue'),
                    path: 'reports/marketing',
                    name: 'marketing-report',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/reports/IncomeReport.vue'),
                    path: 'reports/income',
                    name: 'income-report',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/reports/interactive/IncomeReport.vue'),
                    path: 'reports/income-v2',
                    name: 'income-report-v2',
                },
                {
                    component: require('@/components/reports/interactive/RedirectsReport.vue'),
                    path: 'reports/redirects-v2',
                    name: 'redirects-report-v2',
                },
                {
                    component: require('@/components/reports/interactive/RedirectsReportV3.vue'),
                    path: 'reports/redirects-v3',
                    name: 'redirects-report-v3',
                },
                {
                    component: require('@/components/reports/DoctorSpecializationReport.vue'),
                    path: 'reports/doctor-specialization',
                    name: 'doctor-specialization',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/reports/interactive/DoctorSpecializationReport.vue'),
                    path: 'reports/doctor-specialization-v2',
                    name: 'doctor-specialization-v2',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/reports/interactive/SoldServices.vue'),
                    path: 'reports/sold-services-interactive',
                    name: 'sold-services-interactive',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/reports/interactive/DoctorIncomePlanReport.vue'),
                    path: 'reports/doctor-income-plan',
                    name: 'doctor-income-plan-report',
                },
                {
                    component: require('@/components/reports/interactive/Marketing.vue'),
                    path: 'reports/marketing-v2',
                    name: 'marketing-report-v2',
                },
                {
                    component: require('@/components/reports/interactive/MarketingCities.vue'),
                    path: 'reports/marketing-cities-v2',
                    name: 'marketing-report-cities-v2',
                },
                {
                    component: require('@/components/reports/interactive/CallIncome.vue'),
                    path: 'reports/calls-income-interactive',
                    name: 'calls-income-interactive',
                },
                {
                    component: require('@/components/reports/interactive/OperatorBonuses.vue'),
                    path: 'reports/operator-bonuses-interactive',
                    name: 'operator-bonuses-interactive',
                },
                {
                    component: require('@/components/reports/interactive/CallsMissed.vue'),
                    path: 'reports/calls-missed-interactive',
                    name: 'calls-missed-interactive',
                },
                {
                    component: require('@/components/reports/interactive/CallIncomeV2.vue'),
                    path: 'reports/calls-income-interactive-v2',
                    name: 'calls-income-interactive-v2',
                },
                {
                    path: 'settings',
                    name: 'settings',
                    component: require('@/components/call-center/CallCenter.vue'),
                    redirect: { name: 'notification-settings' },
                    children: [
                        {
                            component: require('@/components/call-center/notifications/Settings.vue'),
                            path: 'notifications',
                            name: 'notification-settings',
                            meta: {guarded: true},
                        },
                    ]
                },
                {
                    component: require('@/components/insurance/Companies.vue'),
                    path: 'insurance-companies',
                    name: 'insurance-companies',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/insurance/ExecutionAct.vue'),
                    path: 'insurance-execution-acts',
                    name: 'insurance-execution-acts',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/insurance/price-list/Services.vue'),
                    path: 'insurance-services-prices',
                    name: 'insurance-services-prices',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/insurance/price-list/Analyses.vue'),
                    path: 'insurance-analyses-prices',
                    name: 'insurance-analyses-prices',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/insurance/price-list/upload/Analyses.vue'),
                    path: 'insurance/serivice-prices/upload',
                    name: 'insurance-analyses-prices-upload',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/insurance/price-list/upload/Services.vue'),
                    path: 'insurance/analyses-prices/upload',
                    name: 'insurance-services-prices-upload',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/resources/DoctorIncomePlan.vue'),
                    path: 'employee/doctor-income-plans',
                    name: 'employee-doctor-income-plans',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/legal-entities/Entities.vue'),
                    path: 'legal-entities',
                    name: 'legal-entities',
                },
                {
                    component: require('@/components/msp/Msp.vue'),
                    path: 'msp',
                    name: 'msp',
                },
                {
                    component: require('@/components/ehealth/Applications.vue'),
                    path: 'ehealth/applications',
                    name: 'ehealth-application-history',
                },
                {
                    component: require('@/components/ehealth/Bind.vue'),
                    path: 'ehealth/bind',
                    name: 'ehealth-bind',
                },
                {
                    component: require('@/components/stationare/PatientJournal.vue'),
                    path: 'stationare-patient-journal',
                    name: 'stationare-patient-journal',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/stationare/SurgeryJournal.vue'),
                    path: 'stationare-surgery-journal',
                    name: 'stationare-surgery-journal',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/cashier/ExchangeRate.vue'),
                    path: 'exchange-list',
                    name: 'exchange-list',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/appointments/DelayJournal.vue'),
                    path: 'appointment-delays-journal',
                    name: 'appointment-delays-journal',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/laboratories/LaboratoryOrders.vue'),
                    path: 'laboratory-orders',
                    name: 'laboratory-orders',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/appointments/TreatmentRefusing.vue'),
                    path: 'treatment-refusings',
                    name: 'treatment-refusings',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/call-center/wait-list-records/WaitlistRecord.vue'),
                    path: 'wait-list-records',
                    name: 'wait-list-records',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/appointments/VideoConsultations.vue'),
                    path: 'videoconsultations-log',
                    name: 'videoconsultations-log',
                    meta: {guarded: true},
                },
                {
                    component: require('@/components/appointments/AmbulanceCalls.vue'),
                    path: 'ambulance-call',
                    name: 'ambulance-call',
                    meta: {guarded: true},
                },
            ],
        },
        {
            path: '/login',
            component: require('@/components/Login.vue'),
            name: 'login',
        },
    ],
});

let confirmations = [];

const proceedNavigation = (to, from, next) => {
    confirmations = [];
    if (to.meta && to.meta.guarded && store.state.user === null) {
        store.commit('setEntryRoute', to);
        next({name: 'login'});
    } else {
        next();
    }
}

router.beforeEach((to, from, next) => {
    if (confirmations.length === 0) {
        proceedNavigation(to, from, next);
        return;
    }
    for (let confirmation of confirmations) {
        if (confirmation.condition === null || confirmation.condition() === true) {
            let message = _.isFunction(confirmation.message) ? confirmation.message() : confirmation.message;
            Vue.prototype.$confirm.call(null, message, () => {
                proceedNavigation(to, from, next);
            });
            return;
        }
    }
    proceedNavigation(to, from, next);
});

Vue.prototype.$confirmNavigation = (message, condition = undefined) => {
    if (message === false) {
        confirmations = [];
    } else {
        confirmations.push({message, condition});
    }
}

export default router;
