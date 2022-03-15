<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section class="grey">
                <el-row :gutter="20">
                    <el-col :span="-15">
                        <template v-if="isPatientContact">
                            <form-switch
                                :entity="model"
                                options="patient_status"
                                property="contact.status"
                                :label="__('Кто звонит')"
                                :disabled="isContactExist || isFilled(model.contact.lastname)"
                            />
                            <form-switch
                                :entity="model"
                                :disabled="isNotPatient"
                                options="call_is_first"
                                property="is_first"
                                :label="__('Тип обращения')"/>
                        </template>
                        <form-row
                            name="current_client_type"
                            :label="__('Контакт')"
                            :required="true">
                            <el-button-group>
                                <el-button
                                    :class="['toggle', {active: isNew}]"
                                    @click="resetContact">
                                    {{ __('Новый') }}
                                </el-button>
                                <el-button
                                    :class="['toggle', {active: !isNew}]"
                                    @click="searchContact">
                                    {{ __('Существующий') }}
                                </el-button>
                            </el-button-group>
                        </form-row>
                    </el-col>
                    <el-col :span="-45">
                        <patient-form
                            v-if="isPatientContact"
                            key="patient-form"
                            :model="model"
                            :sources="sources"
                            :clinics="clinics"
                            genders="gender" />
                        <employee-form
                            v-else
                            key="employee-form"
                            :model="model" />
                    </el-col>
                </el-row>
            </section>
            <hr />
            <section>
                <h3 class="section-title">{{ __('Данные звонка') }}</h3>
                <call-form
                    :model="model"
                    :specializations="specializations"
                    @spec_changed="specChanged"
                    :operators="operators"
                    :clinics="clinics"
                    :results="results"
                    :call-requests="callRequests"
                    :is-patient="isPatient" />
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';
import Patient from '@/models/patient';
import Employee from '@/models/employee';
import SpecializationRepository from '@/repositories/specialization';
import EmployeeRepository from '@/repositories/employee';
import CallResultRepository from '@/repositories/calls/result';
import CallRequestRepository from '@/repositories/call-request';
import PatientForm from './form-parts/PatientForm.vue';
import EmployeeForm from './form-parts/EmployeeForm.vue';
import CallForm from './form-parts/CallForm.vue';
import CONSTANTS  from '@/constants';
import SearchContact from '@/components/call-center/voip/SearchContact.vue';
import InformationSourceRepository from '@/repositories/patient/information-source';
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    components: {
        PatientForm,
        EmployeeForm,
        CallForm,
    },
    props: {
        model: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        let specializations = new SpecializationRepository({
            limitClinics: this.limitClinics,
            filters: this.getSpecializationFilters(),
        });
        specializations.watch('data', (repo, prop, data) => {
            this.nonProfileSpecializations = data
                .filter((item) => item.is_non_profile)
                .map((item) => item.id);
            this.results.setFilters(this.getCallResultFilters());
        });

        return {
            sources: new ProxyRepository(({filters, sort, limit}) => {
                let combined = this.sources.getFilters(filters);
                let repository = new InformationSourceRepository();
                if ((combined.filters.query === undefined || combined.filters.query.length === 0) &&
                    combined.filters.clinic.length === 0) {
                    return Promise.resolve([]);
                }
                return repository.fetchList(combined.filters, sort, limit);
            }, {
                filters: this.getSourceFilters(),
            }),
            specializations,
            existedPatientSpecializations: null,
            operators: new EmployeeRepository({
                filters: {
                    status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                },
            }),
            clinics: new ClinicRepository({
                accessLimit: this.limitClinics,
            }),
            results: new CallResultRepository({
                filters: this.getCallResultFilters(),
            }),
            callRequests: new ProxyRepository(({filters, sort, limit}, isList, self) => {
                let repository = new CallRequestRepository();
                if (this.model.contact.isNew()) {
                    return Promise.resolve([]);
                }
                filters = self.getFilters(filters).filters || {};
                return repository.fetchList(filters, sort, limit);
            }, {
                filters: this.getCallRequestFilters(),
            }),
            disabledSwitch: false,
        };
    },
    computed: {
        isPatientContact() {
            return this.getIsPatientContact();
        },
        isEmployeeContact() {
            return this.getIsEmployeeContact();
        },
        isNew() {
            return this.getIsNew();
        },
        isNotPatient() {
            return this.getIsNotPatient();
        },
        isPatient() {
            return this.getIsPatient();
        },
        isContactExist() {
            return this.model.contact.id !== null;
        },
    },
    beforeCreate() {
        this.nonProfileSpecializations = [];
    },
    watch: {
        ['model.contact.status'](val) {
            this.model.contact.unset(['clinics']);
            this.model.unset(['clinic_id', 'specialization_id']);
            if (val === CONSTANTS.PATIENT.STATUS.GUEST) {
                this.model.is_first = CONSTANTS.CALL.REQUEST_TYPES.FIRST;
            }
            this.results.setFilters(this.getCallResultFilters());
        },
        ['model.contact.clinics'](val) {
            this.callRequests.setFilters(this.getCallRequestFilters());
            this.sources.setFilters(this.getSourceFilters());
        },
        ['model.is_first'](val) {
            if (this.getIsNew() && val == CONSTANTS.CALL.REQUEST_TYPES.REPEATED) {
                this.searchContact();
            } else {
                this.results.setFilters(this.getCallResultFilters());
            }
        },
        ['model.clinic_id'](clinic) {
            this.specializations.setFilters(this.getSpecializationFilters());
        },
        ['model.specialization_id'](specialization) {
            this.results.setFilters(this.getCallResultFilters());
        },
    },
    mounted() {
        if(this.model.isNew()) {
            this.setCallDateTime();
        }
    },
    methods: {
        searchContact() {
            this.$modalComponent(SearchContact, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                patientSelected: (dialog, patient) => {
                    this.selectPatient(patient);
                    dialog.close();
                },
                employeeSelected: (dialog, employee) => {
                    this.selectEmployee(employee);
                    dialog.close();
                },
            }, {
                header: __('Выбор контакта'),
                width: '1270px',
                beforeClose: () => {
                    if (this.getIsNew()) {
                        this.model.is_first = CONSTANTS.CALL.REQUEST_TYPES.FIRST;
                    } else {
                        if(this.model.contact_type != CONSTANTS.USER.TYPE.PATIENT){
                            this.model.is_first = CONSTANTS.CALL.REQUEST_TYPES.REPEATED;
                        }

                    }
                }
            });
        },
        getExistedPatientSpecializations(patient){
            let totalFilter = this.getSpecializationFilters();
            totalFilter.patientappeal = patient.id;

            new SpecializationRepository({
                limitClinics: this.limitClinics,
                filters: totalFilter,
            }).fetchList(totalFilter).then((result)=> {
                this.existedPatientSpecializations = result;
            });

        },
        specChanged(spec){
            let specIsFound = false;
            if(this.existedPatientSpecializations && this.model.contact_type == CONSTANTS.USER.TYPE.PATIENT){
                this.existedPatientSpecializations.map((item)=>{
                    if(item.id == spec){
                        this.model.is_first = CONSTANTS.CALL.REQUEST_TYPES.REPEATED;
                        specIsFound = true;
                        return;
                    }

                });

                if(specIsFound){
                    this.model.is_first = CONSTANTS.CALL.REQUEST_TYPES.REPEATED;
                }else{
                    this.model.is_first = CONSTANTS.CALL.REQUEST_TYPES.FIRST;
                }

            }
        },
        resetContact() {
            this.model.contact = new Patient();
            this.model.contact_id = null;
            this.model.contact_type = CONSTANTS.USER.TYPE.PATIENT;
            this.updateContactRelatedFilters();
        },
        selectPatient(patient) {
            this.getExistedPatientSpecializations(patient);
            this.model.contact = patient;
            this.model.contact_id = patient.id;
            this.model.contact_type = CONSTANTS.USER.TYPE.PATIENT;
            this.updateContactRelatedFilters();
        },
        selectEmployee(employee) {
            this.model.contact = employee;
            this.model.contact_id = employee.id;
            this.model.contact_type = CONSTANTS.USER.TYPE.EMPLOYEE;
            this.updateContactRelatedFilters();
        },
        updateContactRelatedFilters() {
            this.callRequests.setFilters(this.getCallRequestFilters());
            this.results.setFilters(this.getCallResultFilters());
            this.sources.setFilters(this.getSourceFilters());
        },
        setCallDateTime() {
            let now = this.$moment();
            this.model.set('date', now.format("YYYY-MM-DD"));
            this.model.set('time', now.format("HH:mm:ss"));
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                clinic: this.model.clinic_id,
                not_use_for_call: 0,
            });
        },
        getCallRequestFilters() {
            return _.onlyFilled({
                patient: this.model.contact.id,
                or: _.onlyFilled({
                    status: 'made',
                    id: this.model.call_request_id,
                }),
            });
        },
        getCallResultFilters() {
            if (this.getIsNotPatient()) {
                return {
                    use_for_not_patient: 1,
                };
            }

            if (this.getIsNonProfile()) {
                return {
                    use_for_unspecialized_patient: 1,
                };
            }

            return _.onlyFilled({
                use_for_new_call: this.getIsNew() ? 1 : null,
                use_for_is_patient_first: this.getIsPatientFirst() ? 1 : null,
                use_for_repeated_patient: this.getIsPatientRepeated() ? 1 : null,
            })
        },
        getIsPatientContact() {
            return (this.model.contact instanceof Patient);
        },
        getIsEmployeeContact() {
            return (this.model.contact instanceof Employee);
        },
        getIsNew() {
            return this.model.contact.isNew();
        },
        getIsPatient() {
            return this.getIsPatientContact()
                && this.model.contact.status === CONSTANTS.PATIENT.STATUS.PATIENT;
        },
        getIsNotPatient() {
            return !this.getIsPatientContact()
                || this.model.contact.status === CONSTANTS.PATIENT.STATUS.GUEST;
        },
        getIsPatientFirst() {
            return this.getIsPatient()
                && this.model.is_first === CONSTANTS.CALL.REQUEST_TYPES.FIRST;
        },
        getIsPatientRepeated() {
            return this.getIsPatient()
                && this.model.is_first === CONSTANTS.CALL.REQUEST_TYPES.REPEATED;
        },
        getIsNonProfile() {
            return this.nonProfileSpecializations.indexOf(this.model.specialization_id) !== -1;
        },
        getSourceFilters() {
            if (this.getIsPatientContact()) {
                return {
                    clinic: this.model.contact.clinics,
                };
            } else {
                return {};
            }
        },
        isFilled(val) {
            return _.isFilled(val);
        },
    }
}
</script>
