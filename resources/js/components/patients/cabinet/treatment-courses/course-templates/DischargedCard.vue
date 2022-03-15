<!-- /** @scan-translations-off */ -->
<template>
    <div>
        <div style="position: absolute; top: -55555px;">
            <div class="moz-blank" id="moz-blank" style="width: 185mm;">
                <blank-header 
                    :clinic="clinic"
                    :blank-data="blankData" />
                <div class="blank-body">
                    <div class="text-center"><h1>{{ __('КАРТА ПАЦІЄНТА, ЯКИЙ ВИБУВ ІЗ СТАЦІОНАРУ,') }} №{{ patient.card_number }}</h1></div>
                    <div class="blank-content">
                        <div class="row">
                            <b>{{ __('Дата госпіталізації') }}:</b>&nbsp;{{ hospitalisationStart }}
                        </div>
                        <div class="row">
                            <b>{{ __('Прізвище, ім’я, по батькові хворого') }}:</b>&nbsp;{{ patient.full_name }}
                        </div>
                        <div class="row">
                            <b>{{ __('Стать') }}:</b>&nbsp;{{ patient.gender ? __($handbook.getOption('gender', patient.gender)) : '' }}
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
                            <b>{{ __('Строк госпіталізації:') }} {{ hospitaledTimeDuration }}</b>
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
                        <div
                            v-if="outpatientRecord"
                            class="outpatient-record-details sections-wrapper" style="margin:0; margin-left:-20px;">
                            <section>
                                <div class="card-record-section">
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
                        </div>
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
import DocumentMixin from './mixin/document';
import CardRecordRepository from '@/repositories/patient/card/record';
import OutpatientRecord from '@/models/patient/card/outpatient-record';
import FieldsSection from '@/components/patients/cabinet/outpatient-cards/print/Section.vue';
import {Structure} from '@/services/card/template';
import CONSTANTS from '@/constants';

const BlankData = {
    blankNumber: '066/о',
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
    },
    data() {
        return {
            blankData: BlankData,
            documentTitle: __('КАРТА_ПАЦІЄНТА,_ЯКИЙ_ВИБУВ_ІЗ_СТАЦІОНАРУ,') + '_№' + this.patient.card_number,
            outpatientRecord: null,
            includeSections: [
                CONSTANTS.CARD_RECORD.SECTIONS.HOSPITALIZATION_DATA,
            ],
        };
    },
    computed: {
        hospitaledTimeDuration() {
            let minutes = 0;
            if (this.stationarAppointments.length !== 0) {
                this.stationarAppointments.forEach(appointment => {
                    minutes += this.$moment(appointment.end).diff(appointment.start, 'minutes');
                });
            }
            let hours = (minutes > 0) ? (minutes / 60) : 0;
            if (hours >= 24) {
                return 3;
            } else if(hours >= 7) {
                return 2;
            }
            return 1;
        },
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
                                this.loadOutpatientCardAddons().then(templateAddons => {
                                    let availableAddons = this.includeAddons(templateAddons);
                                    let structure = new Structure(availableAddons[0].structure, availableAddons[0].field_keys);
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
    }
}
</script>