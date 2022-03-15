<!-- /** @scan-translations-off */ -->
<template>
    <div>
        <div style="position: absolute; top: -55555px;">
            <div class="moz-blank" id="moz-blank" style="width: 185mm;">
                <blank-header
                    :clinic="clinic"
                    :blank-data="blankData" />
                <div class="blank-body">
                    <div class="text-center"><h1>{{ __('МЕДИЧНА КАРТА СТАЦІОНАРНОГО ХВОРОГО') }} №{{ patient.card_number }}</h1></div>
                    <div class="blank-content">
                        <div class="row">
                            <div><b>{{ __('Дата госпіталізації') }}:</b>&nbsp;{{ hospitalisationStart }}</div>
                            <div><b>{{ __('Стать') }}:</b>&nbsp;{{ patient.gender ? __($handbook.getOption('gender', patient.gender)) : '' }}</div>
                        </div>
                        <div class="row">
                            <b>{{ __('Прізвище, ім’я, по батькові хворого') }}:</b>&nbsp;{{ patient.full_name }}
                        </div>
                        <div class="row">
                            <div><b>{{ __('Дата народження') }}:</b>&nbsp;{{ patient.birthdate }}</div>
                            <div><b>{{ __('Вік') }}:</b>&nbsp;{{ patient.age }}</div>
                        </div>
                        <div class="row">
                            <b>{{ __('Документ, що посвідчує особу') }}:</b>&nbsp;{{ __('Паспорт') }}
                        </div>
                        <div class="row">
                            <div><b>{{ __('Номер документа, що посвідчує особу:') }}</b>&nbsp;{{ patient.passport }}</div>
                            <div><b>{{ __('Громадянство:') }}</b>&nbsp;{{ __('UA') }}</div>
                        </div>
                        <div class="row">
                            <b>{{ __('Постійне місце проживання/перебування') }}:</b>&nbsp;{{ patient.full_address }}
                        </div>
                        <div class="row">
                            <b>{{ __('Місце роботи, посада') }}:</b>&nbsp;{{ patient.workplace }}
                        </div>
                        <div class="row">
                            <b>{{ __('Ким направлений хворий') }}:</b>&nbsp;{{ course.reffered }}
                        </div>
                        <div class="row">
                            <b>{{ __('Діагноз при госпіталізації (код за МКХ-10)') }}:</b>&nbsp;{{ $formatter.listFormat(diagnosisIcd) }}
                        </div>
                        <div class="row">
                            <div>
                                <b>{{ __('Відділення при госпіталізації') }}:</b>&nbsp;78
                            </div>
                            <div>
                                <b>{{ __('Відділення при виписці') }}:</b>&nbsp;78
                            </div>
                        </div>
                        <div class="row">
                            <div><b>{{ __('Група крові') }}:</b>&nbsp;{{ signalRecord.blood_group ? $handbook.getOption('blood_group', signalRecord.blood_group) : '' }}</div>
                            <div><b>{{ __('Резус-приналежність') }}:</b>&nbsp;{{ signalRecord.rhesus_factor ? __($handbook.getOption('rhesus_factor', signalRecord.rhesus_factor)) : '' }}</div>
                        </div>
                        <div class="row">
                            <b>{{ __('Алергічні реакції, гіперчутливість чи непереносимість лікарського засобу') }}:</b>&nbsp;{{ signalRecord.drug_intolerance }}
                        </div>
                        <div class="row">
                            <b>{{ __('Діагноз заключний клінічний (код за МКХ-10)') }}:</b>&nbsp;{{ course.last_diagnosis_icd }}
                        </div>
                        <div class="row">
                            <b>{{ __('Медичні процедури та хірургічні операції') }}:</b>
                            {{ surgeryServiceName }},&nbsp;
                            {{ $formatter.listFormat([...anesthesiaServices], 'name') }}
                        </div>
                        <div class="row">
                            <template v-if="hasCancerObservation">
                                <b>{{ __('Онкологічний профілактичний огляд') }}:</b>
                                <p v-if="gynecologyObservation">{{ gynecologyObservation }}</p>
                                <p v-if="proctologyObservation">{{ proctologyObservation }}</p>
                                <p v-if="urologyObservation">{{ urologyObservation }}</p>
                            </template>
                        </div>
                        <div class="row" v-if="signalRecord.onco_observation_ren && signalRecord.onco_observation_ren_date">
                            <b>{{ __('Обстеження органів грудної порожнини') }}:</b>&nbsp;{{ formatDate(signalRecord.onco_observation_ren_date) }}
                        </div>
                        <div class="row" v-if="signalRecord.onco_observation_vil && signalRecord.onco_observation_vil_date">
                            <b>{{ __('Cкрининг ВИЛ') }}:</b>&nbsp;{{ formatDate(signalRecord.onco_observation_vil_date) }}
                        </div>
                        <div class="row" v-if="signalRecord.onco_observation_vaserman && signalRecord.onco_observation_vaserman_date">
                            <b>{{ __('Реакция Вассермана') }}:</b>&nbsp;{{ formatDate(signalRecord.onco_observation_vaserman_date) }}
                        </div>
                        <div class="row">
                            <b>{{ __('Застрахований(а)') }}:</b>&nbsp;{{ __($handbook.getOption('med_insurance_availability', patient.med_insurance)) }}
                        </div>
                        <div
                            v-if="outpatientRecord"
                            class="outpatient-record-details sections-wrapper" style="margin:0; margin-left:-20px;">
                            <section>
                                <div class="card-record-section">
                                    <div class="card-record-subsection">
                                        <h2>{{ __('Жалобы') }}</h2>
                                        <div class="card-record-line">
                                            <div class="card-record-field growable" style="display: block">
                                                <div class="border-bottom field-input" style="flex-basis: 100%;">
                                                    {{ outpatientRecord.complaints }}
                                                </div>
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
                                </div>
                            </section>
                            <section>
                                <h2>{{ __('Дневник') }}</h2>
                                <div class="previous-visit-block">
                                    <div v-for="(record, index) in diaryRecords"
                                         :key="index">
                                        <h3>
                                            {{ formatDate(record.date) }}
                                            <template v-if="record.doctor">
                                                {{ __('Врач:') }} {{ record.doctor }}
                                            </template>
                                        </h3>
                                        <div v-for="comment in record.comments"
                                             class="paragraph diary-entry">
                                            {{ comment }}
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <assignments :treatment-activities="treatmentActivities" />
                        <div class="row">
                            <b>{{ __('Дата заповнення') }}:</b>&nbsp;{{ formatDate($moment()) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section
            v-loading="loading"
            ref="docContainer"
            id="blank-container"
            class="light-grey pdf-container">
        </section>
    </div>
</template>
<script>
import BlankHeader from './BlankHeader.vue';
import CardRecordRepository from '@/repositories/patient/card/record';
import OutpatientRecord from '@/models/patient/card/outpatient-record';
import DiaryRecord from '@/models/patient/card/diary-record';
import ConditionRecord from '@/models/patient/card/condition-record';
import FieldsSection from '@/components/patients/cabinet/outpatient-cards/print/Section.vue';
import {Structure} from '@/services/card/template';
import Diary from '@/components/doctor/appointment/diary/Entries.vue';
import DocumentMixin from './mixin/document';
import CONSTANTS from '@/constants';
import Assignments from './Assignments.vue';

const BlankData = {
    blankNumber: '003/о',
    date: '14.02.2012',
    number: 110
};

export default {
    mixins: [
        DocumentMixin,
    ],
    components: {
        BlankHeader,
        FieldsSection,
        Diary,
        Assignments,
    },
    props: {
        treatmentActivities: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            blankData: BlankData,
            outpatientRecord: null,
            diaryRecords: [],
            records: null,
            excludeSections: [
                CONSTANTS.CARD_RECORD.SECTIONS.OPERATION_PROTOCOL,
                CONSTANTS.CARD_RECORD.SECTIONS.ANESTHESIOLOGY,
                CONSTANTS.CARD_RECORD.SECTIONS.ANESTHESIOLOGY_PROTOCOL,
                CONSTANTS.CARD_RECORD.SECTIONS.EPIDEMIOLOGICAL_MAP,
            ],
            documentTitle: __('МЕДИЧНА_КАРТА_СТАЦІОНАРНОГО_ХВОРОГО') + '_№' + this.patient.card_number,
        };
    },
    beforeMount() {
        this.getAppointmentRecords();
    },
    methods: {
        getAppointmentRecords() {
            let surgeryService = this.getSurgeryService();
            if (!surgeryService) {
                this.$error(__('Проверьте наличие назначенной операции в курсе лечения'));
                return;
            }

            let filters = {
                or: [
                    {course_records: this.course.id},
                    {card_assignment: surgeryService.card_assignment_id},
                ]
            };

            let record = new CardRecordRepository();
            record.fetch(filters, [{field: 'id', direction: 'desc'}]).then(courseRecords => {
                if (courseRecords && courseRecords.rows && courseRecords.rows.length !== 0) {
                    let outpatientRecords = courseRecords.rows.filter(row => row instanceof OutpatientRecord);
                    //Return if specialization template is missing
                    if (outpatientRecords.length === 0) {
                        this.$error(__('Шаблон амбулаторной карты отсутствует'));
                        return this.$emit('cancel');
                    }

                    let cardRecord = courseRecords.rows[0];

                    record.getAppointmentRecords(cardRecord.card_specialization_id, cardRecord.appointment_id, null, true, cardRecord.template_id)
                        .then(appointmentRecords => {
                            let template = appointmentRecords.outpatientData.template;
                            this.outpatientRecord = new OutpatientRecord();

                            //add diary records
                            courseRecords.rows.forEach(row => {
                                if ((row.type === CONSTANTS.CARD_RECORD.TYPE.DIARY_RECORD || row.type === CONSTANTS.CARD_RECORD.TYPE.CONDITION_RECORD) && row.appointment_id !== cardRecord.appointment_id) {
                                    appointmentRecords.records.push(row);
                                }
                            });

                            appointmentRecords.records.forEach((record) => {
                                let doctor = _.isFilled(record.doctor) && record.appointment_doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE ? record.doctor.full_name : null;
                                let doctorAndDateExist = this.diaryRecords.findIndex(existRecord => {
                                    return existRecord.doctor === doctor && existRecord.date === record.appointment_date
                                })
                                if (record instanceof OutpatientRecord) {
                                    if (this.outpatientRecord === null) {
                                        this.outpatientRecord = record;
                                    } else {
                                        this.outpatientRecord.mergeWith(record);
                                    }
                                } else if (record instanceof DiaryRecord) {
                                    if (doctorAndDateExist >= 0) {
                                        this.diaryRecords[doctorAndDateExist].comments.push(record.comment)
                                    } else {
                                        this.diaryRecords.push({doctor: doctor, date: record.appointment_date, comments: [record.comment]})
                                    }
                                } else if (record instanceof ConditionRecord) {
                                    if (doctorAndDateExist >= 0) {
                                        this.diaryRecords[doctorAndDateExist].comments.push(__('t: ') + record.temperature + "℃, " + __('АД: ') + record.at + '/' + record.at2 + __('мм рт. ст.') + ', ' + __('ЧП: ') + record.frequency + __('уд./мин'))
                                    } else {
                                        this.diaryRecords.push({doctor: doctor, date: record.appointment_date, comments: [__('t: ') + record.temperature + "℃, " + __('АД: ') + record.at + '/' + record.at2 + __('мм рт. ст.') + ', ' + __('ЧП: ') + record.frequency + __('уд./мин')]})
                                    }
                                }
                            });
                            this.diaryRecords = _.orderBy(this.diaryRecords, 'date', 'desc');
                            this.outpatientRecord.setPrevious(appointmentRecords.outpatientData);

                            if (template) {
                                this.loadOutpatientCardAddons().then(templateAddons => {

                                    let availableAddons = this.excludeAddons(templateAddons);
                                    let structure = new Structure(template.structure, template.field_keys, availableAddons);
                                    this.outpatientRecord.mapStructure(structure);
                                    this.structure = structure;
                                    this.records = appointmentRecords.records;
                                    this.loading = false;
                                    this.$nextTick(() => this.makePDF());
                                });
                            } else {
                                this.records = appointmentRecords.records;
                                this.loading= false;
                                this.$nextTick(() => this.makePDF());
                            }
                        });
                }
            });
        },
    },
}
</script>
