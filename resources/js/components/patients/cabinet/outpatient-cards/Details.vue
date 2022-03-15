<template>
    <content-placeholder
        v-if="loading"
        height="400px"/>
    <div
        v-else
        class="outpatient-record-details sections-wrapper">
        <section>
            <div class="card-record-section">
                <div class="card-record-subsection">
                    <h2 :class="{ changed: previousVisit && outpatientRecord.complaints !== previousVisit.complaints}">
                        {{ __('Жалобы') }}
                    </h2>
                    <div class="card-record-line">
                        <div class="card-record-field growable"
                             width="100%">
                            <el-input
                                v-model="outpatientRecord.complaints"
                                autosize
                                readonly
                                type="textarea" />
                        </div>
                    </div>
                </div>
                <template v-if="structure">
                    <fields-section
                        v-for="(section, index) in structure.getSections()"
                        :key="index"
                        :label="section.label"
                        :fields="section.children"
                        :readonly="true" />
                </template>
                <div class="card-record-line">
                    <div class="card-record-field growable" style="width: 100%">
                        <div class="prefix"
                             :class="{ changed: icdChanged }">
                            {{ __('Диагноз по МКБ') }}
                        </div>
                        <el-input
                            :value="$formatter.listFormat(outpatientRecord.diagnosis_icd_names)"
                            :autosize="true"
                            :readonly="true"
                            type="textarea" />
                    </div>
                </div>
                <div class="card-record-line">
                    <div class="card-record-field growable" style="width: 100%">
                        <div class="prefix"
                             :class="{ changed: previousVisit && outpatientRecord.diagnosis !== previousVisit.diagnosis}">
                            {{ __('Диагноз') }}
                        </div>
                        <el-input
                            :value="outpatientRecord.diagnosis"
                            :autosize="true"
                            :readonly="true"
                            type="textarea" />
                    </div>
                </div>
            </div>
        </section>
        <hr />
        <section>
            <h2>{{ __('Дневник визита') }}</h2>
            <last-visit-records :records="records" />
        </section>
    </div>
</template>

<script>
import RecordTemplateRepository from '@/repositories/patient/card/record-template';
import CardRecordRepository from '@/repositories/patient/card/record';
import OutpatientRecord from '@/models/patient/card/outpatient-record';
import {Structure} from '@/services/card/template';
import FieldsSection from '@/components/doctor/appointment/outpatient-record/Section.vue';
import LastVisitRecords from '@/components/doctor/appointment/previous-visit/Entries.vue';
import DiagnosisRepository from '@/repositories/diagnosis';
import CardPrint from './Print.vue';
import printer from '@/services/print';
import CONSTANTS from '@/constants';

export default {
    components: {
        FieldsSection,
        LastVisitRecords,
    },
    props: {
        appointments: Array,
        specialization_id: [String, Number],
        specialization: {
            type: Object,
            default: () => ({})
        },
        card: [String, Number],
        clinic_id: [String, Number],
    },
    data() {
        return {
            appointment: null,
            previousVisit: null,
            outpatientRecord: null,
            loading: true,
            structure: null,
            diagnosisRepository: new DiagnosisRepository(),
            records: null,
            currentAppointmentIndex: 0,
            isFieldsChanged: false,
        };
    },
    computed: {
        icdChanged() {
            if(this.outpatientRecord && this.previousVisit){
                return !_.isEqual(_.sortBy(this.outpatientRecord.diagnosis_icd_names), _.sortBy(this.previousVisit.diagnosis_icd_names));
            }
            return false;
        },
        isChanged() {
            return this.icdChanged || this.isFieldsChanged;
        }
    },
    mounted() {
        this.appointment = this.appointments[this.currentAppointmentIndex];
    },
    methods: {
        load(templateAddons = []) {
            this.loadAppointmentRecords().then((appointmentRecords) => {
                let template = appointmentRecords.outpatientData.template;
                this.outpatientRecord = new OutpatientRecord();
                appointmentRecords.records.forEach((record) => {
                    if (record instanceof OutpatientRecord) {
                        if (this.outpatientRecord === null) {
                            this.outpatientRecord = record;
                        } else {
                            this.outpatientRecord.mergeWith(record);
                        }
                    }
                });
                this.outpatientRecord.setPrevious(appointmentRecords.outpatientData);
                if (template) {
                    let structure = new Structure(template.structure, template.field_keys, templateAddons);
                    this.outpatientRecord.mapStructure(structure);
                    this.structure = structure;
                } else {
                    this.structure = null;
                }
                this.records = appointmentRecords.records;
                if (appointmentRecords.outpatientData.id) {
                    this.previousVisit = appointmentRecords.outpatientData;
                }
                this.loading = false;
            });
        },
        isSurgeryCourse() {
            return this.specialization && this.specialization.service_group === CONSTANTS.SPECIALIZATION.SERVICE_GROUPS.SURGERY;
        },
        loadCardTemplate() {
            let repository = new RecordTemplateRepository();
            return repository.getForSpecialization(this.specialization_id);
        },
        loadOutpatientCardAddons() {
            let repository = new RecordTemplateRepository();
            return repository.fetch({specialization_addons: this.specialization_id}).then(templates => {
                if (templates && templates.rows) {
                    return Promise.resolve(templates.rows);
                }
                return Promise.resolve([]);
            });
        },
        loadAppointmentRecords() {
            let repository = new CardRecordRepository();
            return repository.getAppointmentRecords(this.card, this.appointment.id, null, true);
        },
        print(pageHeader = '') {
            printer.printComponent(CardPrint, {
                structure: this.structure,
                record: this.outpatientRecord,
                appointment: this.appointment,
                records: this.records,
                pageHeader,
            }, this.clinic_id);
        },
        prev(){
            let index = this.currentAppointmentIndex;
            if(this.appointments[++index] !== undefined){
                this.appointment = this.appointments[index];
                ++this.currentAppointmentIndex;
            }
        },
        next(){
            let index = this.currentAppointmentIndex;
            if(this.appointments[--index] !== undefined){
                this.appointment = this.appointments[index];
                --this.currentAppointmentIndex;
            }
        },
        dateChange(data){
            let appointmentIndex = null;
            let appointment = this.appointments.find((appointment, index) => {
                if(appointment.date === data.formattedDate){
                    appointmentIndex = index;
                    return true;
                }

                return null;
            });
            if(appointment){
                this.currentAppointmentIndex = appointmentIndex;
                this.appointment = appointment;
            }
        }
    },
    watch: {
        appointment: function (val) {
            this.loading = true;
            if (this.isSurgeryCourse()) {
                this.loadOutpatientCardAddons().then(templateAddons => {
                    this.load(templateAddons);
                });
            } else {
                this.load();
            }
            this.$emit('appointmentChange', {
                appointment:this.appointment,
                appointmentIndex: this.currentAppointmentIndex
            });
        },
        structure: {
            handler: function (val, oldVal) {
                if (val) {
                    let vm = this;
                    val._fields.forEach((field) => {
                        if(field.changed){
                            vm.isFieldsChanged = true;
                        }
                    })
                }
            },
            deep: true
        },
    }
};
</script>
