<!-- /** @scan-translations-off */ -->
<template>
    <div>
        <div style="position: absolute; top: -55555px;">
            <div class="moz-blank" id="moz-blank" style="width: 185mm;">
                <blank-header 
                    :clinic="clinic"
                    :blank-data="blankData" />
                <div class="blank-body">
                    <div class="text-center"><h1>{{ __('ВИПИСКА') }} <br> {{ __('із медичної карти амбулаторного (стаціонарного) хворого') }}</h1></div>
                    <div class="blank-content">
                        <div class="row">
                            <b>{{ __('Прізвище, ім’я, по батькові хворого') }}:</b>&nbsp;{{ patient.full_name }}
                        </div>
                        <div class="row">
                            <b>{{ __('Дата народження') }}:</b>&nbsp;{{ patient.birthdate }}
                        </div>
                        <div class="row">
                            <b>{{ __('Постійне місце проживання/перебування') }}:</b>&nbsp;{{ patient.full_address }}
                        </div>
                        <div class="row">
                            <b>{{ __('Місце роботи, посада') }}:</b>&nbsp;{{ patient.workplace }}
                        </div>
                        <div class="row">
                            <b>{{ __('Дата направлення в стацiонар') }}:</b>&nbsp;{{ hospitalisationStart }}
                        </div>
                        <div class="row">
                            <b>{{ __('Діагноз при госпіталізації (код за МКХ-10)') }}:</b>&nbsp;{{ $formatter.listFormat(diagnosisIcd) }}
                        </div>
                        <div class="row">
                            <b>{{ __('Дата виписки') }}:</b>&nbsp;{{ dischargeDate }}
                        </div>
                        <div class="row">
                            <b>{{ __('Діагноз заключний клінічний (код за МКХ-10)') }}:</b>&nbsp;{{ course.last_diagnosis_icd }}
                        </div>
                        <div
                            v-if="outpatientRecord"
                            class="outpatient-record-details sections-wrapper" style="margin:0; margin-left:-20px;">
                            <section>
                                <div class="card-record-section">
                                    <div class="card-record-subsection">
                                        <h2>{{ __('Жалобы') }}</h2>
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
                                </div>
                            </section>
                        </div>
                        <div class="row">
                            <b>{{ __('Вид операції') }}:</b>&nbsp; {{ surgeryServiceName }}
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
import FieldsSection from '@/components/patients/cabinet/outpatient-cards/print/Section.vue';
import {Structure} from '@/services/card/template';
import DocumentMixin from './mixin/document';
import Assignments from './Assignments.vue';
import CONSTANTS from '@/constants';

const BlankData = {
    blankNumber: '027/о',
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
            documentTitle: __('ВИПИСКА_із_медичної_карти_амбулаторного_(стаціонарного)_хворого'),
            includeSections: [
                CONSTANTS.CARD_RECORD.SECTIONS.LABOR_RECOMENDATIONS,
                CONSTANTS.CARD_RECORD.SECTIONS.EPICRISIS,
            ],
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

            let record = new CardRecordRepository();
            let filters = {
                or: [
                    {course_records: this.course.id},
                    {card_assignment: surgeryService.card_assignment_id},
                ]
            };

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
                                    let subtemplate = availableAddons.splice(0, 1)[0];
                                    if (subtemplate) {
                                        template.structure = subtemplate.structure;
                                        template.field_keys = subtemplate.field_keys;
                                    }
                                    let structure = new Structure(template.structure, template.field_keys, availableAddons);
                                    this.outpatientRecord.mapStructure(structure);
                                    this.structure = structure;
                                    this.loading = false;
                                    this.$nextTick(() => this.makePDF());
                                });
                            } else {
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